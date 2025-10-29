<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\User;
use DB;

class authController extends Controller
{
    use ResponseHelper;

    public function login(Request $request)
    {
        $rules=array(
            'slug_name' => 'required',
            'password' => 'required',
        );
        
        $validator=Validator::make($request->all(),$rules);


        if ($validator->fails()) {
            return $this->validationFailed($validator->errors());
        }
        
       
        $user = User::with(['user_detail', 'bank_detail'])->where('slug_name', $request->slug_name)->get()->first();

        if ($user && \Hash::check($request->password, $user->password)) 
        {
            if($user->role_id == 1)
            {
                return $this->errorResponse("Admin cant Login",400,"Failed to Login");
            }
            elseif($user->is_active == 0)
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
            else
            {
                $user->auth_token = $user->createToken('authToken')->plainTextToken;

                return $this->successResponse($user, 200, 'Successfully Logged in');
            }

        }
        else
        {

            return $this->errorResponse("Invalid Login Credentials",400,"Failed to Login");
        }
            
    }

    public function logout(Request $request)
    {
        // Delete all tokens of the user
        $request->user()->tokens()->delete();

        return $this->successResponse('Success', 200, 'Logged out from all devices successfully');
    }
}
