<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Traits\common;
use App\Models\User;
use DB;

class authController extends Controller
{
    use ResponseHelper,common;

    public function register(Request $request, $company)
    {
        $shop = User::where('slug_name',$company)->first();

        $rules = [
            'name' => 'required|string|max:50',
            'email' => 'nullable|email',

            'phone' => 'required','digits:10','different:alt_phone',

            'alt_phone' => 'nullable|digits:10|different:phone',
            'address' => 'required|string|max:200',
            'pincode' => 'nullable|digits:6|regex:/^[1-9][0-9]{5}$/',
            'gst' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
            'password' => 'required|min:6|max:20|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/',
        ];
        
        $validator=Validator::make($request->all(),$rules);


        if ($validator->fails()) {
            return $this->validationFailed($validator->errors());
        }

        $customer = Customer::where([['user_id',$shop->owner_id],['phone',$request->phone]])->first();
        if($customer)
        {
            $user = User::where('customer_id',$customer->id)->first();
        }
        else
        {
            $user = null;
        }

        if($user && $customer)
        {
            return $this->errorResponse("User already exit.",400,"Failed to register");
        }

        DB::beginTransaction();

        if(!$customer)
        {
            $customer = Customer::create([ 
                'user_id' => $shop->owner_id,
                'name' => Str::ucfirst($request->name),
                'phone' => $request->phone,
                'alt_phone' => $request->alt_phone,
                'address' => $request->address,
                'pincode' => $request->pincode,
                'gender_id' => $request->gender,
                'dob' => $request->dob,
                'gst' => $request->gst,
            ]);
        }

        
        if(!$user)
        {
            $user = User::create([ 
                'role_id' => 4,
                'owner_id' => $shop->owner_id,
                'unique_id' => $this->userUnique(),
                'customer_id' => $customer->id,
                'name' => Str::ucfirst($request->name),
                'email' => $request->email,
                'user_name' => $request->name,
                'phone' => $request->phone,
                'alt_phone' => $request->alt_phone,
                'password' => \Hash::make($request->password),
                'is_active' => 1,
                'is_lock' => 0,
                'is_delete' => 0,
                'able_to_login' => 1,
            ]);
        }

        DB::commit();

        return $this->successResponse('Success', 200, 'Successfully registered');

    }

    public function login(Request $request, $company)
    {
        $rules=array(
            'phone' => 'required',
            'password' => 'required',
        );
        
        $validator=Validator::make($request->all(),$rules);


        if ($validator->fails()) {
            return $this->validationFailed($validator->errors());
        }
        
        $shop = User::where('slug_name',$company)->first();
        $customer = Customer::where([['user_id',$shop->id],['phone',$request->phone]])->first();
        
        $user = User::with('customer')->where('customer_id', $customer->id)->first();

        if ($user && \Hash::check($request->password, $user->password)) 
        {
            if($user->role_id == 4)
            {
                
                if($user->is_active == 0)
                {
                    return $this->errorResponse("Please verify your email.",400,"Failed to Login");
                }
                elseif($user->is_lock == 1)
                {
                    return $this->errorResponse("Your account has been locked. Please contact team for future details.",400,"Failed to Login");
                }
                elseif($user->is_delete == 1)
                {
                    return $this->errorResponse("This account has been deleted.",400,"Failed to Login");
                }

                $user->auth_token = $user->createToken('authToken')->plainTextToken;
                return $this->successResponse($user, 200, 'Successfully Logged in');
            }
            else
            {
                return $this->errorResponse("Invalid login role.",400,"Failed to Login");
                
            }
        }
        else
        {
            return $this->errorResponse("Invalid credential.",400,"Failed to Login");
                
        }
    }
}
