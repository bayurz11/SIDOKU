<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CacheAuthUser
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            // Load user SEKALI
            $user = Auth::user();

            // Simpan di container
            app()->instance('auth.cached_user', $user);
        }

        return $next($request);
    }
}
