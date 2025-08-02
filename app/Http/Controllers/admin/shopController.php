<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BankDetail;
use App\Models\UserDetail;
use App\Traits\common;
use App\Models\User;
use App\Traits\Log;
use DB;

class shopController extends Controller
{
    use Log, common;

    public function index(Request $request)
    {
        $shops = User::with(['user_detail', 'bank_detail'])->where('role_id',2)->orderBy('id','desc')->paginate(30);
        return view('admin.shops.index',compact('shops'));
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
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|digits:10|unique:users,phone',
            'phone1' => 'nullable|digits:10',
            'password' => 'required|min:6|confirmed', // optional confirmation
            'address' => 'nullable|string|max:255',
            'slug_name' => 'required|alpha_dash|unique:users,user_name',
            'gst' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
            'payment_method' => 'nullable|string|max:255',

            'bank' => 'nullable|string|max:50',
            'name' => 'nullable|string|max:50',
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
            'unique_id' => $this->userUnique(),
            'name' => Str::ucfirst($request->name),
            'email' => $request->email,
            'user_name' => $request->slug_name,
            'phone' => $request->phone,
            'alt_phone' => $request->phone1,
            'password' => \Hash::make($request->password),
            'is_active' => 1,
            'is_lock' => 0,
            'is_delete' => 0,
        ]);

        $user->update([
            'created_by' => $user->id
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = config('path.root') . '/' . config('path.HO.head_office') . '/' . request()->route('company') . '/' . config('path.HO.logo');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            // Save to user
            $user->logo = $filePath; // This is relative to storage/app/public
            $user->save();
        }


        $role = Role::where('id',2)->first()->name;
        $user->assignRole($role);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Shop Create','App/Models/User','users',$user->id,'Insert',null,$request,'Success','Shop Created Successfully');

        $user_detail = UserDetail::create([
            'user_id' => $user->id,
            'address' => $request->address,
            'gst' => $request->gst,
            'payment_method' => $request->payment_method,
            'primary_colour' => $request->primary_colour,
            'secondary_colour' => $request->secondary_colour,
        ]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Shop Create','App/Models/UserDetail','user_details',$user_detail->id,'Insert',null,$request,'Success','Shop Created Successfully');

        $bank_detail = BankDetail::create([
            'user_id' => $user->id,
            'name' => $request->bank,
            'holder_name' => $request->name,
            'branch' => $request->branch,
            'account_no' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
        ]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Shop Create','App/Models/BankDetail','bank_details',$bank_detail->id,'Insert',null,$request,'Success','Shop Created Successfully');

        DB::commit();

        return redirect()->route('admin.shop.index')->with('toast_success', 'Shop created successfully.');


    }

    public function view(Request $request,$id)
    {
        $user = User::with(['user_detail', 'bank_detail'])->where('id', $id)->first();
        return view('admin.shops.view',compact('user'));
    }

    public function edit(Request $request,$id)
    {
        $user = User::with(['user_detail', 'bank_detail'])->where('id', $id)->first();
        return view('admin.shops.edit',compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048', // Allow jpg, jpeg, png up to 2MB
            'name' => 'required|string|max:50',
            'email' => 'nullable|email|unique:users,email,'.$request->id.',id',
            'phone' => 'required|digits:10|unique:users,phone,'.$request->id.',id',
            'phone1' => 'nullable|digits:10',
            'password' => 'nullable|min:6|confirmed', // optional confirmation
            'address' => 'nullable|string|max:255',
            'slug_name' => 'required|alpha_dash|unique:users,user_name,'.$request->id.',id',
            'gst' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',

            'bank' => 'nullable|string|max:50',
            'account_number' => 'nullable|digits:16',
            'confirm_account_number' => 'nullable|same:account_number',
            'branch' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i',
        ], 
        [
            'logo.mimes' => 'Logo must be a JPG, JPEG or PNG file.',
            'logo.max' => 'Logo size must not exceed 2MB.',
            
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone number is required.',
            'phone.digits' => 'Phone number must be exactly 10 digits.',
            'phone1.digits' => 'Alternate phone number must be exactly 10 digits.',

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

        $user = User::where('id',$request->id)->first();
        $user_detail = UserDetail::where('user_id',$request->id)->first();
        $bank_detail = BankDetail::where('user_id',$request->id)->first();

        DB::beginTransaction();

        $user->update([ 
            'name' => Str::ucfirst($request->name),
            'email' => $request->email,
            'user_name' => $request->slug_name,
            'phone' => $request->phone,
            'alt_phone' => $request->phone1,
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = config('path.root') . '/' . config('path.HO.head_office') . '/' . config('path.HO.logo');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            $user->update([ 
                'logo' => $filePath,
            ]);
        }

        if($request->password != null)
        {
            $user->update([ 
                'password' => \Hash::make($request->password),
            ]);
        }

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Shop Update','App/Models/User','users',$user->id,'Update',null,$request,'Success','Shop Updated Successfully');

        $user_detail->update([
            'address' => $request->address,
            'gst' => $request->gst,
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date,
            'primary_colour' => $request->primary_colour,
            'secondary_colour' => $request->secondary_colour,
        ]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Shop Update','App/Models/UserDetail','user_details',$user_detail->id,'Update',null,$request,'Success','Shop Updated Successfully');

        $bank_detail->update([
            'name' => $request->bank,
            'holder_name' => $request->name,
            'branch' => $request->branch,
            'account_no' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
        ]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Shop Update','App/Models/BankDetail','bank_details',$bank_detail->id,'Update',null,$request,'Success','Shop Updated Successfully');

        DB::commit();

        return redirect()->back()->with('toast_success', 'Shop updated successfully.');

    }

    public function lock(Request $request,$id)
    {
        $user = User::find($id);

        if ($user) {
            $user->is_lock = $user->is_lock == 1 ? 0 : 1;
            $user->save();
        }

        $user = User::find($id);

        $statusText = $user->is_lock == 1 ? 'Shop locked Successfully' : 'Shop unlocked Successfully';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Shop Update','App/Models/User','users',$id,'Update',null,null,'Success',$statusText);

        return redirect()->back()->with('toast_success', $statusText);
    }

    public function delete(Request $request,$id)
    {
        $user = User::find($id);

        if ($user) {
            $user->is_delete = 1;
            $user->save();
        }

        $user = User::find($id);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Shop Delete','App/Models/User','users',$id,'Delete',null,null,'Success','Shop Deleted Successfully');

        return redirect()->back()->with('toast_success', 'Shop Deleted Successfully');
    }

}
