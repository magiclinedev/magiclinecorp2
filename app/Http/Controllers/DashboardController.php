<?php

namespace App\Http\Controllers;
use App\Models\Mannequin;
use App\Models\Category;
use App\Models\Company;
use App\Models\Type;
use App\Models\User;
use App\Models\AuditTrail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Str;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;

use DataTables;

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

    public function index(Request $request)
    {
        $user = Auth::user();
        $categories = Category::all();
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

        if($request->ajax()){

            $data = collect();

            if(count($mannequins) > 0) {
                foreach ($mannequins as $m) {
                    // Cache the image URL with a reasonable duration (e.g., 1 hour)
                    $imageCacheKey = 'image_' . $m->id;
                    $imageUrl = Cache::remember($imageCacheKey, now()->addHour(1), function () use ($m) {
                        $imagePaths = explode(',', $m->images);
                        $firstImagePath = $imagePaths[0] ?? null;

                        if (Storage::disk('dropbox')->exists($firstImagePath)) {
                            return Storage::disk('dropbox')->url($firstImagePath);
                        } else {
                            return null;
                        }
                    });
                    $data->push([
                        'image' => $imageUrl,
                        'itemref' => $m->itemref,
                        'company' => $m->company,
                        'category' => $m->category,
                        'type' => $m->type,
                        'addedBy' => $m->addedBy,
                        // 'created_at' => $m->created_at->toDateTimeString(),
                        'action' => '
                        <a href="'.route('collection.view_prod', ['id' => Crypt::encrypt($m->id)]).'" class="bg-transparent hover:bg-green-500 text-green-700 font-semibold hover:text-white py-2 px-2 border border-green-500 hover:border-transparent rounded">
                        <i class="fas fa-eye"></i>View</a>',
                    ]);

                        // <a href="'.route('collection.edit', ['id' => Crypt::encrypt($m->id)]).'" class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-2 border border-blue-500 hover:border-transparent rounded">
                        // <i class="fas fa-edit"></i></a>

                        // <a href="'.route('collection.trash', $m->id).'" class="btn-delete bg-transparent hover:bg-red-500 text-red-700 font-semibold hover:text-white py-2 px-2 border border-red-500 hover:border-transparent rounded">
                        // <i class="fas fa-trash-alt"></i></button>
                }
            }

            return DataTables::of($data)->rawColumns(['action'])->make(true);
        }
        return view('dashboard')->with([
            'categories' => $categories,
            'mannequins' => $mannequins,
            'companies' => $companies,
            // 'companyName' => $selectedCompany,
            'user' => $user,
            'users' => $users,
        ]);
    }
}
