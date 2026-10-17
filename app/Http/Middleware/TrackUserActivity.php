<?php

namespace App\Http\Middleware;

use App\Models\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Activity tracking
        |--------------------------------------------------------------------------
        |
        | Sirf authenticated normal GET pages ko track kiya jayega.
        |
        */

        if ($this->shouldTrack($request)) {
            try {
                $user = $request->user();
                $userAgent = (string) $request->userAgent();

                /*
                |--------------------------------------------------------------------------
                | Valid business ID find karein
                |--------------------------------------------------------------------------
                */

                $businessId = $this->resolveBusinessId($request);

                /*
                |--------------------------------------------------------------------------
                | Activity create karein
                |--------------------------------------------------------------------------
                */

                $activity = UserActivity::create([
                    'user_id' => $user->id,

                    'business_id' => $businessId,

                    'session_key' => hash(
                        'sha256',
                        $request->session()->getId()
                    ),

                    'route_name' => optional(
                        $request->route()
                    )->getName(),

                    'page_title' => null,

                    'url' => $request->fullUrl(),

                    'path' => '/' . ltrim(
                        $request->path(),
                        '/'
                    ),

                    'method' => $request->method(),

                    'duration_seconds' => 0,
                    'heartbeat_count' => 0,

                    'started_at' => now(),
                    'last_seen_at' => now(),

                    'ip_address' => $request->ip(),
                    'user_agent' => $userAgent,

                    'device_type' => $this->deviceType($userAgent),
                    'browser' => $this->browser($userAgent),
                    'platform' => $this->platform($userAgent),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Blade tracker component ke liye activity ID
                |--------------------------------------------------------------------------
                */

                View::share(
                    'currentUserActivityId',
                    $activity->id
                );
            } catch (\Throwable $exception) {
                /*
                |--------------------------------------------------------------------------
                | Tracking error se main software band nahi hona chahiye
                |--------------------------------------------------------------------------
                |
                | Activity tracking fail ho jaye tab bhi dashboard/page normally
                | open hoga. Error Laravel log mein save rahega.
                |
                */

                Log::error('User activity tracking failed.', [
                    'user_id' => optional($request->user())->id,
                    'url' => $request->fullUrl(),
                    'message' => $exception->getMessage(),
                ]);

                View::share(
                    'currentUserActivityId',
                    null
                );
            }
        }

        return $next($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve valid business ID
    |--------------------------------------------------------------------------
    */

    private function resolveBusinessId(Request $request): ?int
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | 1. Active business session se
        |--------------------------------------------------------------------------
        */

        $businessId = $request->session()->get('active_business_id');

        /*
        |--------------------------------------------------------------------------
        | 2. User ke direct business_id se
        |--------------------------------------------------------------------------
        |
        | Kuch systems mein users table mein business_id directly hota hai.
        |
        */

        if (!$businessId && !empty($user->business_id)) {
            $businessId = $user->business_id;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. User current_business_id se
        |--------------------------------------------------------------------------
        */

        if (!$businessId && !empty($user->current_business_id)) {
            $businessId = $user->current_business_id;
        }

        /*
        |--------------------------------------------------------------------------
        | 4. User businesses relation se first business
        |--------------------------------------------------------------------------
        */

        if (!$businessId) {
            try {
                $businessId = $user->businesses()
                    ->value('businesses.id');
            } catch (\Throwable $exception) {
                $businessId = null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Valid numeric ID check
        |--------------------------------------------------------------------------
        */

        if (!$businessId || !is_numeric($businessId)) {
            return null;
        }

        $businessId = (int) $businessId;

        /*
        |--------------------------------------------------------------------------
        | Business database mein exist karna chahiye
        |--------------------------------------------------------------------------
        */

        $businessExists = DB::table('businesses')
            ->where('id', $businessId)
            ->exists();

        if (!$businessExists) {
            /*
            | Deleted business ki invalid session value hata dein.
            */

            $request->session()->forget('active_business_id');

            return null;
        }

        return $businessId;
    }

    private function shouldTrack(Request $request): bool
    {
        if (!$request->user()) {
            return false;
        }

        if (!$request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        if (
            $request->is('_debugbar/*') ||
            $request->is('livewire/*') ||
            $request->is('storage/*')
        ) {
            return false;
        }

        if (
            $request->routeIs('activity.heartbeat') ||
            $request->routeIs('activity.end')
        ) {
            return false;
        }

        if ($request->is('super-admin/user-activity*')) {
            return false;
        }

        return true;
    }

    private function deviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (
            str_contains($userAgent, 'tablet') ||
            str_contains($userAgent, 'ipad')
        ) {
            return 'tablet';
        }

        if (
            str_contains($userAgent, 'mobile') ||
            str_contains($userAgent, 'android') ||
            str_contains($userAgent, 'iphone')
        ) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function browser(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') =>
                'Microsoft Edge',

            str_contains($userAgent, 'OPR/'),
            str_contains($userAgent, 'Opera') =>
                'Opera',

            str_contains($userAgent, 'Chrome/') =>
                'Google Chrome',

            str_contains($userAgent, 'Firefox/') =>
                'Mozilla Firefox',

            str_contains($userAgent, 'Safari/')
            && !str_contains($userAgent, 'Chrome/') =>
                'Safari',

            default =>
                'Other',
        };
    }

    private function platform(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Windows') =>
                'Windows',

            str_contains($userAgent, 'Android') =>
                'Android',

            str_contains($userAgent, 'iPhone'),
            str_contains($userAgent, 'iPad') =>
                'iOS',

            str_contains($userAgent, 'Macintosh') =>
                'macOS',

            str_contains($userAgent, 'Linux') =>
                'Linux',

            default =>
                'Other',
        };
    }
}