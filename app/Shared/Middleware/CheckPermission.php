<?php

namespace App\Shared\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        foreach ($permissions as $permissionGroup) {

            // AND condition: permission:a&b&c
            if (str_contains($permissionGroup, '&')) {
                $required = explode('&', $permissionGroup);

                foreach ($required as $perm) {
                    if (!$user->hasPermission($perm)) {
                        abort(403, 'You do not have permission to access this resource.');
                    }
                }

                return $next($request);
            }

            // OR condition: permission:a|b|c
            if (str_contains($permissionGroup, '|')) {
                $options = explode('|', $permissionGroup);

                foreach ($options as $perm) {
                    if ($user->hasPermission($perm)) {
                        return $next($request);
                    }
                }

                abort(403, 'You do not have permission to access this resource.');
            }

            // Single permission
            if ($user->hasPermission($permissionGroup)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}
