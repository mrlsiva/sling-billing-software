<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class loginController extends Controller
{
    public function sign_in(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required',
            'password' => 'required'
        ], 
        [
            'email.required' => 'Please enter your email.',
            'password.required' => 'Please enter your password.',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password]))
        {
            //Active/Inactive
            $user = User::where([['id',auth()->user()->id],['is_active',0]])->first();
            if($user)
            {
                return redirect()->back()->with('error_alert', 'Please verify your email.');    
            }

            //Lock/Unlock
            $user = User::where([['id',auth()->user()->id],['is_lock',1]])->first();
            if($user)
            {
                return redirect()->back()->with('error_alert', 'Your account has been locked. Please contact team for future details.');    
            }
            //Delete
            $user = User::where([['id',auth()->user()->id],['is_delete',1]])->first();
            if($user)
            {
                return redirect()->back()->with('error_alert', 'This account has been deleted.');   
            }

            if (auth()->user()->role_id == 1)
            {
                return redirect('admin/dashboard'); 
            }
            else
            {
                return redirect()->route('dashboard');
            }
        }
        else 
        {
            return redirect()->back()->with('error_alert', 'Invalid Credential');
        }
    }
}
