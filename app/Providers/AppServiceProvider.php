<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', \App\Http\Middleware\SetActiveBusiness::class);
         View::addNamespace('layouts', resource_path('views/layouts'));

          Gate::before(function ($user, $ability) {
                return $user->hasRole('super admin') ? true : null;
            });
    }
}
