<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->status == 1 || auth()->user()->status == 4) {
            return $next($request);
        }

        return redirect('/dashboard')->with('danger_message', 'You do not have permission to access this content.');
    }
}
