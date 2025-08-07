<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BankDetail;
use App\Models\UserDetail;
use App\Traits\common;
use App\Models\User;
use App\Traits\Log;
use DB;

class branchController extends Controller
{

    use Log, common;

    public function create(Request $request,$id)
    {
        $user = User::with(['user_detail', 'bank_detail'])->where('id', $id)->first();
        return view('admin.branches.create',compact('user'));
    }

    public function store(Request $request)
    {
        $parent = User::where('id',$request->parent_id)->first();

        $request->validate([
            'logo' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048', // Allow jpg, jpeg, png up to 2MB
            'name' => 'required|string|max:50',
            'email' => ['nullable','email',
                Rule::unique('users')->ignore($parent->id)->where(function ($query) use ($parent) {
                    return $query->where('email', '!=', $parent->email);
                }),
            ],
            'phone' => ['required','digits:10','different:phone1',
                Rule::unique('users')->ignore($parent->id)->where(function ($query) use ($parent) {
                    return $query->where('phone', '!=', $parent->phone);
                }),
            ],
            'phone1' => [ 'nullable','digits:10','different:phone',
                Rule::unique('users', 'alt_phone')->ignore($parent->id)->where(function ($query) use ($parent) {
                    return $query->where('alt_phone', '!=', $parent->alt_phone);
                }),
            ],
            'password' => 'nullable|min:6|max:20|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/', // optional confirmation
            'address' => 'nullable|string|max:100',
            'slug_name' => 'required|alpha_dash|unique:users,user_name|max:50',

            'gst' => ['nullable','regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
                Rule::unique('user_details', 'gst')->ignore($parent->user_detail->id ?? null)->where(function ($query) use ($parent) {
                    return $query->where('gst', '!=', $parent->user_detail->gst ?? null);
                }),
            ],

            'bank' => 'nullable|string|max:50',
            'name' => 'nullable|string|max:50',
            'account_number' => 'nullable|numeric|digits_between:9,18|same:confirm_account_number',
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
            'phone.different' => 'Phone number and alternate phone number must be different.',
            'phone1.different' => 'Alternate phone number and phone number must be different.',
            
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            
            'address.required' => 'Address is required.',
            'slug_name.required' => 'Slug name is required.',
            'slug_name.alpha_dash' => 'Slug name may only contain letters, numbers, dashes and underscores.',
            'slug_name.unique' => 'This slug name is already taken.',
            
            'gst.required' => 'GST number is required.',
            'gst.regex' => 'GST number format is invalid.',

            'account_number.digits_between' => 'Account number must be between 9 to 18 digits.',
            'account_number.same' => 'Account numbers do not match.',
            'confirm_account_number.same' => 'Account numbers do not match.',
            'ifsc_code.regex' => 'Invalid IFSC code format.',
        ]);

        DB::beginTransaction();

        $user = User::create([ 
            'role_id' => 3,
            'parent_id' => $request->parent_id,
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
            $path = config('path.root') . '/' . $parent->user_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.logo');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            // Save to user
            $user->logo = $filePath; // This is relative to storage/app/public
            $user->save();
        }
        else
        {
            $sourceRelativePath = $parent->logo;
            $destinationRelativePath = '/'. config('path.root') . '/' . $parent->user_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.logo') . '/' . basename($parent->logo);
            Storage::disk('public')->makeDirectory(dirname($destinationRelativePath));
            if (Storage::disk('public')->exists($sourceRelativePath)) {
                Storage::disk('public')->copy($sourceRelativePath, $destinationRelativePath);
            }
            $user->logo = $parent->logo; // This is relative to storage/app/public
            $user->save();
        }


        $role = Role::where('id',3)->first()->name;
        $user->assignRole($role);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Branch Create','App/Models/User','users',$user->id,'Insert',null,$request,'Success','Branch Created Successfully');

        $user_detail = UserDetail::create([
            'user_id' => $user->id,
            'address' => $request->address,
            'gst' => $request->gst,
            'payment_method' => $request->payment_method,
            'primary_colour' => $request->primary_colour,
            'secondary_colour' => $request->secondary_colour,
        ]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Branch Create','App/Models/UserDetail','user_details',$user_detail->id,'Insert',null,$request,'Success','Branch Created Successfully');

        $bank_detail = BankDetail::create([
            'user_id' => $user->id,
            'name' => $request->bank,
            'holder_name' => $request->holder_name,
            'branch' => $request->branch,
            'account_no' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
        ]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Branch Create','App/Models/BankDetail','bank_details',$bank_detail->id,'Insert',null,$request,'Success','Branch Created Successfully');

        DB::commit();

        return redirect()->route('admin.shop.view', $request->parent_id)->with('toast_success', 'Branch created successfully.');

    }

    public function view(Request $request,$id)
    {
        $user = User::with(['user_detail', 'bank_detail'])->where('id', $id)->first();
        return view('admin.branches.view',compact('user'));
    }

    public function lock(Request $request,$id)
    {
        $user = User::find($id);

        if ($user) {
            $user->is_lock = $user->is_lock == 1 ? 0 : 1;
            $user->save();
        }

        $user = User::find($id);

        $statusText = $user->is_lock == 1 ? 'Branch locked Successfully' : 'Branch unlocked Successfully';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Branch Update','App/Models/User','users',$id,'Update',null,null,'Success',$statusText);

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
        $this->addToLog($this->unique(),Auth::user()->id,'Branch Delete','App/Models/User','users',$id,'Delete',null,null,'Success','Branch Deleted Successfully');

        return redirect()->back()->with('toast_success', 'Branch Deleted Successfully');
    }
}
