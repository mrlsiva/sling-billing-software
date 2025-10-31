<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Vendor;
use App\Traits\Log;
use DB;

class vendorController extends Controller
{

    use Log, Notifications, ResponseHelper;

    public function list(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $vendors = Vendor::where('shop_id', Auth::user()->owner_id)
            ->when($request->vendor, function ($query, $vendor) {
                $query->where(function ($q) use ($vendor) {
                    $q->where('name', 'like', "%{$vendor}%")
                      ->orWhere('phone', 'like', "%{$vendor}%");
                });
            })->orderBy('id', 'desc')->paginate(10);

            return $this->successResponse($vendors, 200, 'Successfully returned all vendors');
        }
    }

    public function store(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $rules = [
                'name' => ['required',
                    Rule::unique('vendors')->where(function ($query) use ($request) {
                        return $query->where('shop_id', Auth::user()->owner_id);
                    }),
                ],
                'phone' => 'required|digits:10',
                'email' => 'nullable|email',
                'gst' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
            ];

            $messages = [
                'name.required' => 'Name is required.',
                'phone.required' => 'Phone is required.',
                'phone.digits' => 'Phone number must be exactly 10 digits.',
                'gst.regex' => 'GST number format is invalid.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

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
            $this->addToLog($this->unique(),Auth::user()->id,'Vendor Create','App/Models/Vendor','vendors',$vendor->id,'Insert',null,json_encode($request->all()),'Success','Vendor Created Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Vendor', $vendor->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->name.' vendor created successfully',null, null);

            return $this->successResponse($vendor, 200, 'Vendor created successfully');
        }
    }

    public function view(Request $request, $vendor)
    {
        if(Auth::user()->role_id == 2)
        {
            $vendor = Vendor::find($vendor);
            return $this->successResponse($vendor, 200, 'Successfully requested vendor');
        }
    }

    public function status(Request $request, $vendor)
    {
        if(Auth::user()->role_id == 2)
        {
            $vendor = Vendor::find($vendor);

            if ($vendor) {
                $vendor->is_active = $vendor->is_active == 1 ? 0 : 1;
                $vendor->save();
            }

            $vendor = Vendor::find($vendor->id);

            $statusText = $vendor->is_active == 1 ? 'Vendor changed to active state' : 'Vendor changed to in-active state';

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Vendor Status Update','App/Models/Vendor','vendors',$vendor->id,'Update',null,null,'Success',$statusText);

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Vendor', $request->id, null, $vendor->id, now(), Auth::user()->id, $vendor->name.' '.$statusText,null, null);

            return $this->successResponse("Success", 200, $statusText);
        }
    }

    public function update(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {

            $rules = [
                'name' => ['required',
                    Rule::unique('vendors','name')->where(function ($query) use ($request) {
                        return $query->where('shop_id', Auth::user()->owner_id);
                    })->ignore($request->vendor_id), // <-- ignore current vendor ID
                ],
                'phone' => 'required|digits:10',
                'email' => 'nullable|email',
                'gst' => [
                    'nullable',
                    'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i'
                ],
            ];

            $messages = [
                'name.required' => 'Name is required.',
                'phone.required' => 'Phone is required.',
                'phone.digits' => 'Phone number must be exactly 10 digits.',
                'gst.regex' => 'GST number format is invalid.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }


            DB::beginTransaction();

            $vendor = Vendor::find($request->vendor_id);

            $vendor->update([ 
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
            $this->addToLog($this->unique(),Auth::user()->id,'Vendor Update','App/Models/Vendor','vendors',$vendor->id,'Update',null,$request,'Success','Vendor Updated Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Vendor', $vendor->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->name. ' vendor updated successfully',null, null);

            return $this->successResponse($vendor, 200, 'Vendor updated successfully');
        }
    }
    
}
