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
        $user = Auth::user();
        $addedByInfo = $this->getAddedByInfo('Added', $user);

        // dd($request);
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'activeStatus' => "1",
            'addedBy' => $addedByInfo,
        ]);
        // $this->setActionBy($user, 'Added');

        if($request->status == 1 || $request->status == 4)
        {
            // Redirect or do something else after successful registration
            return redirect()->route('users')->with('success_message', 'User created successfully.');

        }
        else
        {
            // Attach selected companies to the user
            $user->companies()->attach($request->input('company_ids'));

            // Update checkPrice value for each selected company
            foreach ($request->input('selected_company_ids') as $companyId) {
                $user->companies()->updateExistingPivot($companyId, ['checkPrice' => 1]);
            }
            // Redirect or do something else after successful registration
            return redirect()->route('users')->with('success_message', 'User created successfully.');
        }
    }

    //delete user
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
            return response()->json(['success' => true, 'message' => 'User is Deleted']); // Change this line
        } else {
            // Change the active activeStatus to 0 for users with activeStatus other than 0
            $user->activeStatus = 0;
            $user->addedBy = $addedByInfo;
            $user->save();
            return response()->json(['success' => true, 'message' => 'User is Inactive']);
        }
    }

    // Restore user(to viewer only)
    public function restore($id)
    {
        $user = Auth::user();
        $addedByInfo = $this->getAddedByInfo('Restored', $user);

        $user = User::findOrFail($id);
        // Check if the item is actually deleted (activeStatus = 0)
        if ($user->activeStatus == 0) {
            $user->update(['activeStatus' => 1, 'addedBy' => $addedByInfo]);
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('danger_message', 'An error occurred while restoring the item.');
    }

}
