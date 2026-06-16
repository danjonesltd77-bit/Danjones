<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();

        $hasAdminAccess = $user->hasRole('admin') || $user->hasRole('super-admin');

        if (! $hasAdminAccess) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
