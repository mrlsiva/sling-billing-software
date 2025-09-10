<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Traits\Log;
use DB;

class vendorController extends Controller
{
    use Log;

    public function index(Request $request)
    {
        $vendors = Vendor::where('shop_id', Auth::user()->id)
        ->when($request->vendor, function ($query, $vendor) {
            $query->where(function ($q) use ($vendor) {
                $q->where('name', 'like', "%{$vendor}%")
                  ->orWhere('phone', 'like', "%{$vendor}%");
            });
        })
        ->orderBy('id', 'desc')
        ->paginate(10);

        return view('users.vendors.index',compact('vendors'));
    }

    public function store(Request $request)
    {

        $request->validate([

            'name' => ['required',
                Rule::unique('vendors')->where(function ($query) use ($request) {
                    return $query->where('shop_id', Auth::user()->id);
                }),
            ],
            'phone' => 'required|digits:10',
            'email' => 'nullable|email',
            'gst' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',

        ], 
        [
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone is required.',
            'phone.digits' => 'Phone number must be exactly 10 digits.',
            'gst.regex' => 'GST number format is invalid.',
        ]);

        DB::beginTransaction();

        $vendor = Vendor::create([ 
            'shop_id' => Auth::user()->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'address1' => $request->address1,
            'state' => $request->state,
            'city' => $request->city,
            'gst' => $request->gst,
            'is_active' => 1,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Vendor Create','App/Models/Vendor','vendors',$vendor->id,'Insert',null,$request,'Success','Vendor Created Successfully');

        return redirect()->back()->with('toast_success', 'Vendor created successfully.');
    }

    public function status(Request $request)
    {
        $vendor = Vendor::find($request->id);

        if ($vendor) {
            $vendor->is_active = $vendor->is_active == 1 ? 0 : 1;
            $vendor->save();
        }

        $vendor = Vendor::find($request->id);

        $statusText = $vendor->is_active == 1 ? 'Vendor changed to active state' : 'Vendor changed to in-active state';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Vendor Status Update','App/Models/Vendor','vendors',$request->id,'Update',null,null,'Success',$statusText);

        return redirect()->back()->with('toast_success', "Vendor Status Changed");
    }

    public function update(Request $request)
    {

        $request->validate([
            'vendor_name' => [
                'required',
                Rule::unique('vendors','name')->where(function ($query) use ($request) {
                    return $query->where('shop_id', Auth::user()->id);
                })->ignore($request->vendor_id), // <-- ignore current vendor ID
            ],
            'vendor_phone' => 'required|digits:10',
            'vendor_email' => 'nullable|email',
            'vendor_gst' => [
                'nullable',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i'
            ],
        ], 
        [
            'vendor_name.required' => 'Name is required.',
            'vendor_phone.required' => 'Phone is required.',
            'vendor_phone.digits' => 'Phone number must be exactly 10 digits.',
            'vendor_gst.regex' => 'GST number format is invalid.',
        ]);

        DB::beginTransaction();

        $vendor = Vendor::find($request->vendor_id);

        $vendor->update([ 
            'name' => $request->vendor_name,
            'phone' => $request->vendor_phone,
            'email' => $request->vendor_email,
            'address' => $request->vendor_address,
            'address1' => $request->vendor_address1,
            'state' => $request->vendor_state,
            'city' => $request->vendor_city,
            'gst' => $request->vendor_gst,
            'is_active' => 1,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Vendor Update','App/Models/Vendor','vendors',$vendor->id,'Update',null,$request,'Success','Vendor Updated Successfully');

        return redirect()->back()->with('toast_success', 'Vendor updated successfully.');

    }
}
