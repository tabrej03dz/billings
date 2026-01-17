<?php
//
//namespace App\Models\Concerns;
//
//use Illuminate\Database\Eloquent\Builder;
//
//trait BelongsToBusiness
//{
//    public static function bootBelongsToBusiness(): void
//    {
//        // create ke waqt business_id auto set
//        static::creating(function ($model) {
//            if (blank($model->business_id) && auth()->check()) {
//                $bid = auth()->user()->current_business_id ?? session('active_business_id');
//                if ($bid) {
//                    $model->business_id = $bid;
//                }
//            }
//        });
//
//        // har query ko active business tak restrict
//        static::addGlobalScope('business', function (Builder $builder) {
//            if (auth()->check()) {
//                $bid = auth()->user()->current_business_id ?? session('active_business_id');
//                if ($bid) {
//                    $builder->where($builder->getModel()->getTable().'.business_id', $bid);
//                }
//            }
//        });
//    }
//
//    public function business()
//    {
//        return $this->belongsTo(\App\Models\Business::class);
//    }
//}


namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToBusiness
{
    protected static function resolveActiveBusinessId(): ?int
    {
        // API middleware se (X-Business-Id)
        if (app()->bound('active_business_id')) {
            return (int) app('active_business_id');
        }

        // Web session
        if ($bid = session('active_business_id')) {
            return (int) $bid;
        }

        // User default business
        if (auth()->check()) {
            return (int) auth()->user()->current_business_id;
        }

        return null;
    }


    public static function bootBelongsToBusiness(): void
    {
        // create ke waqt business_id auto set
        static::creating(function ($model) {
            if (blank($model->business_id)) {
//                $bid = session('active_business_id');
//                if (!$bid && auth()->check()) {
//                    $bid = auth()->user()->current_business_id;
//                }
                $bid = static::resolveActiveBusinessId();
                if ($bid) {
                    $model->business_id = $bid;
                }
            }
        });

        // har query ko active business tak restrict
        static::addGlobalScope('business', function (Builder $builder) {
            // superadmin ko sab dikhana ho to yahan skip kara sakte ho:
            if (auth()->check() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('superadmin')) {
                return; // no restriction for superadmin
            }

            $table = $builder->getModel()->getTable();
            $bid = session('active_business_id');

//            if (!$bid && auth()->check()) {
//                $bid = auth()->user()->current_business_id;
//            }

            $bid = static::resolveActiveBusinessId();

            if ($bid) {
                $builder->where("$table.business_id", $bid);
            }
        });
    }

    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class);
    }

    // optional: manual scope (kabhi direct use karna ho)
    public function scopeForBusiness(Builder $q, $bid): Builder
    {
        $table = $q->getModel()->getTable();
        return $q->where("$table.business_id", $bid);
    }
}
