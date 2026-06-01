<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\ResponseHelper;
use App\Traits\Log;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDetail;

class profileController extends Controller
{
    use ResponseHelper, Log;

    public function my_profile(Request $request)
    {
        $user = User::with(['user_detail', 'bank_detail'])->find(Auth::user()->id);

        return $this->successResponse($user, 200, 'Profile retrieved successfully.');
    }

    public function settings(Request $request)
    {
        if (Auth::user()->role_id !== 2) {
            return $this->errorResponse([], 403, 'Only HO accounts can access settings.');
        }

        $detail = UserDetail::where('user_id', Auth::user()->id)->first();

        if (!$detail) {
            return $this->errorResponse([], 404, 'Settings not found.');
        }

        return $this->successResponse([
            'is_bill_enabled'                    => (bool) $detail->is_bill_enabled,
            'is_scan_avaiable'                   => (bool) $detail->is_scan_avaiable,
            'is_size_differentiation_available'  => (bool) $detail->is_size_differentiation_available,
            'is_colour_differentiation_available'=> (bool) $detail->is_colour_differentiation_available,
            'able_to_edit_bill'                  => (bool) $detail->able_to_edit_bill,
            'is_imei_required'                   => (bool) $detail->is_imei_required,
            'is_gst_bill_avaiable'               => (bool) $detail->is_gst_bill_avaiable,
            'bill_type'                          => $detail->bill_type,
            'primary_colour'                     => $detail->primary_colour,
            'secondary_colour'                   => $detail->secondary_colour,
            'plan_start'                         => $detail->plan_start,
            'plan_end'                           => $detail->plan_end,
        ], 200, 'Settings retrieved successfully.');
    }

    public function update_settings(Request $request)
    {
        if (Auth::user()->role_id !== 2) {
            return $this->errorResponse([], 403, 'Only HO accounts can update settings.');
        }

        $validator = Validator::make($request->all(), [
            'is_scan_avaiable'                    => 'required|boolean',
            'is_size_differentiation_available'   => 'required|boolean',
            'is_colour_differentiation_available' => 'required|boolean',
            'able_to_edit_bill'                   => 'required|boolean',
            'is_imei_required'                    => 'required|boolean',
            'is_gst_bill_avaiable'                => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        $detail = UserDetail::where('user_id', Auth::user()->id)->first();

        if (!$detail) {
            return $this->errorResponse([], 404, 'Settings not found.');
        }

        $old = $detail->toArray();

        $detail->update([
            'is_scan_avaiable'                    => $request->is_scan_avaiable,
            'is_size_differentiation_available'   => $request->is_size_differentiation_available,
            'is_colour_differentiation_available' => $request->is_colour_differentiation_available,
            'able_to_edit_bill'                   => $request->able_to_edit_bill,
            'is_imei_required'                    => $request->is_imei_required,
            'is_gst_bill_avaiable'                => $request->is_gst_bill_avaiable,
        ]);

        $this->addToLog($this->unique(), Auth::id(), 'Settings', 'App/Models/UserDetail', 'user_details', $detail->id, 'Update', json_encode($old), $request, 'Success', 'Settings Updated Successfully');

        return $this->successResponse([
            'is_bill_enabled'                    => (bool) $detail->is_bill_enabled,
            'is_scan_avaiable'                   => (bool) $detail->is_scan_avaiable,
            'is_size_differentiation_available'  => (bool) $detail->is_size_differentiation_available,
            'is_colour_differentiation_available'=> (bool) $detail->is_colour_differentiation_available,
            'able_to_edit_bill'                  => (bool) $detail->able_to_edit_bill,
            'is_imei_required'                   => (bool) $detail->is_imei_required,
            'is_gst_bill_avaiable'               => (bool) $detail->is_gst_bill_avaiable,
            'bill_type'                          => $detail->bill_type,
            'primary_colour'                     => $detail->primary_colour,
            'secondary_colour'                   => $detail->secondary_colour,
            'plan_start'                         => $detail->plan_start,
            'plan_end'                           => $detail->plan_end,
        ], 200, 'Settings updated successfully.');
    }

    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password'         => [
                'required',
                'min:6',
                'max:20',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/',
            ],
        ], [
            'password.regex'     => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*#?&).',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min'       => 'Password must be at least 6 characters.',
            'password.max'       => 'Password must not exceed 20 characters.',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        $user = User::find(Auth::user()->id);

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse([], 422, 'Current password is incorrect.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $this->addToLog($this->unique(), Auth::id(), 'Profile', 'App/Models/User', 'users', $user->id, 'Update', null, null, 'Success', 'Password Changed Successfully');

        return $this->successResponse([], 200, 'Password changed successfully.');
    }
}