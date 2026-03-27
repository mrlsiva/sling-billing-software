<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class salesReportController extends Controller
{
    public function sales(Request $request, $company, $branch)
    {
        $branches = User::where([
            ['parent_id', Auth::user()->owner_id],
            ['is_active', 1],
            ['is_lock', 0],
            ['is_delete', 0]
        ])->get();

        return view('users.reports.sales',compact('branches'));
    }
}
