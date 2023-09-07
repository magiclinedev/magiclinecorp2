<?php

namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\AuditTrail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function index()
    {
        $company = Company::all();
        return view('company')->with(['company' => $company]);
    }

    // ADD COMPANY
    public function company(Request $request)
    {
        // Validate the incoming data
        $validator = \Validator::make($request->all(), [
            'company' => 'required|unique:companies,name',
        ]);

        // If validation fails, redirect back with errors and input
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('danger_message', 'Invalid Input');
        }

        // Initialize a variable to store the photo path
        $photoPath = null;

        // Check if an image is uploaded
        if ($request->hasFile('images')) {
            $photo = $request->file('images');
            $photoName = time() . '.' . $photo->getClientOriginalExtension();
            $photo->storeAs('public/images', $photoName);

            // Update the $photoPath with the path to the uploaded photo
            $photoPath = 'images/' . $photoName;
        } else {
            return redirect()->back()->withInput()->with('danger_message', 'Please upload an image.');
        }

        // Create a new Company instance
        $company = new Company();
        $company->name = strtoupper($request->company);

        // Set the 'images' field to the uploaded photo path
        $company->images = $photoPath;

        // addedBy -> user
        if (Auth::check()) {
            $user = Auth::user()->name;
            $time = Carbon::now()->format('m/d/y - g:i A');
            $company->addedBy = "$user at $time";
        }

        $company->save();

        return redirect()->route('company')->with('success_message', 'Company added successfully!');
    }

    //Delete company(PERMANENTLY)
    public function trash($id)
    {
        $company = Company::findOrFail($id);
        // Perform any additional tasks related to deletion, if needed

        // Delete associated images
        foreach (explode(',', $company->images) as $imagePath) {
            Storage::delete('public/' . trim($imagePath));
        }

        // Delete the company
        $company->delete();

        return response()->json(['success' => true]);
    }


}
