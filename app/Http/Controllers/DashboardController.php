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
        // Define gates here
        Gate::define('users_access', function ($user) {
            return in_array($user->status, [1, 2, 3]);
        });
        Gate::define('admin_access', function ($user) {
            return in_array($user->status, [1, 2]);
        });
        Gate::define('super_admin', function ($user) {
            return in_array($user->status, [1, 4]);
        });
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

        if ($user->status == 1 || $user->status == 4) {
            $mannequins = Mannequin::all(); // Super users see all data
            $companies = Company::all();
        } else {
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
