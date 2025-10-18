<?php

namespace App\Http\Middleware;

use Closure;

class SetActiveBusiness
{
    public function handle($request, Closure $next)
    {
        if ($u = $request->user()) {
            $active = session('active_business_id')
                ?? $request->get('business_id')
                ?? $u->businesses()->value('business_id'); // user ka pehla business

            if ($active && $u->businesses()->where('business_id', $active)->exists()) {
                // models/traits ko available
                $u->current_business_id = $active;
            }
        }
        return $next($request);
    }
}
