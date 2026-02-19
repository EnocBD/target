<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class BlockSiteMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if site blocking is enabled
        if (env('BLOCK_SITE', 0) == 1) {
            // Allow login and registration routes
            if ($request->is('login') || $request->is('register') || $request->is('password/*')) {
                return $next($request);
            }

            // Check if user is authenticated
            if (Auth::check()) {
                // Check if user is active
                if (!Auth::user()->is_active) {
                    Auth::logout();
                    return redirect()->route('login')
                        ->with('warning', 'Tu cuenta no está activa. Nos pondremos en contacto contigo');
                }
                return $next($request);
            }

            // User not authenticated, redirect to login
            return redirect()->route('login');
        }

        return $next($request);
    }
}
