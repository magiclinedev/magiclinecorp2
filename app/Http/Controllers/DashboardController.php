<?php

namespace App\Http\Controllers;
use App\Models\Mannequin;
use App\Models\Category;
use App\Models\Company;
use App\Models\Type;
use App\Models\User;
use App\Models\AuditTrail;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Admin 1, Admin 2, Viewer can view
        Gate::define('users_access', function ($user) {
            return in_array($user->status, [1, 2, 3]);
        });
        // Only Admins can view
        Gate::define('admin_access', function ($user) {
            return in_array($user->status, [1, 2]);
        });
        // Only Admin 1 and Owner can view
        Gate::define('super_admin', function ($user) {
            return in_array($user->status, [1, 4]);
        });
        // For owner only
        Gate::define('owner', function ($user) {
            return in_array($user->status, [4]);
        });
    }

    public function index()
    {
        $user = Auth::user();
        $categories = Category::all();
        // $mannequins = Mannequin::all(); // Super users see all data
        // $companies = Company::all();
        $users = User::all();

         // Check if the user is a super user (status 1 or 4)
        if ($user->status == 1 || $user->status == 4) {
            // Super users see all mannequins and companies
            $mannequins = Mannequin::all();
            $companies = Company::all();
        } else {
             // admin 2 and viewer can only see mannequins and companies associated with their companies
            $mannequins = Mannequin::whereIn('company', $user->companies->pluck('name'))->get();
            $companies = $user->companies;
        }

        return view('dashboard')->with([
            'categories' => $categories,
            'mannequins' => $mannequins,
            'companies' => $companies,
            'users' => $users,
        ]);
    }
}
