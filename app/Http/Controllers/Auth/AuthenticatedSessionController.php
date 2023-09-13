<?php

namespace App\Http\Controllers\Auth;
use App\Models\AuditTrail;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use App\Events\UserLoggedIn;
use Illuminate\Session\Store;
// use App\Events\UserLoggedIn;

use Illuminate\Support\Facades\Session;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');

    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user(); // Retrieve the authenticated user

        $request->session()->regenerate();

        event(new UserLoggedIn($user->name)); // Pass the user's name to the event

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function logActivity($user, $activity, $timestamp)
    {
        $log = new ActivityLog;
        $log->user_id = $user->id;
        $log->name = $user->name;
        $log->activity = $activity;
        // $log->timestamp = $timestamp;
        $log->save();
    }
}
