<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class adminController extends Controller
{
    public function dashboard(Request $request)
    {
        $shops = User::where([['role_id',2],['is_active',1]])
            ->with('user_detail')
            ->orderBy('id','desc')
            ->paginate(12)
            ->withQueryString();

        return view('admin.dashboard',compact('shops'));
        
    }
}
