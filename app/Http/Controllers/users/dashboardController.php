<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class dashboardController extends Controller
{
    public function index(Request $request)
    {
        $auth = User::where('id', Auth::user()->id)->with(['user_detail', 'bank_detail'])->first();
        $branches = User::where('parent_id',Auth::user()->id)->get();

        return view('users.dashboard',compact('auth','branches'));
    }
}
