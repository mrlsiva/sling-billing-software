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
use App\Models\ShopPayment;
use App\Models\BankDetail;
use App\Models\UserDetail;
use App\Models\PrinterType;
use App\Models\Payment;
use App\Models\User;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class shopApiController extends Controller
{
    use Log, ResponseHelper;

    private function isAdmin(): bool
    {
        return Auth::user()->role_id === 1;
    }

    public function index(Request $request)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $shops = User::with(['user_detail', 'bank_detail'])
            ->where('role_id', 2)
            ->when($request->shop, function ($q) use ($request) {
                $search = $request->shop;
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('user_name', 'like', "%{$search}%")
                       ->orWhere('slug_name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return $this->successResponse($shops, 200, 'Shops retrieved successfully.');
    }

    public function create_data(Request $request)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $printer_types = PrinterType::where('is_active', 1)->get();
        $payments      = Payment::where('is_active', 1)->get();

        return $this->successResponse(compact('printer_types', 'payments'), 200, 'Create data retrieved successfully.');
    }

    public function store(Request $request)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $validator = Validator::make($request->all(), [
            'logo'                  => 'required|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'fav_icon'              => 'required|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'name'                  => 'required|string|max:50',
            'email'                 => 'nullable|email|unique:users',
            'phone'                 => 'required|digits:10|different:phone1|unique:users',
            'phone1'                => 'nullable|digits:10|different:phone|unique:users,alt_phone',
            'password'              => 'required|min:6|max:20|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/',
            'address'               => 'nullable|string|max:100',
            'slug_name'             => 'required|alpha_dash|unique:users,slug_name|max:50',
            'user_name'             => 'required|alpha_dash|max:20',
            'gst'                   => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i|unique:user_details,gst',
            'account_number'        => 'nullable|numeric|digits_between:9,18|same:confirm_account_number',
            'confirm_account_number'=> 'nullable|same:account_number',
            'ifsc_code'             => 'nullable|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i',
            'bill_type'             => 'required',
            'payment_method'        => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        DB::beginTransaction();

        $user = User::create([
            'role_id'   => 2,
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
            'able_to_login' => $request->has('able_to_login') ? 1 : 0,
        ]);

        $user->update(['owner_id' => $user->id, 'created_by' => Auth::user()->id]);

        if ($request->hasFile('logo')) {
            $file     = $request->file('logo');
            $path     = config('path.root') . '/' . $request->slug_name . '/' . config('path.logo');
            $filePath = $file->storeAs($path, time() . '_' . $file->getClientOriginalName(), 'public');
            $user->update(['logo' => $filePath]);
        }

        if ($request->hasFile('fav_icon')) {
            $file     = $request->file('fav_icon');
            $path     = config('path.root') . '/' . $request->slug_name . '/' . config('path.fav_icon');
            $filePath = $file->storeAs($path, time() . '_' . $file->getClientOriginalName(), 'public');
            $user->update(['fav_icon' => $filePath]);
        }

        $role = Role::find(2)->name;
        $user->assignRole($role);

        $plan_end = $this->calcPlanEnd(Carbon::now(), $request->payment_method);

        UserDetail::create([
            'user_id'                             => $user->id,
            'address'                             => $request->address,
            'gst'                                 => $request->gst,
            'primary_colour'                      => $request->primary_colour,
            'secondary_colour'                    => $request->secondary_colour,
            'payment_method'                      => $request->payment_method,
            'payment_date'                        => Carbon::now(),
            'plan_start'                          => Carbon::now(),
            'plan_end'                            => $plan_end,
            'bill_type'                           => $request->bill_type,
            'is_scan_avaiable'                    => $request->has('is_scan_avaiable') ? 1 : 0,
            'is_bill_enabled'                     => $request->has('is_bill_enabled') ? 1 : 0,
            'is_size_differentiation_available'   => $request->has('is_size_differentiation_available') ? 1 : 0,
            'is_colour_differentiation_available' => $request->has('is_colour_differentiation_available') ? 1 : 0,
            'able_to_edit_bill'                   => $request->has('able_to_edit_bill') ? 1 : 0,
            'is_imei_required'                    => $request->has('is_imei_required') ? 1 : 0,
        ]);

        BankDetail::create([
            'user_id'     => $user->id,
            'name'        => $request->bank,
            'holder_name' => $request->holder_name,
            'branch'      => $request->branch_bank,
            'account_no'  => $request->account_number,
            'ifsc_code'   => $request->ifsc_code,
        ]);

        $payments = Payment::where('is_active', 1)->get();
        foreach ($payments as $payment) {
            ShopPayment::create(['shop_id' => $user->id, 'payment_id' => $payment->id]);
        }

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Shop Create', 'App/Models/User', 'users', $user->id, 'Insert', null, $request, 'Success', 'Shop Created Successfully');

        return $this->successResponse(['shop_id' => $user->id], 200, 'Shop created successfully.');
    }

    public function view(Request $request, $id)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $shop = User::with(['user_detail', 'bank_detail'])->find($id);

        if (!$shop) return $this->errorResponse([], 404, 'Shop not found.');

        $branches = User::with(['user_detail', 'bank_detail'])
            ->where('parent_id', $id)
            ->when($request->branch, function ($q) use ($request) {
                $search = $request->branch;
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('user_name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(5);

        return $this->successResponse(compact('shop', 'branches'), 200, 'Shop detail retrieved successfully.');
    }

    public function edit(Request $request, $id)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $shop          = User::with(['user_detail', 'bank_detail'])->find($id);
        $printer_types = PrinterType::where('is_active', 1)->get();

        if (!$shop) return $this->errorResponse([], 404, 'Shop not found.');

        return $this->successResponse(compact('shop', 'printer_types'), 200, 'Shop edit data retrieved successfully.');
    }

    public function update(Request $request)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $ownerId = $request->id;

        $validator = Validator::make($request->all(), [
            'id'                    => 'required|exists:users,id',
            'logo'                  => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'fav_icon'              => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'name'                  => 'required|string|max:50',
            'email'                 => ['nullable', 'email', Rule::unique('users', 'email')->ignore($ownerId)],
            'phone'                 => ['required', 'digits:10', 'different:phone1', Rule::unique('users', 'phone')->ignore($ownerId)],
            'phone1'                => ['nullable', 'digits:10', 'different:phone', Rule::unique('users', 'alt_phone')->ignore($ownerId)],
            'password'              => 'nullable|min:6|max:20|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/',
            'slug_name'             => ['required', 'alpha_dash', 'max:50', Rule::unique('users', 'slug_name')->ignore($ownerId)],
            'user_name'             => ['required', 'alpha_dash', 'max:20', Rule::unique('users', 'user_name')->ignore($ownerId)],
            'account_number'        => 'nullable|numeric|digits_between:9,18|same:confirm_account_number',
            'confirm_account_number'=> 'nullable|same:account_number',
            'ifsc_code'             => 'nullable|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i',
            'bill_type'             => 'required',
            'payment_method'        => 'required',
            'payment_date'          => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        $user        = User::find($ownerId);
        $user_detail = UserDetail::where('user_id', $ownerId)->first();
        $bank_detail = BankDetail::where('user_id', $ownerId)->first();

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
            $path     = config('path.root') . '/' . $request->slug_name . '/' . config('path.logo');
            $filePath = $file->storeAs($path, time() . '_' . $file->getClientOriginalName(), 'public');
            $user->update(['logo' => $filePath]);
        }

        if ($request->hasFile('fav_icon')) {
            $file     = $request->file('fav_icon');
            $path     = config('path.root') . '/' . $request->slug_name . '/' . config('path.fav_icon');
            $filePath = $file->storeAs($path, time() . '_' . $file->getClientOriginalName(), 'public');
            $user->update(['fav_icon' => $filePath]);
        }

        if ($request->password) {
            $user->update(['password' => \Hash::make($request->password)]);
        }

        $paymentDate = Carbon::parse($request->payment_date);
        $plan_end    = $this->calcPlanEnd($paymentDate, $request->payment_method);

        $user_detail->update([
            'address'                             => $request->address,
            'gst'                                 => $request->gst,
            'primary_colour'                      => $request->primary_colour,
            'secondary_colour'                    => $request->secondary_colour,
            'payment_method'                      => $request->payment_method,
            'payment_date'                        => $request->payment_date,
            'plan_start'                          => $request->payment_date,
            'plan_end'                            => $plan_end,
            'bill_type'                           => $request->bill_type,
            'is_scan_avaiable'                    => $request->has('is_scan_avaiable') ? 1 : 0,
            'is_bill_enabled'                     => $request->has('is_bill_enabled') ? 1 : 0,
            'is_size_differentiation_available'   => $request->has('is_size_differentiation_available') ? 1 : 0,
            'is_colour_differentiation_available' => $request->has('is_colour_differentiation_available') ? 1 : 0,
            'able_to_edit_bill'                   => $request->has('able_to_edit_bill') ? 1 : 0,
            'is_imei_required'                    => $request->has('is_imei_required') ? 1 : 0,
        ]);

        $bank_detail->update([
            'name'        => $request->bank,
            'holder_name' => $request->holder_name,
            'branch'      => $request->branch_bank,
            'account_no'  => $request->account_number,
            'ifsc_code'   => $request->ifsc_code,
        ]);

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Shop Update', 'App/Models/User', 'users', $user->id, 'Update', null, $request, 'Success', 'Shop Updated Successfully');

        return $this->successResponse(['shop_id' => $user->id], 200, 'Shop updated successfully.');
    }

    public function lock(Request $request, $id)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $user = User::find($id);

        if (!$user) return $this->errorResponse([], 404, 'Shop not found.');

        $user->is_lock = $user->is_lock == 1 ? 0 : 1;
        $user->save();

        $statusText = $user->is_lock == 1 ? 'Shop locked successfully.' : 'Shop unlocked successfully.';

        $this->addToLog($this->unique(), Auth::user()->id, 'Shop Lock', 'App/Models/User', 'users', $id, 'Update', null, null, 'Success', $statusText);

        return $this->successResponse(['is_lock' => $user->is_lock], 200, $statusText);
    }

    public function delete(Request $request, $id)
    {
        if (!$this->isAdmin()) return $this->errorResponse([], 403, 'Unauthorized.');

        $user = User::find($id);

        if (!$user) return $this->errorResponse([], 404, 'Shop not found.');

        $user->update(['is_delete' => 1]);

        $this->addToLog($this->unique(), Auth::user()->id, 'Shop Delete', 'App/Models/User', 'users', $id, 'Delete', null, null, 'Success', 'Shop Deleted Successfully');

        return $this->successResponse(null, 200, 'Shop deleted successfully.');
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