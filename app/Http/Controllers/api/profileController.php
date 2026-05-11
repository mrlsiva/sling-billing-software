<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\User;

class profileController extends Controller
{
    use ResponseHelper;

    public function my_profile(Request $request)
    {
        $user = User::with(['user_detail', 'bank_detail'])->find(Auth::user()->id);

        return $this->successResponse($user, 200, 'Profile retrieved successfully.');
    }
}