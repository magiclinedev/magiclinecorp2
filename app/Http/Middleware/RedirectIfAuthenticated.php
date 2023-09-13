<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Check if the user's last activity time is within the allowed session timeout
                $lastActivity = Auth::guard($guard)->user()->last_activity;

                if (time() - strtotime($lastActivity) > config('session.lifetime') * 60) {
                    Auth::guard($guard)->logout();

                    return redirect()->route('login')
                        ->with('session_timeout', 'Your session has timed out. Please log in again.');
                }

                // User is authenticated and within the session timeout, redirect them to the home page
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
