<?php

namespace App\Http\Controllers;
use App\Models\Collection as P;
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

use Spatie\Dropbox\Client as DropboxClient;

use DataTables;

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

    //Collection View
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $categories = Category::all();
        $selectedCompany = $request->query('company', '');
        // $companies = Company::all(); // Fetch all companies

        if ($user->status == 1) {
            $mannequins = Mannequin::all(); // Super users see all data
            $companies = Company::all();
        } else {
            $mannequins = Mannequin::whereIn('company', $user->companies->pluck('name'))->get();
            $companies = $user->companies;
        }

        return view('collection')->with([
            'categories' => $categories,
            'mannequins' => $mannequins,
            'companies' => $companies,
            'companyName' => $selectedCompany,
            'user' => $user,
            // 'dropboxFiles' => $files,
        ]);
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

    // ADD PRODUCT
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'po' => 'nullable|string|max:255|unique',
            'itemRef' => 'required|string|max:255|unique',
            'company' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'price' => 'nullable|numeric',
            'description' => 'nullable',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'file' => 'nullable|mimes:xlsx,xls|max:2048',
            'pdf' => 'nullable|mimes:pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect('/collection-add')->with('danger_message', 'Input Incorrect or Files Too Large(Max 2MB): Please check the form fields and try again.')
                ->withErrors($validator)
                ->withInput();
        }

        //uploaded files
        $photoPaths = [];
        $excelFileName = null;
        $pdfFileName = null;

        // Optimize image upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $photo) {
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $path = 'Magicline Database/images/product/' . $photoName; // Relative path within Dropbox

                // Upload the image to Dropbox
                Storage::disk('dropbox')->put($path, file_get_contents($photo));

                // Store the Dropbox path in your database
                $photoPaths[] = $path;
            }
        }

        // Optimize file upload
        $uploadFile = function ($fileType, $fileKey, $pathPrefix) use ($request) {
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/files/' . $pathPrefix, $fileName);
                return 'files/' . $pathPrefix . $fileName;
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
        ]);

        $this->setActionBy($mannequin, 'Added');
        $mannequin->activeStatus = "1";

        if (!empty($photoPaths)) {
            $mannequin->images = implode(',', $photoPaths);
        }

        if ($excelFileName !== null) {
            $mannequin->file = $excelFileName;
        }

        if ($pdfFileName !== null) {
            $mannequin->pdf = $pdfFileName;
        }

        if ($mannequin->save()) {
            // Add audit trail for the "Added" action with the item reference
            $activity = "Added " . $request->itemRef;
            $this->logAuditTrail(auth()->user(), $activity);

            return redirect('/collection')->with('success_message', 'Collection has been successfully added!');
        }
        else {
            return redirect('/collection')->with('danger_message', 'DATABASE ERROR!');
        }
    }

    //View selected product for editing
    public function edit($id)
    {
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

    //EDIT Product
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        // Find the Mannequin by ID or throw a 404 error if not found
        $mannequin = Mannequin::findOrFail($id);

        // Keep the original itemref for audit trail
        $originalItemref = $mannequin->itemref;

        // Create an array to store updates
        $updates = [];

        // Update the fields using the request input directly
        if ($request->has('po') && $request->input('po') !== $mannequin->po) {
            $mannequin->po = $request->input('po');
            $updates[] = 'PO';
        }

        // Handle the "itemref" field separately for old and new values
        if ($request->has('itemref') && $request->input('itemref') !== $mannequin->itemref) {
            $oldItemref = $mannequin->itemref; // Store the old itemref
            $newItemref = $request->input('itemref'); // Store the new itemref
            $mannequin->itemref = $newItemref;
            $updates[] = "Itemref (new: $newItemref, old: $oldItemref)";
        }

        if ($request->has('company') && $request->input('company') !== $mannequin->company) {
            $mannequin->company = $request->input('company');
            $updates[] = 'Company';
        }
        if ($request->has('category') && $request->input('category') !== $mannequin->category) {
            $mannequin->category = $request->input('category');
            $updates[] = 'Category';
        }
        if ($request->has('type') && $request->input('type') !== $mannequin->type) {
            $mannequin->type = $request->input('type');
            $updates[] = 'Type';
        }
        if ($request->has('price') && $request->input('price') !== $mannequin->price) {
            $mannequin->price = $request->input('price');
            $updates[] = 'Price';
        }
        if ($request->has('description') && $request->input('description') !== $mannequin->description) {
            $mannequin->description = $request->input('description');
            $updates[] = 'Description';
        }

        // Update images
        if ($request->hasFile('images')) {
            // Remove the old images if they exist
            foreach (explode(',', $mannequin->images) as $oldImagePath) {
                // Delete the old image from storage
                Storage::delete('public/product/' . trim($oldImagePath));
            }

            $imagePaths = [];

            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/images/product', $imageName);
                $imagePaths[] = 'images/product/' . $imageName;
            }

            // Update the images field in the database
            $mannequin->images = implode(',', $imagePaths);

            $updates[] = 'Images';
        }

        //FOR COSTING
        if ($request->hasFile('file')) {
            // Remove the old file if it exists
            if ($mannequin->file) {
                // Delete the old file from storage
                Storage::delete('public/files/' . $mannequin->file);
            }

            $file = $request->file('file');
            $filename = $file->getClientOriginalName();

            // Store the file in the desired storage location (public disk in this case)
            $file->storeAs('public/files', $filename);

            // Update the file field in the database
            $mannequin->file = $filename;

            $updates[] = 'File';
        }
        //FOR PDF
        if ($request->hasFile('pdf')) {
            // Remove the old PDF if it exists
            if ($mannequin->pdf) {
                // Delete the old PDF from storage
                Storage::delete('public/files/' . $mannequin->pdf);
            }

            $pdfFile = $request->file('pdf');
            $pdfFilename = $pdfFile->getClientOriginalName();

            // Store the PDF file in the desired storage location (public disk in this case)
            $pdfFile->storeAs('public/files', $pdfFilename);

            // Update the pdf field in the database
            $mannequin->pdf = $pdfFilename;

            $updates[] = 'PDF';
        }

        $this->setActionBy($mannequin, 'Modified');

        $mannequin->save();

        // Generate the "Updated" message based on the fields that were updated
        if (!empty($updates)) {
            $activity = "Updated " . implode(', ', $updates) . " of $originalItemref";
            $this->logAuditTrail(auth()->user(), $activity);
        }

        if($user->status == 4){
            return redirect()->route('dashboard', $mannequin->id)->with('success_message', 'Product details updated successfully.');
        }
        else{
            return redirect()->route('collection', $mannequin->id)->with('success_message', 'Product details updated successfully.');
        }

    }

    //SHOW TRASHCAN
    public function trashcan()
    {
        $mannequins = Mannequin::where('activeStatus', '<', 1)->get();
        return view('collection-trash')->with(['mannequins' => $mannequins,]);;
    }

    //DELETE(to trashcan make active status = 0)
    public function trash($id)
    {
        $mannequin = Mannequin::find($id);
        if ($mannequin) {
            // Store the original item reference for the audit trail
            $originalItemref = $mannequin->itemref;

            $mannequin->activeStatus = 0;
            $this->setActionBy($mannequin, 'Deleted');
            $mannequin->save();

            // Add audit trail for the "Deleted" action with the original item reference
            $activity = "Trashed $originalItemref";
            $this->logAuditTrail(auth()->user(), $activity);

            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    // DELETE to trashcan multiple collections
    public function trashMultiple(Request $request)
    {
        $ids = $request->input('ids');

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $mannequin = Mannequin::find($id);

                if ($mannequin) {
                    $mannequin->activeStatus = 0;
                    $this->setActionBy($mannequin, 'Deleted');
                    $mannequin->save();
                }
            }
            return redirect()->back()->with('success_message', 'Item deleted permanently.');
        }

        return response()->json(['success' => false]);
    }

    //Delete (PERMANENTLY from database to storage)
    public function destroy($id)
    {
        $mannequin = Mannequin::findOrFail($id);

        // Store the original item reference for the audit trail
        $originalItemref = $mannequin->itemref;

        // Delete associated images
        foreach (explode(',', $mannequin->images) as $imagePath) {
            Storage::delete('public/' . trim($imagePath));
        }

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
        $mannequin = Mannequin::findOrFail($id);
        // Check if the item is actually deleted (activeStatus = 0)
        if ($mannequin->activeStatus == 0) {
            // Store the original item reference for the audit trail
            $originalItemref = $mannequin->itemref;

            $this->setActionBy($mannequin, 'Restored');
            $mannequin->update(['activeStatus' => 1]);

            // Add audit trail for the "Restored" action with the original item reference
            $activity = "Restored $originalItemref";
            $this->logAuditTrail(auth()->user(), $activity);

            return redirect()->route('collection')->with('success_message', 'Item restored successfully.');
        } else {
            return redirect()->route('collection')->with('danger_message', 'Item is not restored.');
        }
        return redirect()->back()->with('error', 'An error occurred while restoring the item.');
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
        // Perform any additional tasks related to deletion, if needed

        // Delete the category
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
        // Perform any additional tasks related to deletion, if needed

        // Delete the type
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
}

