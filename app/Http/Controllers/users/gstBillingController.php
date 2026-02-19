<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Imports\GstBillingImport;
use App\Models\BulkUploadLog;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\GstBill;
use App\Models\User;
use App\Traits\Log;
use DB;

class gstBillingController extends Controller
{
    use Log, Notifications;

    public function index(Request $request,$company,$branch)
    {
        $branches = User::where([['parent_id',Auth::user()->owner_id],['is_active',1],['is_lock',0],['is_delete',0]])->get();
        
        if($branch == 0)
        {
            $subQuery = GstBill::where([
                ['shop_id', Auth::user()->owner_id],
                ['branch_id', null]
            ])->selectRaw('MAX(id) as id')->groupBy('order_id');

            $gst_bills = GstBill::joinSub($subQuery, 'latest', function ($join) {
                $join->on('gst_bills.id', '=', 'latest.id');
            })->select('gst_bills.*')->selectRaw('(SELECT SUM(gross) FROM gst_bills gb WHERE gb.order_id = gst_bills.order_id) as total_gross')->orderByDesc('gst_bills.id')->paginate(10);

        }
        else
        {
            $subQuery = GstBill::where([
                ['shop_id', Auth::user()->owner_id],
                ['branch_id', $branch]
            ])->selectRaw('MAX(id) as id')->groupBy('order_id');

            $gst_bills = GstBill::joinSub($subQuery, 'latest', function ($join) {
                $join->on('gst_bills.id', '=', 'latest.id');
            })->select('gst_bills.*')->selectRaw('(SELECT SUM(gross) FROM gst_bills gb WHERE gb.order_id = gst_bills.order_id) as total_gross')->orderByDesc('gst_bills.id')->paginate(10);

        }

        return view('users.gst_bills.index',compact('branches','gst_bills'));

    }

    public function create(Request $request,$company,$branch)
    {
        $categories = Category::where([['user_id',Auth::user()->owner_id],['is_active',1]])->get();
        return view('users.gst_bills.create',compact('categories'));
    }

    public function get_sub_category(Request $request)
    {
        return $sub_categories = SubCategory::where([['user_id',Auth::user()->owner_id],['category_id',$request->id],['is_active',1]])->get();
    }

    public function get_product(Request $request)
    {
        return $products = Product::where([['user_id',Auth::user()->owner_id],['category_id',$request->category],['sub_category_id',$request->sub_category],['is_active',1]])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch'           => 'required',
            'order_id'         => 'required|string|max:50',
            'reference_no'     => 'required|string|max:50',

            'date_time'        => 'required|date',

            'issued_by'        => 'required|string|max:50',
            'sold_by'          => 'required|string|max:50',

            'customer_name'    => 'required|string|max:50',

            'customer_phone'   => ['required','digits:10'],

            'customer_address' => 'required|string|max:200',

            'category'         => 'required|integer',
            'sub_category'     => 'required|integer',

            'product'          => 'required|integer',

            'imie'             => 'nullable|string|max:50',
            'item_code'        => 'required|string|max:50',

            'quantity'         => 'required|integer|min:1',
            'gross'            => 'required|numeric|min:0',

        ], 
        [
            'customer_name.required'  => 'Customer name is required.',
            'customer_phone.required' => 'Customer phone is required.',
            'customer_phone.digits'   => 'Phone must be 10 digits.',
            'transfer_on.required'    => 'Transfer date is required.',
            'quantity.min'            => 'Quantity must be at least 1.',
            'gross.numeric'           => 'Gross must be a valid amount.',
        ]);

        DB::beginTransaction();

        $category = Category::where('id',$request->category)->first()->name;
        $sub_category = SubCategory::where('id',$request->sub_category)->first()->name;
        $product = Product::where('id',$request->product)->first()->name;

        $gst_bill = GstBill::create([
            'shop_id'          => Auth::user()->owner_id,
            'branch_id'        => $request->branch ? $request->branch : null,  
            'order_id'         => $request->order_id,
            'reference_no'     => $request->reference_no,
            'transfer_on'      => $request->date_time,
            'issued_by'        => $request->issued_by,
            'sold_by'          => $request->sold_by,
            'customer_name'    => $request->customer_name,
            'customer_phone'   => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'category'         => $category,
            'sub_category'     => $sub_category,
            'product'          => $product,
            'imie'             => $request->imie,
            'item_code'        => $request->item_code,
            'quantity'         => $request->quantity,
            'gross'            => $request->gross,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Gst Bill Create','App/Models/GstBill','gst_bills',$gst_bill->id,'Insert',null,$request,'Success','Gst Bill Created Successfully');

        return redirect()->back()->with('toast_success', 'GST Bill Created Successfully.');
    }

    public function view_bill(Request $request,$company,$id)
    {
        $user = User::with('user_detail','bank_detail')->where('id',Auth::user()->id)->first();
        $gst_bill = GstBill::where('id',$id)->first();

        $gst_bill_details = GstBill::where('order_id',$gst_bill->order_id)->get();

        return view('bills.gst_bill',compact('gst_bill','user','gst_bill_details'));
    }

    public function bulk_upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx|max:10000',
        ]);

        do {
            $run_id = rand(100000, 999999);
        } while (BulkUploadLog::where('run_id', $run_id)->exists());

        $branchId = $request->branch ? $request->branch : null;
        $import = new GstBillingImport($run_id, $branchId);
        Excel::import($import, $request->file('file'));

        $successRecords = $import->getSuccessCount();
        $errorRecords   = $import->failures()->count();
        $totalRecords   = $successRecords + $errorRecords;

        $skipped = [];
        foreach ($import->failures() as $failure) {
            $skipped[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
        }

        $directory = "bulk_uploads/gst_bills/{$run_id}";
        Storage::disk('public')->makeDirectory($directory);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $excelPath    = $uploadedFile->storeAs($directory, $originalName, 'public');

        $logContent  = "======================" . PHP_EOL;
        $logContent .= "Bulk Upload Report" . PHP_EOL;
        $logContent .= "Run ID: {$run_id}" . PHP_EOL;
        $logContent .= "Uploaded On: " . now() . PHP_EOL;
        $logContent .= "Total Records: {$totalRecords}" . PHP_EOL;
        $logContent .= "Successful Records: {$successRecords}" . PHP_EOL;
        $logContent .= "Error Records: {$errorRecords}" . PHP_EOL;

        if ($errorRecords > 0) {
            $logContent .= "Error Details:" . PHP_EOL;
            foreach ($skipped as $error) {
                $logContent .= "- {$error}" . PHP_EOL;
            }
        }

        $logFile = "{$directory}/log.txt";
        Storage::disk('public')->put($logFile, $logContent);

        $bulk_upload = BulkUploadLog::create([
            'user_id'            => auth()->id(),
            'run_id'             => $run_id,
            'run_on'             => now(),
            'module'             => 'GstBill',
            'total_record'       => $totalRecords,
            'successfull_record' => $successRecords,
            'error_record'       => $errorRecords,
            'excel'              => $excelPath,
            'log'                => $logFile,
        ]);

        $this->notification(
            Auth::user()->owner_id,
            null,
            'App/Models/BulkUploadLog',
            $bulk_upload->id,
            null,
            json_encode($request->all()),
            now(),
            Auth::id(),
            'Bulk upload done for GST Bill',
            null,
            $logFile,
            1
        );

        if ($errorRecords > 0) {
            return back()->with('error_alert', 'Some rows were skipped: ' . implode(' | ', $skipped));
        }

        return back()->with('toast_success', 'GST Bills uploaded successfully.');
    }



}
