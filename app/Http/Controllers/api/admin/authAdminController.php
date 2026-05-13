<?php

namespace App\Http\Controllers\api\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\User;

class authAdminController extends Controller
{
    use ResponseHelper;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'password'  => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        $user = User::where('user_name', $request->user_name)
            ->where('role_id', 1)
            ->first();

        if (!$user || !\Hash::check($request->password, $user->password)) {
            return $this->errorResponse([], 400, 'Invalid login credentials.');
        }

        if ($user->is_lock == 1) {
            return $this->errorResponse([], 400, 'Your account has been locked.');
        }

        if ($user->is_delete == 1) {
            return $this->errorResponse([], 400, 'This account has been deleted.');
        }

        $token = $user->createToken('adminToken')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user'  => $user,
        ], 200, 'Admin logged in successfully.');
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->successResponse('Success', 200, 'Logged out successfully.');
    }
}