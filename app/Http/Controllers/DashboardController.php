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
        }
        else {
             // admin 2 and viewer can only see mannequins and companies associated with their companies
            $mannequins = Mannequin::whereIn('company', $user->companies->pluck('name'))->get();
            $companies = $user->companies;
        }

        $productsCreatedToday = Mannequin::whereDate('created_at', Carbon::today())
        ->where('activeStatus', 1)
        ->count();

        $productsUpdatedToday = Mannequin::whereDate('updated_at', Carbon::today())
        ->where('activeStatus', 1)
        ->count();

        if($request->ajax()){

            // Get the page number and number of records per page from the request
            $page = $request->input('start') / $request->input('length') + 1;
            $perPage = $request->input('length');

            // Modify your query to load the data for the current page
            $dateFilter = $request->input('dateFilter');

            if ($dateFilter == 'today') {
                // Modify your query to filter products added today
                $query = Mannequin::whereDate('created_at', now()->toDateString());
            } else {
                // Your regular query for other filters or all products
                $query = Mannequin::query();
            }

            $query->orderBy('created_at', 'desc');

            $searchQuery = $request->input('search');
            $selectedCategory = $request->input('category');
            $selectedCompany = $request->query('company', '');
            // $dateFilter = $request->input('date');

            // Modify your query to add search functionality
            if (!empty($searchQuery)) {
                $query->where(function ($subquery) use ($searchQuery) {
                    $subquery->where('itemref', 'like', '%' . $searchQuery . '%')
                            ->orWhere('company', 'like', '%' . $searchQuery . '%')
                            ->orWhere('category', 'like', '%' . $searchQuery . '%')
                            ->orWhere('type', 'like', '%' . $searchQuery . '%')
                            ->orWhere('addedBy', 'like', '%' . $searchQuery . '%');
                });
            }

            // FILTERS
            // if ($dateFilter == 'today') {
            //     // Modify your query to filter products added today
            //     $query->whereDate('created_at', now()->toDateString());
            // }
            if (!empty($selectedCategory)) {
                $query->where('category', $selectedCategory);
            }
            if (!empty($selectedCompany)) {
                $query->where('company', $selectedCompany);
            }

            // Product Access

            $query->where('activeStatus', 1);

            $totalRecords = $query->count(); // Get the total number of records

            $mannequins = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $data = collect();

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
                    'created_at' => $m->created_at->toDateTimeString(),
                    // 'created_at' => $m->created_at->toDateTimeString(),
                    'action' => '
                    <a href="'.route('collection.view_prod', ['id' => Crypt::encrypt($m->id)]).'" class="bg-transparent hover:bg-green-500 text-green-700 font-semibold hover:text-white py-2 px-2 border border-green-500 hover:border-transparent rounded">
                    <i class="fas fa-eye"></i>View</a>',
                ]);
            }

            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords, // Total number of records in the database
                'recordsFiltered' => $totalRecords, // Total number of records after filtering (if any)
                'data' => $data, // Your paginated data
            ]);
        }
        return view('dashboard')->with([
            'categories' => $categories,
            'mannequins' => $mannequins,
            'companies' => $companies,
            // 'companyName' => $selectedCompany,
            'user' => $user,
            'users' => $users,
            'productsCreatedToday' => $productsCreatedToday,
            'productsUpdatedToday' => $productsUpdatedToday,
        ]);
    }
}
