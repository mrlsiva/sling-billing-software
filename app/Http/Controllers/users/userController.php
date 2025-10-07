<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerExport;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Gender;
use App\Traits\Log;
use DB;

class userController extends Controller
{
    use Log, Notifications;

    public function index(Request $request)
    {
        $genders = Gender::where('is_active',1)->get();
        $users = Customer::where('user_id',Auth::user()->id)
        ->when(request('customer'), function ($query) {
            $search = request('customer');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        })->orderBy('id','desc')->paginate(10);

        return view('users.customers.index',compact('users','genders'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => ['required','digits:10','different:alt_phone',
                Rule::unique('customers', 'phone')->where(function ($query) {
                    return $query->where('user_id', Auth::user()->id);
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
            'user_id' => Auth::user()->id,
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

    public function order(Request $request,$company,$id)
    {

        $customer = Customer::where('id',$id)->first();
        $orders = Order::where('customer_id',$id)
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
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        })->orderBy('id','desc')->paginate(10);

        return view('users.customers.order',compact('orders','customer'));

    }

    public function download(Request $request)
    {
        $users = Customer::with('gender')
            ->where('user_id', Auth::user()->id)
            ->when($request->customer, function ($query) use ($request) {
                $search = $request->customer;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('id','desc')
            ->get();

        return Excel::download(new CustomerExport($users), 'Customers.xlsx'); // ✅ pass $users
    }
}
