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
use App\Models\PosSetting;
use App\Traits\common;
use App\Models\User;
use App\Traits\Log;
use Carbon\Carbon;
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

        $ownerId = $request->parent_id ?? null;

        $request->validate([
            'logo' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048', // Allow jpg, jpeg, png up to 2MB
            'fav_icon' => 'required|mimes:jpg,jpeg,png,gif,webp|max:2048', // Allow jpg, jpeg, png up to 2MB
            'name' => 'required|string|max:50',
            'email' => ['nullable','email',
                Rule::unique('users', 'email')->where(function ($query) use ($ownerId) {
                    $query->whereNotIn('id', function ($sub) use ($ownerId) {
                        $sub->select('id')->from('users')->where('id', $ownerId)->orWhere('parent_id', $ownerId);
                    });
                }),
            ],

            'phone' => ['required','digits:10','different:phone1',
                Rule::unique('users', 'phone')->where(function ($query) use ($ownerId) {
                    $query->whereNotIn('id', function ($sub) use ($ownerId) {
                        $sub->select('id')->from('users')->where('id', $ownerId)->orWhere('parent_id', $ownerId);
                    });
                }),
            ],

            'phone1' => ['nullable','digits:10','different:phone',
                Rule::unique('users', 'alt_phone')->where(function ($query) use ($ownerId) {
                    $query->whereNotIn('id', function ($sub) use ($ownerId) {
                        $sub->select('id')->from('users')
                            ->where('id', $ownerId)
                            ->orWhere('parent_id', $ownerId);
                    });
                }),
            ],

            'gst' => ['nullable','regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
                Rule::unique('user_details', 'gst')->where(function ($query) use ($ownerId) {
                    $query->whereNotIn('user_id', function ($sub) use ($ownerId) {
                        $sub->select('id')->from('users')
                            ->where('id', $ownerId)
                            ->orWhere('parent_id', $ownerId);
                    });
                }),
            ],
            'password' => 'required|min:6|max:20|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/', // optional confirmation
            'address' => 'nullable|string|max:100',
            'slug_name' => 'required|alpha_dash|unique:users,slug_name|max:50',
            'user_name' => ['required','alpha_dash','max:20',
                Rule::unique('users', 'user_name')->where(function ($query) use ($ownerId) {
                    if ($ownerId === null) {
                        $query->whereNull('parent_id');
                    } else {
                        $query->where(function ($q) use ($ownerId) {
                            $q->where('id', $ownerId)->orWhere('parent_id', $ownerId);
                        });
                    }
                }),
            ],

            'payment_method' => 'required',
            'bill_type' => 'required',
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

            'fav_icon.required' => 'Fav Icon is required.',
            'fav_icon.mimes' => 'Fav Icon must be a JPG, JPEG or PNG file.',
            'fav_icon.max' => 'Fav Icon size must not exceed 2MB.',
            
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

            'user_name.required' => 'User name is required.',
            'user_name.alpha_dash' => 'User name may only contain letters, numbers, dashes and underscores.',

            'gst.required' => 'GST number is required.',
            'gst.regex' => 'GST number format is invalid.',
            'payment_method.required' => 'Payment Method is required.',

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
            'slug_name' => $request->slug_name,
            'user_name' => $request->user_name,
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
            $path = config('path.root') . '/' . $parent->slug_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.logo');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            // Save to user
            $user->logo = $filePath; // This is relative to storage/app/public
            $user->save();
        }
        else
        {
            $sourceRelativePath = $parent->logo;
            $destinationRelativePath = '/'. config('path.root') . '/' . $parent->slug_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.logo') . '/' . basename($parent->logo);
            Storage::disk('public')->makeDirectory(dirname($destinationRelativePath));
            if (Storage::disk('public')->exists($sourceRelativePath)) {
                Storage::disk('public')->copy($sourceRelativePath, $destinationRelativePath);
            }
            $user->logo = $parent->logo; // This is relative to storage/app/public
            $user->save();
        }


        if ($request->hasFile('fav_icon')) {
            $file = $request->file('fav_icon');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = config('path.root') . '/' . $parent->slug_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.fav_icon');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            // Save to user
            $user->fav_icon = $filePath; // This is relative to storage/app/public
            $user->save();
        }
        else
        {
            $sourceRelativePath = $parent->fav_icon;
            $destinationRelativePath = '/'. config('path.root') . '/' . $parent->slug_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.fav_icon') . '/' . basename($parent->fav_icon);
            Storage::disk('public')->makeDirectory(dirname($destinationRelativePath));
            if (Storage::disk('public')->exists($sourceRelativePath)) {
                Storage::disk('public')->copy($sourceRelativePath, $destinationRelativePath);
            }
            $user->fav_icon = $parent->fav_icon; // This is relative to storage/app/public
            $user->save();
        }


        $role = Role::where('id',3)->first()->name;
        $user->assignRole($role);

        $pos_setting = PosSetting::create([ 
            'shop_id' => $request->parent_id,
            'branch_id' => $user->id,
            'pagination' => 21,
        ]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Branch Create','App/Models/User','users',$user->id,'Insert',null,$request,'Success','Branch Created Successfully');

        $user_detail = UserDetail::create([
            'user_id' => $user->id,
            'address' => $request->address,
            'gst' => $request->gst,
            'payment_method' => $request->payment_method,
            'payment_date' => Carbon::now(),
            'primary_colour' => $request->primary_colour,
            'secondary_colour' => $request->secondary_colour,
            'bill_type' => $request->bill_type,
            'is_scan_avaiable' => $request->has('is_scan_avaiable') ? 1 : 0,
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

    public function edit(Request $request,$id)
    {
        $user = User::with(['user_detail', 'bank_detail'])->where('id', $id)->first();
        return view('admin.branches.edit',compact('user'));
    }

    public function update(Request $request)
    {
        $ownerId = $request->parent_id ?? null;
        $parent = User::where('id',$request->parent_id)->first();

        $request->validate([
            'logo' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048', // Allow jpg, jpeg, png up to 2MB
            'fav_icon' => 'required|mimes:jpg,jpeg,png,gif,webp|max:2048', // Allow jpg, jpeg, png up to 2MB
            'name' => 'required|string|max:50',
            'email' => ['nullable','email',
                Rule::unique('users', 'email')->where(function ($query) use ($ownerId) {
                    $query->whereNotIn('id', function ($sub) use ($ownerId) {
                        $sub->select('id')->from('users')->where('id', $ownerId)->orWhere('parent_id', $ownerId);
                    });
                })->ignore($request->id)
            ],
            'phone' => ['required','digits:10','different:phone1',
                Rule::unique('users', 'phone')->where(function ($query) use ($ownerId) {
                    $query->whereNotIn('id', function ($sub) use ($ownerId) {
                        $sub->select('id')->from('users')->where('id', $ownerId)->orWhere('parent_id', $ownerId);
                    });
                })->ignore($request->id)
            ],
            'phone1' => ['nullable','digits:10','different:phone',
                Rule::unique('users', 'alt_phone')->where(function ($query) use ($ownerId) {
                    $query->whereNotIn('id', function ($sub) use ($ownerId) {
                        $sub->select('id')->from('users')->where('id', $ownerId)->orWhere('parent_id', $ownerId);
                    });
                })->ignore($request->id)
            ],
            'gst' => ['nullable','regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
                Rule::unique('user_details', 'gst')->where(function ($query) use ($ownerId) {
                    $query->whereNotIn('user_id', function ($sub) use ($ownerId) {
                        $sub->select('id')->from('users')->where('id', $ownerId)->orWhere('parent_id', $ownerId);
                    });
                })->ignore($request->user_detail)
            ],
            'password' => 'nullable|min:6|max:20|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/', // optional confirmation
            'address' => 'nullable|string|max:100',
            'slug_name' => 'required|alpha_dash|unique:users,slug_name,'.$request->id.',id|max:50',

            'user_name' => ['required','alpha_dash','max:20',
                Rule::unique('users', 'user_name')->where(function ($query) use ($ownerId) 
                {
                    $query->where(function ($q) use ($ownerId) {
                        $q->where('id', $ownerId)
                          ->orWhere('parent_id', $ownerId);
                    });
                })->ignore($request->id) // Ignore current record
            ],

            'payment_method' => 'required',
            'payment_date'   => 'required|date|before_or_equal:today',
            'bill_type' => 'required',

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

            'fav_icon.required' => 'Fav Icon is required.',
            'fav_icon.mimes' => 'Fav Icon must be a JPG, JPEG or PNG file.',
            'fav_icon.max' => 'Fav Icon size must not exceed 2MB.',
            
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone number is required.',
            'phone.digits' => 'Phone number must be exactly 10 digits.',
            'phone1.digits' => 'Alternate phone number must be exactly 10 digits.',
            'phone.different' => 'Phone number and alternate phone number must be different.',
            'phone1.different' => 'Alternate phone number and phone number must be different.',
            
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            
            'address.required' => 'Address is required.',
            'slug_name.required' => 'Slug name is required.',
            'slug_name.alpha_dash' => 'Slug name may only contain letters, numbers, dashes and underscores.',
            'slug_name.unique' => 'This slug name is already taken.',
            'user_name.required' => 'User name is required.',
            'user_name.alpha_dash' => 'User name may only contain letters, numbers, dashes and underscores.',
            
            'gst.required' => 'GST number is required.',
            'gst.regex' => 'GST number format is invalid.',

            'payment_method.required_with' => 'Please select a payment method when entering a payment date.',
            'payment_date.required_with'   => 'Please enter a payment date when selecting a payment method.',

            'account_number.digits_between' => 'Account number must be between 9 to 18 digits.',
            'account_number.same' => 'Account numbers do not match.',
            'confirm_account_number.same' => 'Account numbers do not match.',
            'ifsc_code.regex' => 'Invalid IFSC code format.',
        ]);

        $user = User::where('id',$request->id)->first();
        $user_detail = UserDetail::where('user_id',$request->id)->first();
        $bank_detail = BankDetail::where('user_id',$request->id)->first();

        DB::beginTransaction();

        $user->update([ 
            'name' => Str::ucfirst($request->name),
            'email' => $request->email,
            'slug_name' => $request->slug_name,
            'user_name' => $request->user_name,
            'phone' => $request->phone,
            'alt_phone' => $request->phone1,
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();

            $path = config('path.root') . '/' . $parent->slug_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.logo');

            //$path = config('path.root') . '/' . $request->slug_name . '/' . config('path.logo');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            $user->update([ 
                'logo' => $filePath,
            ]);
        }

        if ($request->hasFile('fav_icon')) {
            $file = $request->file('fav_icon');
            $filename = time() . '_' . $file->getClientOriginalName();

            $path = config('path.root') . '/' . $parent->slug_name . '/' . config('path.branch') . '/' . $request->slug_name . '/' . config('path.fav_icon');

            //$path = config('path.root') . '/' . $request->slug_name . '/' . config('path.fav_icon');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            $user->update([ 
                'fav_icon' => $filePath,
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
            'bill_type' => $request->bill_type,
            'is_scan_avaiable' => $request->has('is_scan_avaiable') ? 1 : 0,
        ]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Shop Update','App/Models/UserDetail','user_details',$user_detail->id,'Update',null,$request,'Success','Shop Updated Successfully');

        $bank_detail->update([
            'name' => $request->bank,
            'holder_name' => $request->holder_name,
            'branch' => $request->branch,
            'account_no' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
        ]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Shop Update','App/Models/BankDetail','bank_details',$bank_detail->id,'Update',null,$request,'Success','Shop Updated Successfully');

        DB::commit();

        return redirect()->back()->with('toast_success', 'Shop updated successfully.');

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
