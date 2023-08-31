<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    //add user
    public function store(Request $request)
    {
        // dd($request->all());
        // dd($request->input('selected_company_ids'));
        try {
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

            // Handle different user statuses
            if ($request->status == 1 || $request->status == 4) {
                return redirect()->route('users')->with('success_message', 'User created successfully.');
            } else {
                // Attach selected companies to the user
                $user->companies()->attach($request->input('company_ids'));

                $user->companies()->whereIn('company_id', $selectedCompanyIds)
                ->update(['checkPrice' => 1]);

                // Redirect or do something else after successful registration
                return redirect()->route('users')->with('success_message', 'User created successfully.');
            }
        } catch (\Exception $e) {
            // Handle the error by redirecting back with an error message
            return redirect()->back()->with('danger_message', 'Failed to create user. Please try again.');
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
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('danger_message', 'An error occurred while restoring the item.');
    }

}
