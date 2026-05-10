<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Imports\GstBillingImport;
use App\Models\BulkUploadLog;
use App\Traits\ResponseHelper;
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
    use Log, Notifications, ResponseHelper;

    // GET /api/gst_bills?branch=0  (0 = HO, or branch user_id)
    public function index(Request $request)
    {
        $branch   = $request->branch ?? 0;
        $shopId   = Auth::user()->owner_id;

        $branches = User::where([
            ['parent_id', $shopId],
            ['is_active', 1],
            ['is_lock', 0],
            ['is_delete', 0],
        ])->get();

        if ($branch == 0) {
            $subQuery = GstBill::where([['shop_id', $shopId], ['branch_id', null]])
                ->selectRaw('MAX(id) as id')
                ->groupBy('order_id');
        } else {
            $subQuery = GstBill::where([['shop_id', $shopId], ['branch_id', $branch]])
                ->selectRaw('MAX(id) as id')
                ->groupBy('order_id');
        }

        $gst_bills = GstBill::joinSub($subQuery, 'latest', fn($j) => $j->on('gst_bills.id', '=', 'latest.id'))
            ->select('gst_bills.*')
            ->selectRaw('(SELECT SUM(gross) FROM gst_bills gb WHERE gb.order_id = gst_bills.order_id) as total_gross')
            ->orderByDesc('gst_bills.id')
            ->paginate(10);

        return $this->successResponse(
            compact('gst_bills', 'branches'),
            200,
            'GST bills retrieved successfully.'
        );
    }

    public function create_data(Request $request)
    {
        $categories = Category::where([['user_id', Auth::user()->owner_id], ['is_active', 1]])->get();

        return $this->successResponse($categories, 200, 'Create data retrieved successfully.');
    }

    public function get_sub_category(Request $request)
    {
        $sub_categories = SubCategory::where([
            ['user_id', Auth::user()->owner_id],
            ['category_id', $request->id],
            ['is_active', 1],
        ])->get();

        return $this->successResponse($sub_categories, 200, 'Sub categories retrieved successfully.');
    }

    public function get_product(Request $request)
    {
        $products = Product::where([
            ['user_id', Auth::user()->owner_id],
            ['category_id', $request->category],
            ['sub_category_id', $request->sub_category],
            ['is_active', 1],
        ])->get();

        return $this->successResponse($products, 200, 'Products retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch'           => 'required',
            'order_id'         => 'required|string|max:50',
            'reference_no'     => 'required|string|max:50',
            'date_time'        => 'required|date',
            'issued_by'        => 'required|string|max:50',
            'sold_by'          => 'required|string|max:50',
            'customer_name'    => 'required|string|max:50',
            'customer_phone'   => 'required|digits:10',
            'customer_address' => 'required|string|max:200',
            'category'         => 'required|integer',
            'sub_category'     => 'required|integer',
            'product'          => 'required|integer',
            'imie'             => 'nullable|string|max:50',
            'item_code'        => 'required|string|max:50',
            'quantity'         => 'required|integer|min:1',
            'gross'            => 'required|numeric|min:0',
        ], [
            'customer_name.required'  => 'Customer name is required.',
            'customer_phone.required' => 'Customer phone is required.',
            'customer_phone.digits'   => 'Phone must be 10 digits.',
            'quantity.min'            => 'Quantity must be at least 1.',
            'gross.numeric'           => 'Gross must be a valid amount.',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        DB::beginTransaction();

        $category     = Category::find($request->category)->name;
        $sub_category = SubCategory::find($request->sub_category)->name;
        $product      = Product::find($request->product)->name;

        $gst_bill = GstBill::create([
            'shop_id'          => Auth::user()->owner_id,
            'branch_id'        => $request->branch ?: null,
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

        $this->addToLog($this->unique(), Auth::user()->id, 'Gst Bill Create', 'App/Models/GstBill', 'gst_bills', $gst_bill->id, 'Insert', null, $request, 'Success', 'Gst Bill Created Successfully');

        if ($gst_bill->branch_id == null) {
            $this->notification(Auth::user()->id, null, 'App/Models/GstBill', $gst_bill->id, null, json_encode($request->all()), now(), Auth::user()->id, 'HO ' . Auth::user()->user_name . ' created a GST bill for the amount of ' . $request->gross . '.', null, null, 17);
        } else {
            $branch = User::find($request->branch);
            $this->notification(Auth::user()->id, null, 'App/Models/GstBill', $gst_bill->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch ' . $branch->user_name . ' created a GST bill for the amount of ' . $request->gross . '.', null, null, 17);
        }

        return $this->successResponse(['gst_bill_id' => $gst_bill->id], 200, 'GST Bill created successfully.');
    }

    public function view_bill(Request $request, $id)
    {
        $gst_bill = GstBill::find($id);

        if (!$gst_bill) {
            return $this->errorResponse([], 404, 'GST Bill not found.');
        }

        $user             = User::with('user_detail', 'bank_detail')->find(Auth::user()->id);
        $gst_bill_details = GstBill::where('order_id', $gst_bill->order_id)->get();

        return $this->successResponse(
            compact('gst_bill', 'user', 'gst_bill_details'),
            200,
            'GST Bill retrieved successfully.'
        );
    }

    public function bulk_upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx|max:10000',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        do {
            $run_id = rand(100000, 999999);
        } while (BulkUploadLog::where('run_id', $run_id)->exists());

        $branchId = $request->branch ?: null;
        $import   = new GstBillingImport($run_id, $branchId);
        \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

        $successRecords = $import->getSuccessCount();
        $errorRecords   = $import->failures()->count();
        $totalRecords   = $successRecords + $errorRecords;

        $skipped = [];
        foreach ($import->failures() as $failure) {
            $skipped[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
        }

        $directory    = "bulk_uploads/gst_bills/{$run_id}";
        Storage::disk('public')->makeDirectory($directory);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $excelPath    = $uploadedFile->storeAs($directory, $originalName, 'public');

        $logContent  = "======================\nBulk Upload Report\nRun ID: {$run_id}\nUploaded On: " . now() . "\nTotal Records: {$totalRecords}\nSuccessful Records: {$successRecords}\nError Records: {$errorRecords}\n";
        if ($errorRecords > 0) {
            $logContent .= "Error Details:\n";
            foreach ($skipped as $error) {
                $logContent .= "- {$error}\n";
            }
        }
        $logContent .= "======================\n";

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

        $this->notification(Auth::user()->owner_id, null, 'App/Models/BulkUploadLog', $bulk_upload->id, null, json_encode($request->all()), now(), Auth::id(), 'Bulk upload done for GST Bill', null, $logFile, 1);

        if ($errorRecords > 0) {
            return $this->errorResponse($skipped, 422, 'Some rows were skipped during upload.');
        }

        return $this->successResponse(
            ['total' => $totalRecords, 'success' => $successRecords, 'errors' => $errorRecords],
            200,
            'GST Bills uploaded successfully.'
        );
    }
}