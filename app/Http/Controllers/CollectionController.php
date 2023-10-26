<?php

namespace App\Http\Controllers;
use App\Models\AuditTrail;
use App\Models\Category;
use App\Models\Company;
use App\Models\Mannequin;
use App\Models\Type;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\ImageOptimizer\OptimizerChainFactory;


class CollectionController extends Controller
{
    use WithPagination;

    // FILTER FOR USERS
    public function __construct()
    {
        // Admins can view
        Gate::define('admin_access', function ($user) {
            return in_array($user->status, [1, 2]);
        });
        // admin 1 and owner
        Gate::define('super_admin', function ($user) {
            return in_array($user->status, [1, 4]);
        });
        // owner view
        Gate::define('owner', function ($user) {
            return in_array($user->status, [4]);
        });
        // users can view except owner
        Gate::define('users', function ($user) {
            return in_array($user->status, [1, 2, 3]);
        });
    }

    //Collection View (Datatble for collection)
    public function index(Request $request)
    {
        // check user if logged in
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $status = $user->status;
        $selectedCategory = $request->input('category');
        $categories = Category::all();
        $selectedCompany = $request->query('company', '');

        //DATATBLES
        if($request->ajax()){

            // Get the page number and number of records per page from the request
            $page = $request->input('start') / $request->input('length') + 1;
            $perPage = $request->input('length');

            // Modify your query to load the data for the current page and order by recently added
            $query = Mannequin::query();
            $query->orderBy('updated_at', 'desc');

            // FILTERS
            $dateFilter = $request->input('date');
            $searchQuery = $request->input('search');
            $selectedCategory = $request->input('category');
            $selectedCompany = $request->query('company', '');

            // SEARCH in datatable
            if (!empty($searchQuery)) {
                $query->where(function ($subquery) use ($searchQuery) {
                    $subquery->where('itemref', 'like', '%' . $searchQuery . '%')
                            ->orWhere('company', 'like', '%' . $searchQuery . '%')
                            ->orWhere('po', 'like', '%' . $searchQuery . '%')
                            ->orWhere('description', 'like', '%' . $searchQuery . '%')
                            ->orWhere('category', 'like', '%' . $searchQuery . '%')
                            ->orWhere('type', 'like', '%' . $searchQuery . '%')
                            ->orWhere('addedBy', 'like', '%' . $searchQuery . '%');
                });
                // Apply an ORDER BY clause for search in ascending order
                $query->orderBy('created_at', 'asc')->get();
            }

            //filter
            if ($dateFilter == 'today') {
                // Modify your query to filter products added today(in dashboard)
                $query->whereDate('created_at', now()->toDateString());
            }
            if ($dateFilter == 'updatedToday') {
                // Modify your query to filter products added today(in dashboard)
                $query  ->whereDate('updated_at', now()->toDateString())
                        ->where('addedBy', 'NOT LIKE', '%Added%');
            }
            if (!empty($selectedCategory)) {
                $query->where('category', $selectedCategory);
            }
            if (!empty($selectedCompany)) {
                $query->where('company', $selectedCompany);
            }

            // Product Access()
            if ($user->status == 1) {
                // If user's status is 1(superadmin), query for activeStatus = 1
                $query->where('activeStatus', 1);
            }
            else {
                // If user's status is not 1, query based on user's companies
                $companies = $user->companies->pluck('name')->toArray();
                $query->whereIn('company', $companies);
            }

            // Get the total number of records
            $totalRecords = $query->count();

            // limit page loadding(only 10)
            $mannequins = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $data = collect();

            // DATATABLE DATA
            foreach ($mannequins as $m) {

                // IMAGE(Cache the image URL with a reasonable duration (e.g., 1 hour))
                $imageUrl = Cache::remember('image_' . $m->id, now()->addHour(1), function () use ($m) {
                    return $this->getImageUrl($m);
                });

                // Action Button
                $action = $this->generateActionButtons($user, $m);

                // Delete Message
                $confirmDeleteMessage = __('Are you sure you want to delete this item?');
                // data
                $data->push([
                    'checkbox'=> '<input type="checkbox" name="" class="prod_checkbox row-checkbox center pb-4 checkbox" value="'.$m->id.'">',
                    'image' => $imageUrl,
                    'itemref' => $m->itemref,
                    'company' => $m->company,
                    'category' => $m->category,
                    'type' => $m->type,
                    'addedBy' => $m->addedBy,
                    'action' => '
                    <div class="flex justify-between center">
                        <a href="' . route('collection.view_prod', ['id' => Crypt::encrypt($m->id)]) . '" class="bg-transparent hover:bg-green-500 text-green-700 font-semibold hover:text-white py-2 px-2 border border-green-500 hover:border-transparent rounded" title="View">
                        <i class="fas fa-eye"></i></a>
                        '.$action.'
                    </div>
                    <script>
                        function showDeleteConfirmation(event) {
                            event.preventDefault();
                            Swal.fire({
                                title: "Delete Item",
                                text: "' . $confirmDeleteMessage . '",
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#d33",
                                cancelButtonColor: "#3085d6",
                                confirmButtonText: "Yes, delete it!",
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // If confirmed, proceed with the deletion
                                    window.location.href = event.target.href;
                                }
                            });
                        }
                    </script>
                    ',
                ]);
            }
            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords, // Total number of records in the database
                'recordsFiltered' => $totalRecords, // Total number of records after filtering (if any)
                'data' => $data, // Your paginated data
            ]);
        }
        //COMPANIES
        $companies = $user->status == 1 ? Company::all() : $user->companies;
        $mannequins = Mannequin::where('activeStatus', 0)->get();

        return view('collection')->with([
            'categories' => $categories,
            'mannequins' => $mannequins,
            'companies' => $companies,
            'companyName' => $selectedCompany,
            'user' => $user,
            'status' => $status,
            // 'dropboxFiles' => $files,
        ]);
    }

    // IMAGE FOR DATATABLES
    private function getImageUrl($mannequin)
    {
        $imagePaths = explode(',', $mannequin->images);
        $firstImagePath = $imagePaths[0] ?? null;

        if (Storage::disk('dropbox')->exists($firstImagePath)) {
            return Storage::disk('dropbox')->url($firstImagePath);
        }
        else {
            return null;
        }
    }

    // ACTION BUTTONS FOR DATATABLES
    private function generateActionButtons($user, $mannequin)
    {
        $action = '';
        if (Gate::allows('super_admin', $user)) {
            if($mannequin->activeStatus == 1){
                $action = '
                <a href="' . route('collection.edit', ['id' => Crypt::encrypt($mannequin->id)]) . '" class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-2 border border-blue-500 hover:border-transparent rounded" title="Edit">
                <i class="fas fa-edit"></i></a>

                <a href="' . route('collection.trash', $mannequin->id) . '" class="btn-delete bg-transparent hover:bg-red-500 text-red-700 font-semibold hover:text-white py-2 px-2 border border-red-500 hover:border-transparent rounded" title="Delete"
                onclick="showDeleteConfirmation(event)">
                <i class="fas fa-trash-alt"></i>
                </a>
                ';
            }
            // For Trashcan
            else{
                $action = '
                <a href="' . route('collection.restore', $mannequin->id) . '" class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-2 border border-blue-500 hover:border-transparent rounded" title="Restore"
                onclick="showRestoreConfirmation(event)">
                <i class="fas fa-undo"></i></a>

                <a href="' . route('collection.delete', $mannequin->id) . '" class="btn-delete bg-transparent hover:bg-red-500 text-red-700 font-semibold hover:text-white py-2 px-2 border border-red-500 hover:border-transparent rounded" title="Delete"
                onclick="showDeleteConfirmation(event)">
                <i class="fas fa-trash-alt"></i>
                </a>
                ';
            }

        }
        elseif (Gate::allows('admin_access', $user)) {
            $action = '
            <a href="' . route('collection.edit', ['id' => Crypt::encrypt($mannequin->id)]) . '" class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-2 border border-blue-500 hover:border-transparent rounded" title="Edit">
            <i class="fas fa-edit"></i></a>
            ';
        }
        return $action;
    }

    //AddedBy User(used for all not just added by)
    private function setActionBy($model, $action)
    {
        if (Auth::check()) {
            $user = Auth::user()->name;
            $time = Carbon::now()->format('m/d/y - g:i A');

            $newHistory = "$action by $user at $time";

            $model->addedBy = $newHistory;
        }
    }

    // VIEW PRODUCT
    public function view($encryptedId)
    {
        try {
            // Decrypt the encrypted ID to get the original ID
            $id = Crypt::decrypt($encryptedId);
        } catch (DecryptException $e) {
            // Redirect with an error message if decryption fails
            return redirect()->route('collection')->with('danger_message', 'Invalid URL.');
        }

        // Find the mannequin using the decrypted ID
        $mannequin = Mannequin::find($id);

        // If the mannequin doesn't exist, redirect with an error message
        if (!$mannequin) {
            return redirect()->route('collection')->with('danger_message', 'Mannequin not found.');
        }

        // PDF
        $pdfPath = $mannequin->pdf;
        if($pdfPath)
        {
            $pdfUrls = $pdfPath; // Initialize with the PDF path
            // dd($pdfUrls);
            if (Storage::disk('dropbox')->exists($pdfPath)) {
                $pdfUrls = Storage::disk('dropbox')->url($pdfPath);
            }
            else {
                // Handle the case when the file does not exist
                $pdfUrls = null;
            }
        }
        else {
            $pdfUrls = null;
        }

        // COSTING / EXCEL
        $filePath = $mannequin->file;
        if($filePath)
        {
            $fileUrls = $filePath; // Initialize with the file path
            // dd($fileUrls);
            if (Storage::disk('dropbox')->exists($filePath)) {
                $fileUrls = Storage::disk('dropbox')->url($filePath);
            }
            else {
                // Handle the case when the file does not exist
                $fileUrls = null;
            }
        }
        else {
            $fileUrls = null;
        }

        // 3D PATH .stl
        $itemref = $mannequin->itemref; // Assuming $mannequin->itemref is the name of the 3D file
        $threeDPath = 'Magicline Database/files/3D/' . $itemref . '.stl'; // Concatenate the file name and path
        if($threeDPath)
        {
            $threeDUrls = $threeDPath; // Initialize with the threeD path
            // dd($threeDUrls);
            if (Storage::disk('dropbox')->exists($threeDPath)) {
                $threeDUrls = Storage::disk('dropbox')->url($threeDPath);
            }
            else {
                // Handle the case when the threeD does not exist
                $threeDUrls = null;
            }
        }
        else {
            $threeDUrls = null;
        }

        // Get the authenticated user
        $user = Auth::user();

        // Get company id from company name from the mannequin
        $companyName = $mannequin->company;
        $company = Company::where('name', $companyName)->first();
        $company_id = $company->id;

        // Check if user has the permission to view prices for the mannequin's company
        $canViewPriceForCompany = $user->companies()->where('companies.id', $company_id)->where('company_user.checkPrice', 1)->exists();

        // Determine if the user can view the price based on status and company permission
        $canViewPrice = ($user->status == 1 || $canViewPriceForCompany || $user->status == 4);

        // Return the view with the mannequin data, encrypted ID, and price view permission
        return view('collection-view', [
            'mannequin' => $mannequin,
            'encryptedId' => $encryptedId,
            'canViewPrice' => $canViewPrice,
            'pdfUrls' => $pdfUrls,
            'fileUrls' => $fileUrls,
            'threeDUrls' => $threeDUrls,
        ]);
    }

    // VIEW MODULE FOR ADD PRODUCT
    public function add()
    {
        $user = Auth::user();
        $types = Type::all();
        $categories = Category::all();

        if ($user->status == 1 || $user->status == 4) {
            $companies = Company::all();
        }
        else {
            $companies= Mannequin::whereIn('company', $user->companies->pluck('name'))->get();
            $companies = $user->companies;
        }

        return view('collection-add')->with(['categories' => $categories, 'types' => $types, 'companies' => $companies]);
    }

    //ADD DROPBOX in filepond
    public function uploadToDropbox(Request $request)
    {
        $photoPaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $photo) {

                $photoName = time() . '_' . $photo->getClientOriginalName();
                $path = 'Magicline Database/images/product/' . $photoName; // Relative path within Dropbox

                Storage::disk('dropbox')->put($path, file_get_contents($photo));

                // Store the Dropbox path in your array
                $photoPaths[] = $path;
            }
        }
        return $photoPaths;
    }

    // DROPBOX RFEMOVE IMAGES(not yet working)
    public function removeDropboxImage(Request $request)
    {
        // Get the image path from the request
        // $imagePath = $request->input('imagePath');

        // // Ensure that the image path is not empty
        // if (!empty($imagePath)) {
        //     // Delete the image from Dropbox
        //     if (Storage::disk('dropbox')->exists($imagePath)) {
        //         Storage::disk('dropbox')->delete($imagePath);
        //     }

        //     // You can also update your database to remove the reference to the image
        //     // For example, if you have a Product model, you can remove the image path from the product's image column.

        //     // Redirect back or to a specific route after deletion
        //     return redirect()->back()->with('success_message', 'Image deleted successfully');
        // }

        // // Handle the case where the image path is empty or the image doesn't exist
        // return redirect()->back()->with('danger_message', 'Image not found or deletion failed');

    }

    // ADD PRODUCT
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'po' => 'nullable|string|max:255|unique:mannequins,po',
            'itemRef' => 'required|string|max:255|unique:mannequins,itemref',
            'company' => 'required|nullable|string|max:255',
            'category' => 'required|nullable|string|max:255',
            'type' => 'required|nullable|string|max:255',
            'price' => 'nullable|numeric',
            'description' => 'nullable',
            'images' => 'required|array|min:1|max:8', // Ensure at least one image is present
            // 'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'file' => 'nullable|mimes:xlsx,xls|max:2048',//COSTING
            'pdf' => 'nullable|mimes:pdf|max:2048',
        ], [
            // 'po.unique' => 'The Purchase Order is already being used.',
            'itemRef.required' => 'The Item Reference is required.',
            'itemRef.unique' => 'The Item Reference is already taken.',
            'images.required' => 'At least one image is required.',
            // 'images.min' => 'At least one image is required.',
            // 'images.*.max' => 'The :attribute must be less than or equal to 2 MB.',
            // 'images.*.max' => 'The :attribute must be less than or equal to 2 MB.',
            'file.max' => 'The :attribute must be less than or equal to 2 MB.',
            'pdf.max' => 'The :attribute must be less than or equal to 2 MB.',
        ]);

        if ($validator->fails()) {
            return redirect('/collection-add')->with('danger_message', ' ')
                ->withErrors($validator)
                ->withInput();
        }

        //IMAGES PATH
        $inputArray = $request->input('images');

        $photoPaths = [];

        foreach ($inputArray as $element) {
            // Remove square brackets and backslashes
            $cleanedElement = str_replace(['[', ']', '\\', '"'], '', $element);

            // Remove leading and trailing whitespace
            $cleanedElement = trim($cleanedElement);

            // Add the cleaned element to the photoPaths array
            $photoPaths[] = $cleanedElement;
        }

        //FILE UPLOADS
        $excelFileName = null;
        $pdfFileName = null;

        // Optimize file upload
        $uploadFile = function ($fileType, $fileKey, $pathPrefix) use ($request) {
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = 'Magicline Database/files/'. $pathPrefix . $fileName; // Relative path within Dropbox

                Storage::disk('dropbox')->put($path, file_get_contents($file));

                return 'Magicline Database/files/' . $pathPrefix . $fileName;
            }
            return null;
        };

        $excelFileName = $uploadFile('file', 'file', 'excel/');
        $pdfFileName = $uploadFile('pdf', 'pdf', 'pdf/');

        // Create a new Mannequin instance and set its properties
        $mannequin = new Mannequin([
            'po' => strtoupper($request->po),
            'itemref' => strtoupper($request->itemRef),
            'company' => strtoupper($request->company),
            'category' => strtoupper($request->category),
            'type' => strtoupper($request->type),
            'price' => $request->price,
            'description' => $request->description,
            'images' => implode(',', $photoPaths),
            'activeStatus' => "1",
            'file' => $excelFileName,
            'pdf' => $pdfFileName
        ]);

        $this->setActionBy($mannequin, 'Added');
        try {
            if ($mannequin->save()) {
                // Add audit trail for the "Added" action with the item reference
                $activity = "Added " . $request->itemRef;
                $this->logAuditTrail(auth()->user(), $activity);

                return redirect('/collection')->with('success_message', 'Collection has been successfully added!');
            }
            else {
                return redirect('/collection')->with('danger_message', 'DATABASE ERROR!');
            }
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Database error: ' . $e->getMessage());
            return redirect('/collection')->with('danger_message', 'Database error occurred.');
        }
    }

    //View selected product for editing
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        // Get the authenticated user
        $user = Auth::user();

        // Fetch all categories
        $categories = Category::all();

        // Fetch the selected mannequin
        $mannequin = Mannequin::find($id);

        // If the mannequin doesn't exist, redirect with an error message
        if (!$mannequin) {
            return redirect()->route('collection')->with('danger_message', 'Mannequin not found.');
        }

        // Initialize variables
        $companies = [];
        $canViewPrice = false;

        // Check user's status and determine allowed companies and price visibility
        if ($user->status == 1 || $user->status == 4) {
            // If user's status is 1 or 4, fetch all companies and allow price view
            $companies = Company::all();
            $canViewPrice = true;
        }
        else {
            $companies= Mannequin::whereIn('company', $user->companies->pluck('name'))->get();
            $companies = $user->companies;

            $companyName = $mannequin->company_name; // Assuming the company name is stored in the 'company_name' column
            $company = Company::where('name', $companyName)->first();

            // Check if user can view price for the selected company
            if ($company && $user->companies()->where('companies.id', $company->id)->where('company_user.checkPrice', 1)->exists()) {
                $canViewPrice = true;
            }
        }

        // Fetch all types
        $types = Type::all();

        // Return the view with the required data
        return view('collection-edit')->with([
            'categories' => $categories,
            'mannequin' => $mannequin,
            'types' => $types,
            'companies' => $companies,
            'canViewPrice' => $canViewPrice,
        ]);
    }

    // UPDATE PRODUCT
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            // 'po' => 'nullable|string|max:255|unique:mannequins,po,' . $id,
            'itemref' => 'required|string|max:255|unique:mannequins,itemref,' . $id,
            'company' => 'required|nullable|string|max:255',
            'category' => 'required|nullable|string|max:255',
            'type' => 'required|nullable|string|max:255',
            'price' => 'nullable|numeric',
            'description' => 'nullable',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'file' => 'nullable|mimes:xlsx,xls|max:2048',
            'pdf' => 'nullable|mimes:pdf|max:2048',
        ], [
            // 'po.unique' => 'The Purchase Order is already being used.',
            'itemref.required' => 'The Item Reference is required.',
            'itemref.unique' => 'The Item Reference has already been taken.',
            'images.*.max' => 'The :attribute must be less than or equal to 2 MB.',
            'file.max' => 'The :attribute must be less than or equal to 2 MB.',
            'pdf.max' => 'The :attribute must be less than or equal to 2 MB.',
        ]);

        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()->back()->with('danger_message', ' ')
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $mannequin = Mannequin::findOrFail($id);

        // Store the original itemref for the audit trail
        $originalItemref = $mannequin->itemref;

        // Define the fields that can be updated
        $fillableFields = ['po', 'company', 'category', 'type', 'price', 'description'];

        $updates = [];

        foreach ($fillableFields as $field) {
            if ($request->has($field) && $request->input($field) !== $mannequin->{$field}) {
                $mannequin->{$field} = $request->input($field);
                $updates[] = strtoupper($field);
            }
        }

        // Handle the "itemref" field separately for old and new values
        if ($request->has('itemref') && $request->input('itemref') !== $mannequin->itemref) {
            $oldItemref = $mannequin->itemref; // Store the old itemref
            $newItemref = $request->input('itemref'); // Store the new itemref
            $mannequin->itemref = $newItemref;
            $updates[] = "Itemref (new: $newItemref, old: $oldItemref)";
        }

        // // Handle image uploads
        // // Optimize image upload using Dropbox
        // if ($request->hasFile('images')) {
        //     $imagePaths = [];

        //     foreach ($request->file('images') as $photo) {
        //         $photoName = time() . '_' . $photo->getClientOriginalName();
        //         $path = '/Magicline Database/images/product/' . $photoName; // Dropbox path

        //         // Upload the image to Dropbox
        //         Storage::disk('dropbox')->put($path, file_get_contents($photo->path()));

        //         // Store the Dropbox path in your database
        //         $photoPaths[] = $path;
        //     }

        //     // Remove old images from Dropbox
        //     $oldImagePaths = explode(',', $mannequin->images);
        //     foreach ($oldImagePaths as $oldImagePath) {
        //         // Delete the old image from Dropbox
        //         Cache::forget('image_' .  $mannequin->id);

        //         Storage::disk('dropbox')->delete($oldImagePath);
        //     }

        //     // Update the images field in the database
        //     $mannequin->images = implode(',', $photoPaths);
        //     $updates[] = 'Images';
        // }

        // Handle file uploads
        $updateFile = function ($fileKey, $pathPrefix) use ($request, $mannequin) {
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);

                // Remove the old file if it exists
                if ($mannequin->{$fileKey}) {
                    Storage::disk('dropbox')->delete($mannequin->{$fileKey});
                }

                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = 'Magicline Database/files/' . $pathPrefix . $fileName; // Relative path within Dropbox

                // Use the Laravel Filesystem to store the updated file
                Storage::disk('dropbox')->put($path, file_get_contents($file));

                // Update the file field in the database
                $mannequin->{$fileKey} = $path;
                // $mannequin->save();
            }
        };

        // Update the "excel" and "pdf" files
        $updateFile('file', 'excel/');
        $updateFile('pdf', 'pdf/');

        // IMAGE UPLOAD
        // Get the deleted image paths from the request
        $deletedImages = explode(',', $request->input('deleted_images'));

        if (!empty($deletedImages)) {
            // Process and remove the deleted images from your storage
            foreach ($deletedImages as $deletedImage) {
                if (Storage::disk('dropbox')->exists($deletedImage)) {
                    Storage::disk('dropbox')->delete($deletedImage);
                    $updates[] = 'Images';
                }
            }
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];

            foreach ($request->file('images') as $photo) {
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $path = '/Magicline Database/images/product/' . $photoName; // Dropbox path

                // Upload the image to Dropbox
                Cache::forget('image_' .  $mannequin->id);
                Storage::disk('dropbox')->put($path, file_get_contents($photo->path()));

                // Store the Dropbox path in your database
                $imagePaths[] = $path;
            }

            // Combine the new image paths with the existing ones
            $mannequin->images = implode(',', array_merge($imagePaths, explode(',', $mannequin->images)));
            $updates[] = 'Images';
        }

        // MODIFIED BY
        $this->setActionBy($mannequin, 'Modified');

        $mannequin->save();

        // Generate the "Updated" message based on the fields that were updated
        if (!empty($updates)) {
            $activity = "Updated " . implode(', ', $updates) . " of $originalItemref";
            $this->logAuditTrail(auth()->user(), $activity);
        }

        return redirect()->back()->with('success_message', 'Product details updated successfully.');
    }

    //SHOW TRASHCAN
    public function trashcan(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $selectedCategory = $request->input('category');
        $categories = Category::all();
        $selectedCompany = $request->query('company', '');

         //DATATBLES
        if($request->ajax()){

            // Get the page number and number of records per page from the request
            $page = $request->input('start') / $request->input('length') + 1;
            $perPage = $request->input('length');

            // Modify your query to load the data for the current page
            $query = Mannequin::query();


            $query->orderBy('created_at', 'desc');

            $searchQuery = $request->input('search');
            $selectedCategory = $request->input('category');
            $selectedCompany = $request->query('company', '');
            $dateFilter = $request->input('date');

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
            if ($dateFilter == 'today') {
                // Modify your query to filter products added today
                $query->whereDate('created_at', now()->toDateString());
            }
            if (!empty($selectedCategory)) {
                $query->where('category', $selectedCategory);
            }
            if (!empty($selectedCompany)) {
                $query->where('company', $selectedCompany);
            }

            // Product Access
            if ($user->status == 1) {
                // If user's status is 1, query for activeStatus = 1
                $query->where('activeStatus', 0);
            }
            else {
                // If user's status is not 1, query based on user's companies
                $companies = $user->companies->pluck('name')->toArray();
                $query->whereIn('company', $companies);
            }

            $totalRecords = $query->count(); // Get the total number of records

            $mannequins = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $data = collect();

            // DATATABLE DATA
            foreach ($mannequins as $m) {

                // Cache the image URL with a reasonable duration (e.g., 1 hour)
                $imageUrl = Cache::remember('image_' . $m->id, now()->addHour(1), function () use ($m) {
                    return $this->getImageUrl($m);
                });

                // Action Button
                $action = $this->generateActionButtons($user, $m);

                // Delete Message
                $confirmDeleteMessage = __('Are you sure you want to delete this item?');
                $confirmRestoreMessage = __('Are you sure you want to restore this item?');
                $data->push([
                    'checkbox'=> '<input type="checkbox" name="" class="prod_checkbox row-checkbox center pb-4 checkbox" value="'.$m->id.'">',
                    'image' => $imageUrl,
                    'itemref' => $m->itemref,
                    'company' => $m->company,
                    'category' => $m->category,
                    'type' => $m->type,
                    'addedBy' => $m->addedBy,
                    'action' => '
                    <div class="flex justify-between">
                        <a href="' . route('collection.view_prod', ['id' => Crypt::encrypt($m->id)]) . '" class="bg-transparent hover:bg-green-500 text-green-700 font-semibold hover:text-white py-2 px-2 border border-green-500 hover:border-transparent rounded" title="View">
                        <i class="fas fa-eye"></i></a>
                        '.$action.'
                    </div>
                    <script>
                        function showDeleteConfirmation(event) {
                            event.preventDefault();
                            Swal.fire({
                                title: "Delete Item",
                                text: "' . $confirmDeleteMessage . '",
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#d33",
                                cancelButtonColor: "#3085d6",
                                confirmButtonText: "Yes, delete it!",
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // If confirmed, proceed with the deletion
                                    window.location.href = event.target.href;
                                }
                            });
                        }
                        function showRestoreConfirmation(event) {
                            event.preventDefault();
                            Swal.fire({
                                title: "Restore Item",
                                text: "' . $confirmRestoreMessage . '",
                                icon: "info",
                                showCancelButton: true,
                                confirmButtonColor: "#d33",
                                cancelButtonColor: "#3085d6",
                                confirmButtonText: "Yes, restore it!",
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // If confirmed, proceed with the deletion
                                    window.location.href = event.target.href;
                                }
                            });
                        }
                    </script>
                    ',
                ]);
            }
            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords, // Total number of records in the database
                'recordsFiltered' => $totalRecords, // Total number of records after filtering (if any)
                'data' => $data, // Your paginated data
            ]);
        }
        $mannequins = Mannequin::where('activeStatus', '<', 1)->get();
        return view('collection-trash')->with(['mannequins' => $mannequins,]);
    }

    //DELETE(to trashcan make active status = 0)
    public function trash($id)
    {
        if (!$id) {
            return redirect()->back()->with('danger_message', 'Item not Deleted.');
        }
        // $mannequinId = Crypt::decrypt($id);
        $mannequin = Mannequin::findOrFail($id);
        // Cache::forget('images_' . $mannequin->id);
        if ($mannequin) {
            // Store the original item reference for the audit trail
            $originalItemref = $mannequin->itemref;

            $mannequin->activeStatus = 0;
            $this->setActionBy($mannequin, 'Deleted');
            $mannequin->save();

            // Add audit trail for the "Deleted" action with the original item reference
            $activity = "Trashed $originalItemref";
            $this->logAuditTrail(auth()->user(), $activity);

            return redirect()->back()->with('success_message', 'Item deleted.');
        }
        else {
            return redirect()->back()->with('danger_message', 'Item not deleted.');
        }
    }

    // Trash Multiple Products
    public function trashMultiple(Request $request)
    {
        $selectedIds = $request->input('ids');

        // Retrieve the selected items
        $selectedItems = Mannequin::whereIn('id', $selectedIds)->get();

        foreach ($selectedItems as $item) {
            //(trashed by)
            $this->setActionBy($item, 'Trashed');
            if ($item->activeStatus == 1) {
                // If activeStatus is 1, set it to 0 and add an audit trail
                $item->update(['activeStatus' => 0]);
                $activity = "Trashed {$item->itemref}";
                $this->logAuditTrail(auth()->user(), $activity);
            }
            else {
                // If activeStatus is 0, delete the item and add an audit trail
                foreach (explode(',', $item->images) as $imagePath) {
                    Storage::disk('dropbox')->delete($imagePath);
                }
                $activity = "Deleted {$item->itemref}";
                $this->logAuditTrail(auth()->user(), $activity);
                // $this->setActionBy($item, 'Deleted');
                $item->delete();
            }
        }
        return response()->json(['message' => 'Items updated and deleted successfully']);
    }

    // RESTORE MULTIPLE
    public function restoreMultiple(Request $request)
    {
        $selectedIds = $request->input('ids');

        // Retrieve the selected items
        $selectedItems = Mannequin::whereIn('id', $selectedIds)->get();

        foreach ($selectedItems as $item) {
            $this->setActionBy($item, 'Restored');
            if ($item->activeStatus == 0) {
                // If activeStatus is 0, set it to 1 and add an audit trail
                $item->update(['activeStatus' => 1]);
                $activity = "Restored {$item->itemref}";
                $this->logAuditTrail(auth()->user(), $activity);
            }
        }
        return response()->json(['message' => 'Items restored successfully']);
    }

    //Delete (PERMANENTLY from database to storage)
    public function destroy($id)
    {
        $mannequin = Mannequin::findOrFail($id);
        Cache::forget('images_' . $mannequin->id);

        // Store the original item reference for the audit trail
        $originalItemref = $mannequin->itemref;

        // Delete associated images
        foreach (explode(',', $mannequin->images) as $imagePath) {
            Storage::disk('dropbox')->delete($imagePath);
        }

        $pdfPath = $mannequin->pdf;
        Storage::disk('dropbox')->delete($pdfPath);

        // Delete the Mannequin record from the database
        $mannequin->delete();

        // Add audit trail for the "Deleted Permanently" action with the original item reference
        $activity = "Deleted Permanently $originalItemref";
        $this->logAuditTrail(auth()->user(), $activity);

        // Retrieve the updated $mannequins collection after deletion
        $mannequins = Mannequin::where('activeStatus', '<', 1)->get();

        return redirect()->back()->with('success_message', 'Item deleted permanently.');
    }

    //RESTORE trashed prod(active status = 1 again)
    public function restore($id)
    {
        if (!$id) {
            return redirect()->back()->with('danger_message', 'Item not restored.');
        }

        $mannequin = Mannequin::findOrFail($id);
        // Cache::forget('images_' . $mannequin->id);

        if ($mannequin) {
            // Store the original item reference for the audit trail
            $originalItemref = $mannequin->itemref;

            $mannequin->activeStatus = 1;
            $this->setActionBy($mannequin, 'Restored');
            $mannequin->save();

            // Add audit trail for the "restored" action with the original item reference
            $activity = "Restored $originalItemref";
            $this->logAuditTrail(auth()->user(), $activity);

            return redirect()->back()->with('success_message', 'Item Restored.');
        }
        else{
            return redirect()->back()->with('danger_message', 'Item not Restored.');
        }
    }

    //SHOW CATEGORIES MODULE
    public function category()
    {
        $categories = Category::all();
        return view('collection-category')->with(['categories' => $categories]);
    }

    // ADD CATEGORY
    public function store_category(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'category' => 'required|unique:categories,name'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('danger_message', 'Duplicate/No Input');
        }

        $category = new Category();
        $category->name = strtoupper($request->category);

        // Automatically set the 'addedBy' field with the authenticated user's name
        if (Auth::check()) {
            $user = Auth::user()->name;
            $time = Carbon::now()->format('m/d/y - g:i A');
            $category->addedBy = "$user at $time";
        }

        $category->save();

        return redirect()->route('collection.category')->with('success_message', 'Category added successfully!');
    }

    //Delete Category(PERMANENTLY)
    public function trash_category($id)
    {
        $type = type::findOrFail($id);
        $type->delete();

        return response()->json(['success' => true]);
    }

    // SHOW TYPE MODULE
    public function type()
    {
        $types = Type::all();
        return view('collection-type')->with(['types' => $types]);
    }

    // ADD TYPE
    public function store_type(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'required|unique:types,name'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('danger_message', 'Duplicate/No input');
        }

        $type = new type();
        $type->name = strtoupper($request->type);

        // Automatically set the 'addedBy' field with the authenticated user's name
        if (Auth::check()) {
            $user = Auth::user()->name;
            $time = Carbon::now()->format('m/d/y - g:i A');
            $type->addedBy = "$user at $time";
        }

        $type->save();

        return redirect()->route('collection.type')->with('success_message', 'type added successfully!');
    }

    //Delete type(PERMANENTLY)
    public function trash_type($id)
    {
        $type = Type::findOrFail($id);
        $type->delete();

        return response()->json(['success' => true]);
    }

    //Audit Trail
    public function logAuditTrail($user, $activity)
    {
        $log = new AuditTrail;
        $log->name = $user->name;
        $log->user_id = $user->id;
        $log->activity = $activity;
        $log->save();
    }

    // public function downloadPDF($encryptedId)
    // {
    //     try {
    //         // Decrypt the encrypted ID to get the original ID
    //         $id = Crypt::decrypt($encryptedId);
    //     } catch (DecryptException $e) {
    //         // Redirect with an error message if decryption fails
    //         return redirect()->route('collection')->with('danger_message', 'Invalid URL.');
    //     }

    //     // check user if logged in
    //     $user = Auth::user();
    //     if (!$user) {
    //         return redirect()->route('login');
    //     }
    //     // Find the mannequin using the decrypted ID
    //     $mannequin = Mannequin::find($id);

    //     $companyName = $mannequin->company;
    //     $company = Company::where('name', $companyName)->first();
    //     $companyLogo=$company->images;


    //     // IMAGES

    //     $company = $mannequin->company;

    //     // Extract image paths and cache key
    //     $imagePaths = explode(',', $mannequin->images);
    //     $imageCacheKey = 'images_' . $mannequin->id;

    //     // Fetch image URLs from Dropbox disk and cache them
    //     $imageUrls = Cache::remember($imageCacheKey, now()->addHours(1), function () use ($imagePaths) {
    //         return array_filter(array_map(function ($imagePath) {
    //             return Storage::disk('dropbox')->exists($imagePath)
    //                 ? Storage::disk('dropbox')->url($imagePath)
    //                 : null;
    //         }, $imagePaths));
    //     });


    //     return view('pdfMaker')->with([
    //         'mannequin' => $mannequin,
    //         'companyLogo' => $companyLogo,
    //         'imageUrls' => $imageUrls,
    //     ]);

    // }

    public function downloadPDF($encryptedId)
    {
        try {
            // Decrypt the encrypted ID to get the original ID
            $id = Crypt::decrypt($encryptedId);
        } catch (DecryptException $e) {
            // Redirect with an error message if decryption fails
            return redirect()->route('collection')->with('danger_message', 'Invalid URL.');
        }

        // Find the mannequin using the decrypted ID
        $mannequin = Mannequin::find($id);

        $companyName = $mannequin->company;
        $company = Company::where('name', $companyName)->first();
        $companyLogo=$company->images;

        // dd($companyLogo);

        // $company = $mannequin->company;

        // Extract image paths and cache key
        $imagePaths = explode(',', $mannequin->images);
        $imageUrls = [];

        // Limit the loop to the first 5 elements
        foreach (array_slice($imagePaths, 0, 5) as $imagePath) {
            if (Storage::disk('dropbox')->exists($imagePath)) {
                $imageUrls[] = Storage::disk('dropbox')->url($imagePath);
            }
        }

        // Load the PDF view and pass the necessary data
        $pdf = PDF::loadView('pdfMaker', compact('mannequin', 'companyLogo', 'imagePaths', 'imageUrls'));

        // Set the page orientation to landscape
        $pdf->setPaper('landscape');

        // Define the PDF filename
        $pdfFileName = 'mannequin_' . $mannequin->id . '.pdf';

        // Return the PDF for download (stream) with the specified filename
        return $pdf->stream($pdfFileName);
    }

}

