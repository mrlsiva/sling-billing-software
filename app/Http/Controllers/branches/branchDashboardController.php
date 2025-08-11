<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class branchDashboardController extends Controller
{
    public function index(Request $request)
    {
        $auth = User::where('id', Auth::user()->id)->with(['user_detail', 'bank_detail'])->first();

        return view('branches.dashboard',compact('auth'));
    }
}
