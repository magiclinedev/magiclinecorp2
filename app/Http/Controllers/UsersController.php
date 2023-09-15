<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\AuditTrail;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Validation\Rule;

use Carbon\Carbon;

class UsersController extends Controller
{
    //User View
    public function index()
    {
        $users = User::with('companies')->get();
        $companies = Company::all();

        return view('user')->with(['users' => $users, 'companies' => $companies]);
    }

    //addedBy
    private function getAddedByInfo($action, $user = null)
    {
        $time = Carbon::now()->format('m/d/y - g:i A');

        if ($user) {
            return "$action by {$user->name} at $time";
        } else {
            return "$action at $time";
        }
    }

    // Add user
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:5',
            'status' => 'required|in:1,2,3,4',
            'company_ids' => [
                'array', // Must be an array
                // Required if 'status' is not equal to 1 or 4
                Rule::requiredIf(function () use ($request) {
                    return !in_array($request->input('status'), [1, 4]);
                }),
            ],
        ], [
            'company_ids.required' => 'Admin 2 or Viewer is required to have at least 1 company access.',
            'password.min' => 'Password is too short. It must be at least 5 characters long.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return redirect('/users')
                ->with('validation_errors', $errors)
                ->withInput()
                ->with('danger_message', 'Input Incorrect: Please check the form fields and try again.');
        }

        $user = Auth::user();
        $addedByInfo = $this->getAddedByInfo('Added', $user);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'activeStatus' => "1",
            'addedBy' => $addedByInfo,
        ]);

        // Check user creation and handle accordingly
        if (!$user) {
            throw new \Exception('Failed to create user');
        }


        // Add audit trail for the "Added" action with the item reference
        $activity = "Added User " . $request->name;
        $this->logAuditTrail(auth()->user(), $activity);

        // Handle different user statuses
        if ($request->status == 1 || $request->status == 4)
        {
            // User status is admin or owner, so redirect with success message
            return redirect()->route('users')->with('success_message', 'User created successfully.');
        }
        else
        {
            // Attach selected companies to the user
            $attachedCompanies = $user->companies()->attach($request->input('company_ids'));

            // Get selected company IDs for price access
            $selected_company_ids = $request->input('selected_company_ids');

            // Update checkPrice for selected companies if $selected_company_ids is not null
            if ($selected_company_ids !== null) {
                $companies = Company::whereIn('id', $selected_company_ids)->get();

                foreach ($companies as $company) {
                    // Update checkPrice for the current company
                    $user->companies()->updateExistingPivot($company, ['checkPrice' => 1]);
                }
            }

            // Redirect with success message
            return redirect()->route('users')->with('success_message', 'User created successfully.');
        }
    }

    //delete user(Deactivate/Delete permanently)
    public function trash($id)
    {
        $user = Auth::user();
        $addedByInfo = $this->getAddedByInfo('Deactivated', $user);

        $user = User::findOrFail($id);

        if ($user->activeStatus == 0) {
            // Delete related entries from the company_user pivot table
            DB::table('company_user')->where('user_id', $user->id)->delete();

            // Permanently delete user with activeStatus 0
            $user->delete();
            return response()->json(['success' => true, 'message' => 'User is Deleted']);
        } else {
            // Make active users inactive user with activeStatus 1 to 0
            $user->activeStatus = 0;
            $user->addedBy = $addedByInfo;
            $user->save();

            // Log the audit trail entry for user deactivation
            $activity = "Deactivated User {$user->name}";
            $user = Auth::user();
            $this->logAuditTrail($user, $activity);

            return response()->json(['success' => true, 'message' => 'User is Inactive']);
        }
    }

    // Restore user
    public function restore($id)
    {
        $user = Auth::user();
        $addedByInfo = $this->getAddedByInfo('Restored', $user);

        $user = User::findOrFail($id);
        // Check if the item is actually deleted (activeStatus = 0)
        if ($user->activeStatus == 0) {
            //activeStatus -> 1 and addedBy to Retored by user
            $user->update(['activeStatus' => 1, 'addedBy' => $addedByInfo]);

            // Log the audit trail entry for user restoration
            $activity = "Restored User {$user->name}";
            $user = Auth::user();
            $this->logAuditTrail($user, $activity);

            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('danger_message', 'An error occurred while restoring the item.');
    }

    // View User being edited
    public function edit($encryptedId)
    {
        try {
            // Decrypt the encrypted ID to get the original ID
            $id = Crypt::decrypt($encryptedId);
        } catch (DecryptException $e) {
            // Redirect with an error message if decryption fails
            return redirect()->back()->with('danger_message', 'Invalid URL.');
        }

        // Get the authenticated user
        $user = Auth::user();

        // Fetch the selected mannequin
        $user = User::find($id);
        $companies = Company::all();

        // Return the view with the required data
        return view('user-edit')->with([
            'user' => $user,
            'companies' => $companies,
        ]);
    }

    //EDIT User
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $addedByInfo = $this->getAddedByInfo('Updated', $user);

        // Keep the original itemref for audit trail
        $originalName = $user->name;

        // Create an array to store updates
        $updates = [];

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'status' => 'required|in:1,2,3,4',
            'company_ids' => $request->status == 1 || $request->status == 4 ? 'nullable|array' : 'required|array',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return redirect('/users')
                ->with('validation_errors', $errors)
                ->withInput()
                ->with('danger_message', 'Input Incorrect: Please check the form fields and try again.');
        }

        // Find the user by ID
        $user = User::findOrFail($id);

        // Update user information
        $user->update([
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'status' => $request->input('status'),
            'addedBy' => $addedByInfo,
        ]);

        if ($request->status == 1 || $request->status == 4)
        {
            // User status is admin or owner, so remove all company relationships
            $user->companies()->detach();

            // User status is admin or owner, so redirect with success message
            return redirect()->route('users')->with('success_message', 'User Updated successfully.');
        }
        else
        {
            // Attach selected companies to the user
            $user->companies()->sync($request['company_ids']);

            // Handle price access (checkPrice) for selected companies
            if (isset($request['company_ids'])) {
                foreach ($request['company_ids'] as $companyId) {
                    // Check if the company_id should have checkPrice
                    $checkPrice = in_array($companyId, $request->input('selected_company_ids', [])) ? 1 : NULL;

                    $user->companies()->updateExistingPivot($companyId, [
                        'checkPrice' => $checkPrice,
                    ]);
                }
            }

            // Redirect to a success page or return a response as needed
            return redirect()->route('users')->with('success_message', 'User updated successfully');
        }
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
