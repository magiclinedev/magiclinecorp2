<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            // Check if the user's last activity time is within the allowed session timeout
            $lastActivity = Auth::user()->last_activity;

            if (time() - strtotime($lastActivity) > config('session.lifetime') * 10) {
                Auth::logout();

                return redirect()->route('login')
                    ->with('session_timeout', 'Your session has timed out. Please log in again.');
            }
        }

        return $next($request);
    }
}
