<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetActiveBusiness
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        // 1) incoming param se try (route param 'business' ya query 'business_id')
        $incoming = $request->route('business') ?? $request->get('business_id');

        // 2) session me ho to use
        $sessionBid = session('active_business_id');

        // 3) user->current_business_id column (agar DB me maujood ho)
        $userCurrent = $user->current_business_id ?? null;

        // 4) user ka pehla business (sahi tarike se businesses table se id lo)
        //    NOTE: pivot ka 'business_id' column hota hai, businesses table ki primary key 'id' hoti hai
        $firstOwned = $user->businesses()
            ->select('businesses.id')
            ->orderBy('businesses.id', 'asc')
            ->value('businesses.id');

        // Priority: incoming > session > userCurrent > firstOwned
        $active = $incoming ?? $sessionBid ?? $userCurrent ?? $firstOwned;

        // validate: user is business member
        if ($active && $user->businesses()->where('businesses.id', $active)->exists()) {
            // (a) session me store करो ( यही सबसे ज़रूरी )
            session(['active_business_id' => (int) $active]);

            // (b) request cycle ke liye user object par set (runtime use)
            $user->current_business_id = (int) $active;

            // (c) OPTIONAL: agar users table me column hai to persist bhi कर दो
            if (schema_has_column('users', 'current_business_id')) {
                // avoid unnecessary writes
                if ((int) ($user->getOriginal('current_business_id') ?? 0) !== (int) $active) {
                    $user->forceFill(['current_business_id' => (int) $active])->saveQuietly();
                }
            }
        } else {
            // koi valid business nahi mila → session clear
            session()->forget('active_business_id');
        }

        return $next($request);
    }
}

// helper to avoid calling Schema facades in hot path
if (!function_exists('schema_has_column')) {
    function schema_has_column(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
