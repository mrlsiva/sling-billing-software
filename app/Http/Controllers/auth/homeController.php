<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class homeController extends Controller
{
    public function home(Request $request)
    {
        $user = User::where('slug_name',request()->route('company'))->with(['user_detail', 'bank_detail'])->first();

        return view('users.home',compact('user'));
        

    }
}
