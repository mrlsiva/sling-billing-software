<?php

namespace App\Http\Controllers\api\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BankDetail;
use App\Models\UserDetail;
use App\Models\PosSetting;
use App\Models\PrinterType;
use App\Models\User;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class branchApiController extends Controller
{
    use Log, ResponseHelper;

    private function isAdmin(): bool
    {
        return Auth::user()->role_id === 1;
    }

    public function create_data(Request $request, $shop_id)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $shop          = User::with(['user_detail', 'bank_detail'])->find($shop_id);
        $printer_types = PrinterType::where('is_active', 1)->get();

        if (!$shop) return $this->errorResponse([], 404, 'Shop not found.');

        return $this->successResponse(compact('shop', 'printer_types'), 200, 'Branch create data retrieved.');
    }

    public function store(Request $request)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $ownerId = $request->parent_id;
        $parent  = User::find($ownerId);

        if (!$parent) return $this->errorResponse([], 404, 'Parent shop not found.');

        $validator = Validator::make($request->all(), [
            'parent_id'             => 'required|exists:users,id',
            'logo'                  => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'fav_icon'              => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'name'                  => 'required|string|max:50',
            'email'                 => ['nullable', 'email', Rule::unique('users', 'email')],
            'phone'                 => ['required', 'digits:10', 'different:phone1', Rule::unique('users', 'phone')],
            'phone1'                => ['nullable', 'digits:10', 'different:phone', Rule::unique('users', 'alt_phone')],
            'password'              => 'required|min:6|max:20|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/',
            'address'               => 'nullable|string|max:100',
            'slug_name'             => 'required|alpha_dash|unique:users,slug_name|max:50',
            'user_name'             => 'required|alpha_dash|max:20',
            'payment_method'        => 'required',
            'bill_type'             => 'required',
            'account_number'        => 'nullable|numeric|digits_between:9,18|same:confirm_account_number',
            'confirm_account_number'=> 'nullable|same:account_number',
            'ifsc_code'             => 'nullable|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        DB::beginTransaction();

        $user = User::create([
            'role_id'   => 3,
            'parent_id' => $ownerId,
            'name'      => Str::ucfirst($request->name),
            'email'     => $request->email,
            'slug_name' => $request->slug_name,
            'user_name' => $request->user_name,
            'phone'     => $request->phone,
            'alt_phone' => $request->phone1,
            'password'  => \Hash::make($request->password),
            'is_active' => 1,
            'is_lock'   => 0,
            'is_delete' => 0,
            'created_by'=> Auth::user()->id,
            'able_to_login' => $request->has('able_to_login') ? 1 : 0,
        ]);

        // Logo — upload or inherit from parent shop
        if ($request->hasFile('logo')) {
            $file     = $request->file('logo');
            $path     = config('path.root') . '/' . $parent->slug_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.logo');
            $filePath = $file->storeAs($path, time() . '_' . $file->getClientOriginalName(), 'public');
            $user->update(['logo' => $filePath]);
        } else {
            $destPath = config('path.root') . '/' . $parent->slug_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.logo') . '/' . basename($parent->logo);
            Storage::disk('public')->makeDirectory(dirname($destPath));
            if (Storage::disk('public')->exists($parent->logo)) {
                Storage::disk('public')->copy($parent->logo, $destPath);
            }
            $user->update(['logo' => $parent->logo]);
        }

        // Fav icon — upload or inherit
        if ($request->hasFile('fav_icon')) {
            $file     = $request->file('fav_icon');
            $path     = config('path.root') . '/' . $parent->slug_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.fav_icon');
            $filePath = $file->storeAs($path, time() . '_' . $file->getClientOriginalName(), 'public');
            $user->update(['fav_icon' => $filePath]);
        } else {
            $user->update(['fav_icon' => $parent->fav_icon]);
        }

        $role = Role::find(3)->name;
        $user->assignRole($role);

        PosSetting::create([
            'shop_id'    => $ownerId,
            'branch_id'  => $user->id,
            'pagination' => 15,
        ]);

        $plan_end = $this->calcPlanEnd(Carbon::now(), $request->payment_method);

        UserDetail::create([
            'user_id'          => $user->id,
            'address'          => $request->address,
            'gst'              => $request->gst,
            'payment_method'   => $request->payment_method,
            'payment_date'     => Carbon::now(),
            'primary_colour'   => $request->primary_colour,
            'secondary_colour' => $request->secondary_colour,
            'plan_start'       => Carbon::now(),
            'plan_end'         => $plan_end,
            'bill_type'        => $request->bill_type,
            'is_scan_avaiable' => $request->has('is_scan_avaiable') ? 1 : 0,
        ]);

        BankDetail::create([
            'user_id'     => $user->id,
            'name'        => $request->bank,
            'holder_name' => $request->holder_name,
            'branch'      => $request->branch_bank,
            'account_no'  => $request->account_number,
            'ifsc_code'   => $request->ifsc_code,
        ]);

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Branch Create', 'App/Models/User', 'users', $user->id, 'Insert', null, $request, 'Success', 'Branch Created Successfully');

        return $this->successResponse(['branch_id' => $user->id], 200, 'Branch created successfully.');
    }

    public function view(Request $request, $id)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $branch = User::with(['user_detail', 'bank_detail'])->find($id);

        if (!$branch) return $this->errorResponse([], 404, 'Branch not found.');

        return $this->successResponse($branch, 200, 'Branch detail retrieved successfully.');
    }

    public function edit(Request $request, $id)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $branch        = User::with(['user_detail', 'bank_detail'])->find($id);
        $printer_types = PrinterType::where('is_active', 1)->get();

        if (!$branch) return $this->errorResponse([], 404, 'Branch not found.');

        return $this->successResponse(compact('branch', 'printer_types'), 200, 'Branch edit data retrieved.');
    }

    public function update(Request $request)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $ownerId = $request->parent_id;
        $parent  = User::find($ownerId);

        $validator = Validator::make($request->all(), [
            'id'                    => 'required|exists:users,id',
            'parent_id'             => 'required|exists:users,id',
            'logo'                  => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'fav_icon'              => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'name'                  => 'required|string|max:50',
            'email'                 => ['nullable', 'email', Rule::unique('users', 'email')->ignore($request->id)],
            'phone'                 => ['required', 'digits:10', 'different:phone1', Rule::unique('users', 'phone')->ignore($request->id)],
            'phone1'                => ['nullable', 'digits:10', 'different:phone', Rule::unique('users', 'alt_phone')->ignore($request->id)],
            'password'              => 'nullable|min:6|max:20|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/',
            'slug_name'             => ['required', 'alpha_dash', 'max:50', Rule::unique('users', 'slug_name')->ignore($request->id)],
            'user_name'             => ['required', 'alpha_dash', 'max:20', Rule::unique('users', 'user_name')->ignore($request->id)],
            'payment_method'        => 'required',
            'payment_date'          => 'required|date|before_or_equal:today',
            'bill_type'             => 'required',
            'account_number'        => 'nullable|numeric|digits_between:9,18|same:confirm_account_number',
            'confirm_account_number'=> 'nullable|same:account_number',
            'ifsc_code'             => 'nullable|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        $user        = User::find($request->id);
        $user_detail = UserDetail::where('user_id', $request->id)->first();
        $bank_detail = BankDetail::where('user_id', $request->id)->first();

        DB::beginTransaction();

        $user->update([
            'name'      => Str::ucfirst($request->name),
            'email'     => $request->email,
            'slug_name' => $request->slug_name,
            'user_name' => $request->user_name,
            'phone'     => $request->phone,
            'alt_phone' => $request->phone1,
            'able_to_login' => $request->has('able_to_login') ? 1 : 0,
        ]);

        if ($request->hasFile('logo')) {
            $file     = $request->file('logo');
            $path     = config('path.root') . '/' . $parent->slug_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.logo');
            $filePath = $file->storeAs($path, time() . '_' . $file->getClientOriginalName(), 'public');
            $user->update(['logo' => $filePath]);
        }

        if ($request->hasFile('fav_icon')) {
            $file     = $request->file('fav_icon');
            $path     = config('path.root') . '/' . $parent->slug_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.fav_icon');
            $filePath = $file->storeAs($path, time() . '_' . $file->getClientOriginalName(), 'public');
            $user->update(['fav_icon' => $filePath]);
        }

        if ($request->password) {
            $user->update(['password' => \Hash::make($request->password)]);
        }

        $paymentDate = Carbon::parse($request->payment_date);
        $plan_end    = $this->calcPlanEnd($paymentDate, $request->payment_method);

        $user_detail->update([
            'address'          => $request->address,
            'gst'              => $request->gst,
            'payment_method'   => $request->payment_method,
            'payment_date'     => $request->payment_date,
            'primary_colour'   => $request->primary_colour,
            'secondary_colour' => $request->secondary_colour,
            'plan_start'       => $request->payment_date,
            'plan_end'         => $plan_end,
            'bill_type'        => $request->bill_type,
            'is_scan_avaiable' => $request->has('is_scan_avaiable') ? 1 : 0,
        ]);

        $bank_detail->update([
            'name'        => $request->bank,
            'holder_name' => $request->holder_name,
            'branch'      => $request->branch_bank,
            'account_no'  => $request->account_number,
            'ifsc_code'   => $request->ifsc_code,
        ]);

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Branch Update', 'App/Models/User', 'users', $user->id, 'Update', null, $request, 'Success', 'Branch Updated Successfully');

        return $this->successResponse(['branch_id' => $user->id], 200, 'Branch updated successfully.');
    }

    public function lock(Request $request, $id)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $user = User::find($id);

        if (!$user) return $this->errorResponse([], 404, 'Branch not found.');

        $user->is_lock = $user->is_lock == 1 ? 0 : 1;
        $user->save();

        $statusText = $user->is_lock == 1 ? 'Branch locked successfully.' : 'Branch unlocked successfully.';

        $this->addToLog($this->unique(), Auth::user()->id, 'Branch Lock', 'App/Models/User', 'users', $id, 'Update', null, null, 'Success', $statusText);

        return $this->successResponse(['is_lock' => $user->is_lock], 200, $statusText);
    }

    public function delete(Request $request, $id)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $user = User::find($id);

        if (!$user) return $this->errorResponse([], 404, 'Branch not found.');

        $user->update(['is_delete' => 1]);

        $this->addToLog($this->unique(), Auth::user()->id, 'Branch Delete', 'App/Models/User', 'users', $id, 'Delete', null, null, 'Success', 'Branch Deleted Successfully');

        return $this->successResponse(null, 200, 'Branch deleted successfully.');
    }

    private function calcPlanEnd(Carbon $from, $method): ?Carbon
    {
        return match ((int) $method) {
            1 => $from->copy()->addMonth(),
            2 => $from->copy()->addMonths(3),
            3 => $from->copy()->addMonths(6),
            4 => $from->copy()->addYear(),
            default => null,
        };
    }
}