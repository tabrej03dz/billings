<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveBusinessFromHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bid = $request->header('X-Business-Id')
            ?: $request->header('Business-Id')
                ?: $request->header('X-Business-ID');

        if ($bid) {
            app()->instance('active_business_id', (int) $bid);
        }

        return $next($request);
    }
}
