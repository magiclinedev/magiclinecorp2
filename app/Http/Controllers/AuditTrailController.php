<?php

namespace App\Http\Controllers;
use App\Models\AuditTrail;
use App\Models\Users;

use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditTrail::with('user')
        ->orderBy('created_at', 'desc');// Order by created_at in descending order

        if ($request->has('showAll')) {
            $audits = $query->get();
        }
        else {
            $perPage = 30;
            $audits = $query->paginate($perPage);
        }

        return view('audit-trail')->with(['audits' => $audits,]);
    }
}
