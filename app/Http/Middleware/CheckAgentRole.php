<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAgentRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            // Check if the authenticated user has the "agent" role
            if (Auth::user()->role === 'agent') {
                return $next($request);
            }

            // Optionally, add a different response if the role doesn't match
            // For example, redirect to a different page or return a different error
        }

        return response()->view('errors.404', [
            'title' => 'Upgrade Required',
            'message' => 'To access our agency services, please upgrade your account. Simply navigate to the dashboard page and submit an upgrade request. Our team will assist you with the next steps. Thank you for choosing our services!',
        ], 404);

    }
}
