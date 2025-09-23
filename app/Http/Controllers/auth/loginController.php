<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\Log;
use Session;

class loginController extends Controller
{
    use Log;

    public function sign_in(Request $request)
    {
        $validatedData = $request->validate([
            'user_name' => 'required',
            'password' => 'required'
        ], 
        [
            'user_name.required' => 'Please enter your username.',
            'password.required' => 'Please enter your password.',
        ]);

        if (Auth::attempt(['user_name' => $request->user_name, 'password' => $request->password, 'slug_name' => $request->slug_name]))
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
                //Log
                $this->addToLog($this->unique(),auth()->user()->id,'Login','App/Models/User','users',auth()->user()->id,'Login',null,null,'Success','Login Successfully');

                return redirect('admin/dashboard'); 
            }
            else if (auth()->user()->role_id == 2)
            {
                //Log
                $this->addToLog($this->unique(),auth()->user()->id,'Login','App/Models/User','users',auth()->user()->id,'Login',null,null,'Success','Login Successfully');

                $company = request()->route('company');

                return redirect()->route('dashboard', ['company' => $company]);
            }
            else if (auth()->user()->role_id == 3)
            {
                //Log
                $this->addToLog($this->unique(),auth()->user()->id,'Login','App/Models/User','users',auth()->user()->id,'Login',null,null,'Success','Login Successfully');

                $company = request()->route('company');

                return redirect()->route('branch.billing.pos', ['company' => $company]);
            }
        }
        else 
        {
            return redirect()->back()->with('error_alert', 'Invalid Credential');
        }
    }

    public function my_profile(Request $request)
    {
        if(auth()->user()->role_id == 1)
        {
            $user = User::where('id',Auth::user()->id)->first();

            return view('auth.admin_profile',compact('user'));
        }
        else
        {
            $user = User::where('id',Auth::user()->id)->with(['user_detail', 'bank_detail'])->first();

            return view('auth.my_profile',compact('user'));
        }
        
    }

    public function logout(Request $request)
    {
        
        if (Auth::check()) {

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Logout','App/Models/User','users',Auth::user()->id,'Logout',null,null,'Success','Logout Successfully');
        
            Auth::logout(); // Log the user out
            $request->session()->invalidate(); 
            $request->session()->regenerateToken();

            if (request()->segment(1) === 'admin') 
            {
                return redirect('/admin');
            }
            else
            {
                $company = $request->route('company') ?? $request->segment(1);
                return redirect()->route('login', ['company' => $company]);
            }
        }

    }
}
