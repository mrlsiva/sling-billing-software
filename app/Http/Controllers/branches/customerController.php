<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Exports\CustomerExport;
use App\Imports\CustomerImport;
use Illuminate\Validation\Rule;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\BulkUploadLog;
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
            'gst' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
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
            'gst' => $request->gst,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Customer Create','App/Models/Customer','customers',$customer->id,'Insert',null,$request,'Success','Customer Created Successfully');

        //Notifiction
        $this->notification(Auth::user()->parent_id, null,'App/Models/Customer', $customer->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch '.Auth::user()->name. ' created new customer '.Str::ucfirst($request->name),null, null,12);

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
            'gst' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i',
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
            'gst' => $request->gst,

        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Customer Update','App/Models/Customer','customers',$customer->id,'Update',null,$request,'Success','Customer Updated Successfully');

        //Notifiction
        $this->notification(Auth::user()->parent_id, null,'App/Models/Customer', $customer->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch '.Auth::user()->name. ' updated customer '.Str::ucfirst($request->name),null, null,12);

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
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('gst', 'like', "%{$search}%");
                  });
            });
        })->orderBy('id','desc')->paginate(10);

        return view('branches.customers.order',compact('orders','customer'));

    }

    public function bulk_upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx|max:10000',
        ]);

        // Generate unique run_id
        do {
            $run_id = rand(100000, 999999);
        } while (BulkUploadLog::where('run_id', $run_id)->exists());

        $import = new CustomerImport($run_id);
        Excel::import($import, $request->file('file'));

        $skipped = [];
        if ($import->failures()->isNotEmpty()) {
            foreach ($import->failures() as $failure) {
                $skipped[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
        }

        // Counts
        $totalRecords      = $import->getRowCount(); // make sure your import counts ALL rows
        $errorRecords      = count($skipped);
        $successfulRecords = max(0, $totalRecords - $errorRecords);

        // Base directory for this run
        $directory = "bulk_uploads/customers/{$run_id}";

        // Ensure directory exists
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // 1️⃣ Save uploaded Excel file
        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $excelPath    = $uploadedFile->storeAs($directory, $originalName, 'public');

        // 2️⃣ Build log content
        $logContent  = "======================" . PHP_EOL;
        $logContent .= "Bulk Upload Report" . PHP_EOL;
        $logContent .= "Uploaded On: " . now() . PHP_EOL;
        $logContent .= "Run ID: {$run_id}" . PHP_EOL;
        $logContent .= "Uploaded File: {$originalName}" . PHP_EOL;
        $logContent .= "Total Records: {$totalRecords}" . PHP_EOL;
        $logContent .= "Successful Records: {$successfulRecords}" . PHP_EOL;
        $logContent .= "Error Records: {$errorRecords}" . PHP_EOL;

        if ($errorRecords > 0) {
            $logContent .= "Error Details:" . PHP_EOL;
            foreach ($skipped as $error) {
                $logContent .= "- {$error}" . PHP_EOL;
            }
        }

        $logContent .= "======================" . PHP_EOL . PHP_EOL;

        // 3️⃣ Save log file
        $logFile = "{$directory}/log.txt";
        Storage::disk('public')->put($logFile, $logContent);

        // 4️⃣ Save into BulkUploadLog table
        $bulk_upload = BulkUploadLog::create([
            'user_id'            => auth()->id(),
            'run_id'             => $run_id,
            'run_on'             => now(),
            'module'             => 'Customer',
            'total_record'       => $totalRecords,
            'successfull_record' => $successfulRecords,
            'error_record'       => $errorRecords,
            'excel'              => $excelPath,
            'log'                => $logFile,
        ]);

        // 5️⃣ Notification
        $this->notification(
            null,
            Auth::user()->parent_id,
            'App/Models/BulkUploadLog',
            $bulk_upload->id,
            null,
            json_encode($request->all()),
            now(),
            Auth::user()->id,
            'Bulk upload done for customers',
            null,
            $logFile,12
        );

        if ($errorRecords > 0) {
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
