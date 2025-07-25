<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankDetail;
use App\Models\UserDetail;
use App\Models\User;
use App\Traits\Log;

class shopController extends Controller
{
    use Log;

    public function index(Request $request)
    {
        return view('admin.shops.index');
    }

    public function create(Request $request)
    {
        return view('admin.shops.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'logo' => 'required|mimes:jpg,jpeg,png,gif|max:2048', // Allow jpg, jpeg, png up to 2MB
            'name' => 'required|string|max:50',
            'phone' => 'required|digits:10',
            'phone1' => 'nullable|digits:10',
            'password' => 'required|min:6|confirmed', // optional confirmation
            'address' => 'required|string|max:255',
            'slug_name' => 'required|alpha_dash|unique:users,slug_name',
            'gst' => 'required|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',

            'bank' => 'nullable|string|max:50',
            'account_number' => 'nullable|digits:16',
            'confirm_account_number' => 'nullable|same:account_number',
            'branch' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i',
        ], 
        [
            'logo.required' => 'Logo is required.',
            'logo.mimes' => 'Logo must be a JPG, JPEG or PNG file.',
            'logo.max' => 'Logo size must not exceed 2MB.',
            
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone number is required.',
            'phone.digits' => 'Phone number must be exactly 10 digits.',
            'phone1.digits' => 'Alternate phone number must be exactly 10 digits.',
            
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            
            'address.required' => 'Address is required.',
            'slug_name.required' => 'Slug name is required.',
            'slug_name.alpha_dash' => 'Slug name may only contain letters, numbers, dashes and underscores.',
            'slug_name.unique' => 'This slug name is already taken.',
            
            'gst.required' => 'GST number is required.',
            'gst.regex' => 'GST number format is invalid.',

            'account_number.digits_between' => 'Account number must be exactly 16 digits.',
            'confirm_account_number.same' => 'Confirm account number must match the account number.',
            'ifsc_code.regex' => 'Invalid IFSC code format.',
        ]);

        DB::beginTransaction();

        $user = User::create([
            'role_id' => 2,
            'name' => Str::ucfirst($request->name),
            'email' => $request->email,
            'username' => $request->slug_name,
            'phone' => $request->phone,
            'alt_phone' => $request->phone1,
            'address' => $request->address,
            'gst' => $request->gst,
            'password' => \Hash::make($request->password),
            'is_active' => 1,
            'is_lock' => 0,
            'is_delete' => 0,
        ]);

        $user->update([
            'created_by' => $user->id
        ]);

        $role = Role::where('id',2)->first()->name;
        $user->assignRole($role);

        $user_detail = UserDetail::create([
            'user_id' => $user->id,
            'primary_colour' => $request->primary_colour,
            'secondary_colour' => $request->secondary_colour,
        ]);

        $bank_detail = BankDetail::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'branch' => $request->branch,
            'account_no' => $request->account_no,
            'ifsc_code' => $request->ifsc_code,
        ]);

        DB::commit();


    }

    public function view(Request $request)
    {
        return view('admin.shops.view');
    }

    public function edit(Request $request)
    {
        return view('admin.shops.edit');
    }

    public function update(Request $request)
    {

    }

}
