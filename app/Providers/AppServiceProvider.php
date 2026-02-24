<?php

namespace App\Providers;

use App\Support\AuthAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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
    // public function boot(): void
    // {
    //     view()->composer('*', function () {
    //         app()->instance('authUser', AuthAccess::user());
    //     });

    //     // Blade directive for permission checks
    //     Blade::if('permission', function ($permission) {
    //         return auth()->check() && auth()->user()->hasPermission($permission);
    //     });

    //     // Blade directive for role checks
    //     Blade::if('role', function ($role) {
    //         return auth()->check() && auth()->user()->hasRole($role);
    //     });

    //     // Blade directive for any role check
    //     Blade::if('anyrole', function (...$roles) {
    //         return auth()->check() && auth()->user()->hasAnyRole($roles);
    //     });

    //     // Blade directive for all roles check
    //     Blade::if('allroles', function (...$roles) {
    //         return auth()->check() && auth()->user()->hasAllRoles($roles);
    //     });
    // }
    public function boot(): void
    {
        // DB::listen(function ($query) {
        //     if (str_contains($query->sql, 'users')) {
        //         Log::info('USER QUERY', [
        //             'sql' => $query->sql,
        //         ]);
        //     }
        // });
        // Blade directive for permission checks
        Blade::if('permission', function ($permission) {
            $user = AuthAccess::user();
            return $user && $user->hasPermission($permission);
        });

        Blade::if('role', function ($role) {
            $user = AuthAccess::user();
            return $user && $user->hasRole($role);
        });

        Blade::if('anyrole', function (...$roles) {
            $user = AuthAccess::user();
            return $user && $user->hasAnyRole($roles);
        });

        Blade::if('allroles', function (...$roles) {
            $user = AuthAccess::user();
            return $user && $user->hasAllRoles($roles);
        });
    }
}
