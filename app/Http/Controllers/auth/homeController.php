<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ShopPayment;
use App\Models\BankDetail;
use App\Models\UserDetail;
use App\Models\Payment;
use App\Traits\common;
use App\Models\User;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class homeController extends Controller
{
    use Log, common;

    public function home(Request $request)
    {
        $user = User::where('slug_name',request()->route('company'))->with(['user_detail', 'bank_detail'])->first();

        return view('users.home',compact('user'));
        

    }

    public function register(Request $request)
    {
        $request->validate([
            'logo' => 'required|mimes:jpg,jpeg,png,gif,webp|max:2048', // Allow jpg, jpeg, png up to 2MB
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'phone' => 'required|digits:10|different:phone1|unique:users',
            'phone1' => 'nullable|digits:10|different:phone|unique:users,alt_phone',
            'password' => 'required|min:6|max:20|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/', // optional confirmation
            'address' => 'nullable|string|max:100',
            'gst' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i|unique:user_details,gst',
            'bill_type' => 'required',
            'payment_method' => 'required',
        ], 
        [
            'logo.required' => 'Logo is required.',
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

            'email.required' => 'Email is required.',
            
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            
            'address.required' => 'Address is required.',
            'gst.regex' => 'GST number format is invalid.',
            'bill_type.required' => 'Bill Type is required.',
            'payment_method.required' => 'Payment Method is required.',
        ]);

        DB::beginTransaction();

        // Remove spaces & special characters (keep only letters and numbers)
        $name = preg_replace('/[^A-Za-z0-9]/', '', $request->name);

        // Optional: make it lowercase
        $name = strtolower($name);

        $user = User::create([ 
            'role_id' => 2,
            'unique_id' => $this->userUnique(),
            'name' => Str::ucfirst($request->name),
            'email' => $request->email,
            'slug_name' => $name,
            'user_name' => $name,
            'phone' => $request->phone,
            'alt_phone' => $request->phone1,
            'password' => \Hash::make($request->password),
            'is_active' => 1,
            'is_lock' => 0,
            'is_delete' => 0,
        ]);

        $user->update([
            'owner_id' => $user->id,
            'created_by' => $user->id
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = config('path.root') . '/' . $request->slug_name . '/' . config('path.logo');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            // Save to user
            $user->logo = $filePath; // This is relative to storage/app/public
            $user->fav_icon = $filePath; 
            $user->save();
        }


        $role = Role::where('id',2)->first()->name;
        $user->assignRole($role);

        //Log
        $this->addToLog($this->unique(),$user->id,'Shop Create','App/Models/User','users',$user->id,'Insert',null,$request,'Success','Shop Created Successfully');

        $paymentDate = Carbon::now();
        $paymentMethod = $request->payment_method;

        switch ($paymentMethod) {
            case 1:
                $nextPaymentDate = $paymentDate->copy()->addMonth();
            break;
            case 2:
                $nextPaymentDate = $paymentDate->copy()->addMonths(3);
            break;
            case 3:
                $nextPaymentDate = $paymentDate->copy()->addMonths(6);
            break;
            case 4:
                $nextPaymentDate = $paymentDate->copy()->addYear();
            break;
            default:
                $nextPaymentDate = null;
        }

        $user_detail = UserDetail::create([
            'user_id' => $user->id,
            'address' => $request->address,
            'gst' => $request->gst,
            'payment_method' => $request->payment_method,
            'payment_date' => Carbon::now(),
            'plan_start' => Carbon::now(),
            'plan_end' => $nextPaymentDate,
            'bill_type' => $request->bill_type,
            'is_scan_avaiable' => 1,
            'is_bill_enabled' => 1,
            'is_size_differentiation_available' => 0,
            'is_colour_differentiation_available' => 0,
        ]);

        //Log
        $this->addToLog($this->unique(),$user->id,'Shop Create','App/Models/UserDetail','user_details',$user_detail->id,'Insert',null,$request,'Success','Shop Created Successfully');

        $bank_detail = BankDetail::create([
            'user_id' => $user->id,
        ]);

        //Log
        $this->addToLog($this->unique(),$user->id,'Shop Create','App/Models/BankDetail','bank_details',$bank_detail->id,'Insert',null,$request,'Success','Shop Created Successfully');

        $payments = Payment::where('is_active',1)->get();
        foreach($payments as $payment)
        {
            $shop_payment = ShopPayment::create([
                'shop_id' => $user->id,
                'payment_id' => $payment->id
            ]);
        }

        DB::commit();

        return redirect()->back()->with('toast_success', 'Registered successfully. Please check your email for further details.');
    }
}
