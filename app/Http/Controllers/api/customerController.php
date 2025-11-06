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
use App\Models\Customer;
use App\Models\Order;
use App\Traits\Log;
use DB;

class customerController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function customer(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $users = Customer::where('user_id',Auth::user()->owner_id)->when(request('customer'), function ($query) {
                $search = request('customer');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })->orderBy('id','desc')->paginate(10);
        }

        if(Auth::user()->role_id == 3)
        {
            $customer_id1 = Customer::where('branch_id',Auth::user()->id)->pluck('id')->toArray();
            $customer_id2 = Order::where([['branch_id',Auth::user()->id],['shop_id',Auth::user()->parent_id]])->pluck('customer_id')->toArray();

            $customer_id = array_unique(array_merge($customer_id1, $customer_id2));

            $users = Customer::whereIn('id', $customer_id)
            ->when(request('customer'), function ($query) {
                $search = request('customer');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })->orderBy('id','desc')->paginate(10);
            
        }

        return $this->successResponse($users, 200, 'Successfully returned all customers.');
    }

    public function order(Request $request, Customer $customer)
    {

        if(Auth::user()->role_id == 2)
        {
            $orders = Order::where('customer_id',$customer->id)
            ->when(request('order'), function ($query) {
                $search = request('order');
                $query->where(function ($q) use ($search) {
                    // Bill No
                    $q->where('bill_id', 'like', "%{$search}%")
                      // Branch Name / Username
                      ->orWhereHas('branch', function ($q1) use ($search) {
                          $q1->where('name', 'like', "%{$search}%")
                             ->orWhere('user_name', 'like', "%{$search}%");
                      })
                      // Customer Name / Phone
                      ->orWhereHas('customer', function ($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%")
                             ->orWhere('gst', 'like', "%{$search}%");
                      });
                });
            })->orderBy('id','desc')->paginate(10);
        }

        if(Auth::user()->role_id == 3)
        {

            $orders = Order::where([['customer_id',$customer->id],['branch_id',Auth::user()->id]])
            ->when(request('order'), function ($query) {
                $search = request('order');
                $query->where(function ($q) use ($search) {
                    // Bill No
                    $q->where('bill_id', 'like', "%{$search}%")
                    // Customer Name / Phone
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('gst', 'like', "%{$search}%");
                    });
                });
            })->orderBy('id','desc')->paginate(10);
        }

        return $this->successResponse($orders, 200, 'Successfully returned all orders of the customers.');

    }

    public function store(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {

            $rules = [
                'name' => 'required|string|max:50',
                'phone' => ['required','digits:10','different:alt_phone',
                    Rule::unique('customers', 'phone')->where(function ($query) {
                        return $query->where('user_id', Auth::user()->owner_id);
                    }),
                ],
                'alt_phone' => 'nullable|digits:10|different:phone',
                'address' => 'required|string|max:200',
                'pincode' => 'nullable|digits:6|regex:/^[1-9][0-9]{5}$/',
                'gst' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
            ];

            $messages = [
                'name.required' => 'Name is required.',
                'phone.required' => 'Phone is required.',
                'address.required' => 'Phone is required.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            $customer = Customer::create([ 
                'user_id' => Auth::user()->owner_id,
                'name' => Str::ucfirst($request->name),
                'phone' => $request->phone,
                'alt_phone' => $request->alt_phone,
                'address' => $request->address,
                'pincode' => $request->pincode,
                'gender_id' => $request->gender,
                'dob' => $request->dob,
                'gst' => $request->gst,
            ]);

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Customer Create','App/Models/Customer','customers',$customer->id,'Insert',null,$request,'Success','Customer Created Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Customer', $customer->id, null, json_encode($request->all()), now(), Auth::user()->id, 'You created new customer '.Str::ucfirst($request->name),null, null,12);
        }

        if(Auth::user()->role_id == 3)
        {

            $parent = User::where('id',Auth::user()->id)->first();

            $rules = [
                'name' => 'required|string|max:50',
                'phone' => ['required','digits:10','different:alt_phone',
                    Rule::unique('customers', 'phone')->where(function ($query) {
                        return $query->where('user_id', Auth::user()->parent_id);
                    }),
                ],
                'alt_phone' => 'nullable|digits:10|different:phone',
                'address' => 'required|string|max:200',
                'pincode' => 'nullable|digits:6|regex:/^[1-9][0-9]{5}$/',
                'gst' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
            ];

            $messages = [
                'name.required' => 'Name is required.',
                'phone.required' => 'Phone is required.',
                'address.required' => 'Phone is required.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            $customer = Customer::create([ 
                'user_id' => Auth::user()->parent_id,
                'branch_id' => Auth::user()->id,
                'name' => Str::ucfirst($request->name),
                'phone' => $request->phone,
                'alt_phone' => $request->alt_phone,
                'address' => $request->address,
                'pincode' => $request->pincode,
                'gender_id' => $request->gender,
                'dob' => $request->dob,
                'gst' => $request->gst,
            ]);

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Customer Create','App/Models/Customer','customers',$customer->id,'Insert',null,$request,'Success','Customer Created Successfully');

            //Notifiction
            $this->notification(Auth::user()->parent_id, null,'App/Models/Customer', $customer->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch '.Auth::user()->name. ' created new customer '.Str::ucfirst($request->name),null, null,12);

        }

        return $this->successResponse($customer, 200, 'Customer created successfully');
    }

    public function view(Request $request,$customer)
    {
        if(Auth::user()->role_id == 2)
        {

            $customer = Customer::with('gender')->where([['id',$customer],['user_id',Auth::user()->owner_id]])->first();
        }

        if(Auth::user()->role_id == 3)
        {
            $customer = Customer::with('gender')->where([['id',$customer],['user_id',Auth::user()->parent_id]])->first();
        }

        return $this->successResponse($customer, 200, 'Customer returned successfully');
    }

    public function update(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {

            $rules = [
                'name' => 'required|string|max:50',
                'phone' => [
                    'required',
                    'digits:10',
                    'different:alt_phone',
                    Rule::unique('customers', 'phone')
                        ->ignore($request->id)
                        ->where(function ($query) {
                            return $query->where('user_id', Auth::user()->owner_id);
                        }),
                ],
                'gst' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
                'alt_phone' => 'nullable|digits:10|different:phone',
                'address' => 'required|string|max:200',
                'pincode' => 'nullable|digits:6|regex:/^[1-9][0-9]{5}$/',
            ];

            $messages = [
                'name.required' => 'Name is required.',
                'phone.required' => 'Phone is required.',
                'address.required' => 'Phone is required.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            $customer = Customer::where([['id',$request->id],['user_id',Auth::user()->owner_id]])->first();

            $customer->update([

                'name' => Str::ucfirst($request->name),
                //'phone' => $request->phone,
                'alt_phone' => $request->alt_phone,
                'address' => $request->address,
                'pincode' => $request->pincode,
                'gender_id' => $request->gender,
                'dob' => $request->dob,
                'gst' => $request->gst,

            ]);

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Customer Update','App/Models/Customer','customers',$customer->id,'Update',null,$request,'Success','Customer Updated Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Customer', $customer->id, null, json_encode($request->all()), now(), Auth::user()->id, 'You updated customer '.Str::ucfirst($request->name),null, null,12);
        }

        if(Auth::user()->role_id == 3)
        {

            $parent = User::where('id',Auth::user()->id)->first();

            $rules = [
                'name' => 'required|string|max:50',
                'phone' => [
                    'required',
                    'digits:10',
                    'different:alt_phone',
                    Rule::unique('customers', 'phone')
                        ->ignore($request->id)
                        ->where(function ($query) {
                            return $query->where('user_id', Auth::user()->parent_id);
                        }),
                ],
                'gst' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
                'alt_phone' => 'nullable|digits:10|different:phone',
                'address' => 'required|string|max:200',
                'pincode' => 'nullable|digits:6|regex:/^[1-9][0-9]{5}$/',
            ];

            $messages = [
                'name.required' => 'Name is required.',
                'phone.required' => 'Phone is required.',
                'address.required' => 'Phone is required.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            $customer = Customer::where([['id',$request->id],['user_id',Auth::user()->parent_id]])->first();

            $customer->update([

                'name' => Str::ucfirst($request->name),
                //'phone' => $request->phone,
                'alt_phone' => $request->alt_phone,
                'address' => $request->address,
                'pincode' => $request->pincode,
                'gender_id' => $request->gender,
                'dob' => $request->dob,
                'gst' => $request->gst,

            ]);

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Customer Update','App/Models/Customer','customers',$customer->id,'Update',null,$request,'Success','Customer Updated Successfully');

            //Notifiction
            $this->notification(Auth::user()->parent_id, null,'App/Models/Customer', $customer->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch '.Auth::user()->name. ' updated customer '.Str::ucfirst($request->name),null, null,12);
        }

        return $this->successResponse($customer, 200, 'Customer updated successfully');
    }

}
