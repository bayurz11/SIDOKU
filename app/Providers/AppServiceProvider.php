<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Cache auth user once per request
        view()->composer('*', function ($view) {
            if (Auth::check()) {
                $user = Cache::remember(
                    'auth_user_' . Auth::id(),
                    now()->addMinutes(30),
                    fn() => Auth::user()
                );

                app()->instance('authUser', $user);
            }
        });

        Blade::if('permission', function ($permission) {
            $user = app()->bound('authUser') ? app('authUser') : null;
            return $user && $user->hasPermission($permission);
        });

        Blade::if('role', function ($role) {
            $user = app()->bound('authUser') ? app('authUser') : null;
            return $user && $user->hasRole($role);
        });

        Blade::if('anyrole', function (...$roles) {
            $user = app()->bound('authUser') ? app('authUser') : null;
            return $user && $user->hasAnyRole($roles);
        });

        Blade::if('allroles', function (...$roles) {
            $user = app()->bound('authUser') ? app('authUser') : null;
            return $user && $user->hasAllRoles($roles);
        });
    }
}
