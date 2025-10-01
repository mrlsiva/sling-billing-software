<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerExport;
use App\Imports\CustomerImport;
use Illuminate\Validation\Rule;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\Gender;
use App\Models\User;
use App\Models\Order;
use App\Traits\Log;
use DB;

class customerController extends Controller
{
    use Log, Notifications;

    public function index(Request $request)
    {
        //$parent = User::where('id',Auth::user()->id)->first();
        $genders = Gender::where('is_active',1)->get();

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

        return view('branches.customers.index',compact('users','genders'));
    }

    public function store(Request $request)
    {
        $parent = User::where('id',Auth::user()->id)->first();

        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => ['required','digits:10','different:alt_phone',
                Rule::unique('customers', 'phone')->where(function ($query) use ($parent) {
                    return $query->where('user_id', $parent->parent_id);
                }),
            ],
            'alt_phone' => 'nullable|digits:10|different:phone',
            'address' => 'required|string|max:200',
            'pincode' => 'nullable|digits:6|regex:/^[1-9][0-9]{5}$/',
        ], 
        [
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone is required.',
            'address.required' => 'Phone is required.',
        ]);

        DB::beginTransaction();

        $customer = Customer::create([ 
            'user_id' => $parent->parent_id,
            'branch_id' => Auth::user()->id,
            'name' => Str::ucfirst($request->name),
            'phone' => $request->phone,
            'alt_phone' => $request->alt_phone,
            'address' => $request->address,
            'pincode' => $request->pincode,
            'gender_id' => $request->gender,
            'dob' => $request->dob,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Customer Create','App/Models/Customer','customers',$customer->id,'Insert',null,$request,'Success','Customer Created Successfully');

        //Notifiction
        $this->notification(Auth::user()->parent_id, null,'App/Models/Customer', $customer->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch '.Auth::user()->name. ' created new customer '.Str::ucfirst($request->name),null, null);

        return redirect()->back()->with('toast_success', 'Customer created successfully.');
    }

    public function edit(Request $request,$customer,$id)
    {
        $genders = Gender::where('is_active',1)->get();
        $user = Customer::where('id',$id)->first();
        return view('branches.customers.edit',compact('user','genders'));
    }

    public function update(Request $request)
    {
        $parent = User::where('id',Auth::user()->id)->first();

        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => ['required','digits:10','different:alt_phone',
                Rule::unique('customers', 'phone')->ignore($request->id)->where(function ($query) use ($parent) {
                    return $query->where('user_id', $parent->parent_id);
                }),
            ],
            'alt_phone' => 'nullable|digits:10|different:phone',
            'address' => 'required|string|max:200',
            'pincode' => 'nullable|digits:6|regex:/^[1-9][0-9]{5}$/',
        ], 
        [
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone is required.',
            'address.required' => 'Phone is required.',
        ]);

        $customer = Customer::where('id',$request->id)->first();

        DB::beginTransaction();

        $customer->update([

            'name' => Str::ucfirst($request->name),
            'phone' => $request->phone,
            'alt_phone' => $request->alt_phone,
            'address' => $request->address,
            'pincode' => $request->pincode,
            'gender_id' => $request->gender,
            'dob' => $request->dob,

        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Customer Update','App/Models/Customer','customers',$customer->id,'Update',null,$request,'Success','Customer Updated Successfully');

        //Notifiction
        $this->notification(Auth::user()->parent_id, null,'App/Models/Customer', $customer->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch '.Auth::user()->name. ' updated customer '.Str::ucfirst($request->name),null, null);

        return redirect()->back()->with('toast_success', 'Customer updated successfully.');

    }

    public function order(Request $request,$company,$id)
    {

        // $orders = Order::where([['customer_id',$id],['branch_id',Auth::user()->id]])->orderBy('id','desc')->paginate(10);

        // return view('branches.orders.index',compact('orders'));

        $customer = Customer::where('id',$id)->first();
        $orders = Order::where([['customer_id',$id],['branch_id',Auth::user()->id]])
        ->when(request('order'), function ($query) {
            $search = request('order');
            $query->where(function ($q) use ($search) {
                // Bill No
                $q->where('bill_id', 'like', "%{$search}%")
                  // Customer Name / Phone
                  ->orWhereHas('customer', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        })->orderBy('id','desc')->paginate(10);

        return view('branches.customers.order',compact('orders','customer'));

    }

    public function bulk_upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx|max:10000', // Allow larger files
        ]);

        $import = new CustomerImport();
        Excel::import($import, $request->file('file'));

        $skipped = [];
        if ($import->failures()->isNotEmpty()) {
            foreach ($import->failures() as $failure) {
                $skipped[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
        }

        if (count($skipped) > 0) {

            return redirect()->back()->with('error_alert', 'Some rows were skipped: ' . implode(' | ', $skipped)); 
        }

        return redirect()->back()->with('toast_success', 'Bulk customers uploaded successfully.');

    }

    public function download(Request $request)
    {
        //$parent = User::where('id',Auth::user()->id)->first();
        $genders = Gender::where('is_active',1)->get();
        $customer_id = Order::where([['branch_id',Auth::user()->id],['shop_id',Auth::user()->parent_id]])->select('customer_id')->get();
        $users = Customer::whereIn('id', $customer_id)
        ->when(request('customer'), function ($query) {
            $search = request('customer');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        })->orderBy('id','desc')->get();

        return Excel::download(new CustomerExport($users), 'Customers.xlsx');
    }


}
