<?php

namespace App\Models\Concerns;

use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait BelongsToBusiness
{
    /*
    |--------------------------------------------------------------------------
    | Resolve active business ID
    |--------------------------------------------------------------------------
    |
    | Priority:
    | 1. API middleware/container active_business_id
    | 2. Session active_business_id
    | 3. User current_business_id
    | 4. business_user pivot ka first valid business
    |
    | Har ID ko businesses table aur business_user pivot se verify kiya jayega.
    |
    */

    protected static function resolveActiveBusinessId(): ?int
    {
        if (!auth()->check()) {
            return null;
        }

        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Super Admin ke liye optional active business
        |--------------------------------------------------------------------------
        |
        | Super Admin agar particular business select kare to session/container
        | business use hoga. Otherwise null rahega aur global scope skip hoga.
        |
        */

        $isSuperAdmin = method_exists($user, 'hasRole')
            && (
                $user->hasRole('super admin')
                || $user->hasRole('superadmin')
            );

        /*
        |--------------------------------------------------------------------------
        | User ke connected valid business IDs
        |--------------------------------------------------------------------------
        */

        $connectedBusinessIds = DB::table('business_user')
            ->join(
                'businesses',
                'businesses.id',
                '=',
                'business_user.business_id'
            )
            ->where(
                'business_user.user_id',
                $user->id
            )
            ->pluck('businesses.id')
            ->map(fn ($id) => (int) $id)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | API/container business
        |--------------------------------------------------------------------------
        */

        $containerBusinessId = null;

        if (app()->bound('active_business_id')) {
            $containerBusinessId = app('active_business_id');

            if (
                $containerBusinessId
                && is_numeric($containerBusinessId)
            ) {
                $containerBusinessId = (int) $containerBusinessId;

                if (
                    $isSuperAdmin
                    && Business::query()
                        ->withoutGlobalScopes()
                        ->whereKey($containerBusinessId)
                        ->exists()
                ) {
                    return $containerBusinessId;
                }

                if (
                    $connectedBusinessIds
                        ->contains($containerBusinessId)
                ) {
                    static::storeResolvedBusiness(
                        $user,
                        $containerBusinessId
                    );

                    return $containerBusinessId;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Session business
        |--------------------------------------------------------------------------
        */

        $sessionBusinessId = session('active_business_id');

        if (
            $sessionBusinessId
            && is_numeric($sessionBusinessId)
        ) {
            $sessionBusinessId = (int) $sessionBusinessId;

            if (
                $isSuperAdmin
                && Business::query()
                    ->withoutGlobalScopes()
                    ->whereKey($sessionBusinessId)
                    ->exists()
            ) {
                return $sessionBusinessId;
            }

            if (
                $connectedBusinessIds
                    ->contains($sessionBusinessId)
            ) {
                static::storeResolvedBusiness(
                    $user,
                    $sessionBusinessId
                );

                return $sessionBusinessId;
            }

            /*
            | Invalid/deleted session business hata dein.
            */

            session()->forget('active_business_id');
        }

        /*
        |--------------------------------------------------------------------------
        | User current_business_id
        |--------------------------------------------------------------------------
        */

        $currentBusinessId = $user->current_business_id ?? null;

        if (
            $currentBusinessId
            && is_numeric($currentBusinessId)
        ) {
            $currentBusinessId = (int) $currentBusinessId;

            if (
                $isSuperAdmin
                && Business::query()
                    ->withoutGlobalScopes()
                    ->whereKey($currentBusinessId)
                    ->exists()
            ) {
                session([
                    'active_business_id' =>
                        $currentBusinessId,
                ]);

                return $currentBusinessId;
            }

            if (
                $connectedBusinessIds
                    ->contains($currentBusinessId)
            ) {
                session([
                    'active_business_id' =>
                        $currentBusinessId,
                ]);

                return $currentBusinessId;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Pivot se first valid business fallback
        |--------------------------------------------------------------------------
        */

        $fallbackBusinessId =
            $connectedBusinessIds->first();

        if ($fallbackBusinessId) {
            static::storeResolvedBusiness(
                $user,
                (int) $fallbackBusinessId
            );

            return (int) $fallbackBusinessId;
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin without selected business
        |--------------------------------------------------------------------------
        */

        if ($isSuperAdmin) {
            return null;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Session and user default sync
    |--------------------------------------------------------------------------
    */

    protected static function storeResolvedBusiness(
        $user,
        int $businessId
    ): void {
        session([
            'active_business_id' => $businessId,
        ]);

        /*
        | current_business_id column available ho to update karein.
        */

        if (
            isset($user->current_business_id)
            && (int) $user->current_business_id
                !== $businessId
        ) {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'current_business_id' =>
                        $businessId,

                    'updated_at' => now(),
                ]);

            $user->current_business_id =
                $businessId;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Trait boot
    |--------------------------------------------------------------------------
    */

    public static function bootBelongsToBusiness(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Creating: business_id automatically set
        |--------------------------------------------------------------------------
        */

        static::creating(function ($model) {
            if (blank($model->business_id)) {
                $businessId =
                    static::resolveActiveBusinessId();

                if ($businessId) {
                    $model->business_id =
                        $businessId;
                }
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Global business scope
        |--------------------------------------------------------------------------
        */

        static::addGlobalScope(
            'business',
            function (Builder $builder) {
                $user = auth()->user();

                /*
                | Guest requests par scope nahi.
                */

                if (!$user) {
                    return;
                }

                /*
                | Dono possible role names support kiye gaye hain.
                */

                $isSuperAdmin =
                    method_exists($user, 'hasRole')
                    && (
                        $user->hasRole('super_admin')
                        || $user->hasRole('superadmin')
                    );

                /*
                | Super Admin ko sab businesses ka data dikhana hai.
                */

                if ($isSuperAdmin) {
                    return;
                }

                $businessId =
                    static::resolveActiveBusinessId();

                if (!$businessId) {
                    /*
                    |--------------------------------------------------------------------------
                    | Business na mile to data leak rokna
                    |--------------------------------------------------------------------------
                    |
                    | Scope na lagane par user ko sab businesses ka data dikh sakta
                    | tha. Isliye impossible condition lagayi gayi hai.
                    |
                    */

                    $builder->whereRaw('1 = 0');

                    return;
                }

                $table = $builder
                    ->getModel()
                    ->getTable();

                $builder->where(
                    $table . '.business_id',
                    $businessId
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Business relation
    |--------------------------------------------------------------------------
    */

    public function business()
    {
        return $this->belongsTo(
            Business::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Manual business scope
    |--------------------------------------------------------------------------
    */

    public function scopeForBusiness(
        Builder $query,
        int $businessId
    ): Builder {
        $table = $query
            ->getModel()
            ->getTable();

        return $query->where(
            $table . '.business_id',
            $businessId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query without active business scope
    |--------------------------------------------------------------------------
    */

    public function scopeWithoutBusinessScope(
        Builder $query
    ): Builder {
        return $query->withoutGlobalScope(
            'business'
        );
    }
}