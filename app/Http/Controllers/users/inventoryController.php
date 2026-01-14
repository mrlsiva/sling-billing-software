<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Imports\ProductTransferImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductTransferErrorExport;
use App\Models\BulkUploadLog;
use Illuminate\Http\Request;
use App\Traits\Notifications;
use App\Models\ProductHistory;
use App\Models\StockVariation;
use App\Models\SubCategory;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Traits\Log;
use DB;

class inventoryController extends Controller
{
    use Log, Notifications;

    public function stock(Request $request,$company,$shop,$branch)
    {

        if ($branch != 0) {

            $stocks = Stock::where('shop_id', $shop)->where('branch_id', $branch)
            ->when(request('product'), function ($query) {
                $search = request('product');
                $query->where(function ($q) use ($search) {
                    // product name
                    $q->whereHas('product', function ($q1) use ($search) {
                        $q1->where('name', 'like', "%{$search}%");
                    })
                    // category name
                    ->orWhereHas('product.category', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    // subcategory name
                    ->orWhereHas('product.sub_category', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%{$search}%");
                    });
                });
            })->orderBy('category_id')->orderBy('sub_category_id')->orderBy('product_id')->paginate(10);
        }
        else
        {
            $stocks = Stock::where('shop_id', $shop)->whereNull('branch_id')
            ->when(request('product'), function ($query) {
                $search = request('product');
                $query->where(function ($q) use ($search) {
                    // product name
                    $q->whereHas('product', function ($q1) use ($search) {
                        $q1->where('name', 'like', "%{$search}%");
                    })
                    // category name
                    ->orWhereHas('product.category', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    // subcategory name
                    ->orWhereHas('product.sub_category', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%{$search}%");
                    });
                });
            })->orderBy('category_id')->orderBy('sub_category_id')->orderBy('product_id')->paginate(10);
        }

        $branches = User::where([['parent_id',Auth::user()->owner_id],['is_active',1],['is_lock',0],['is_delete',0]])->get();
        $categories = Category::where([['user_id',Auth::user()->owner_id],['is_active',1]])->get();

        return view('users.inventories.stock',compact('stocks','branches','categories'));
    }

    public function get_stock_variation($company, Stock $stock)
    {
        $variations = StockVariation::with(['size', 'colour'])
            ->where('stock_id', $stock->id)
            ->get();

        return view('users.inventories.variation', compact('stock', 'variations'));
    }

    public function transfer(Request $request)
    {
        $categories = Category::where([
            ['user_id', Auth::user()->owner_id],
            ['is_active', 1]
        ])->get();

        $branches = User::where([
            ['parent_id', Auth::user()->owner_id],
            ['is_active', 1],
            ['is_lock', 0],
            ['is_delete', 0]
        ])->get();

        $transfers = ProductHistory::selectRaw('
                MAX(id) as id,
                invoice,
                MAX(`to`) as `to`,
                MAX(transfer_on) as transfer_on,
                MAX(transfer_by) as transfer_by
            ')
            ->where(function ($q) {
                $q->where('from', Auth::user()->owner_id)
                  ->orWhere('to', Auth::user()->owner_id);
            })
            ->when(request()->filled('product'), function ($query) {
                $product = request('product');
                $query->whereHas('product', function ($q) use ($product) {
                    $q->where('name', 'like', "%{$product}%");
                });
            })
            ->when(request()->filled('branch'), function ($query) {
                $branch = request('branch');
                $query->where(function ($q) use ($branch) {
                    $q->where('to', $branch)
                      ->orWhere('from', $branch);
                });
            })
            ->groupBy('invoice')
            ->with(['transfer_from', 'transfer_to']) // product not needed since grouped
            ->orderBy('id', 'Desc')
            ->paginate(10);

        return view('users.inventories.transfer', compact(
            'categories',
            'transfers',
            'branches'
        ));
    }


    public function get_bill(Request $request,$company,$id)
    {
        $transfer_detail = ProductHistory::where('id',$id)->first();
        $transfer_products = ProductHistory::where('invoice',$transfer_detail->invoice)->get();

         return view('users.inventories.bill',compact('transfer_detail','transfer_products'));
    }

    public function get_sub_category(Request $request)
    {
        return $sub_categories = SubCategory::where([['user_id',Auth::user()->id],['category_id',$request->id],['is_active',1]])->get();
    }


    public function get_product(Request $request)
    {
        return $products = Product::where([['user_id',Auth::user()->id],['category_id',$request->category],['sub_category_id',$request->sub_category],['is_active',1]])->get();
    }

    public function get_product_detail(Request $request)
    {
        $product = Stock::with('product.metric')->where([['shop_id', Auth::user()->owner_id],['product_id', $request->product]])->first();

        $imeis = [];

        if (!empty($product->imei)) {
            $imeis = array_filter(explode(',', $product->imei), function ($i) {
                return trim($i) !== "";
            });
        }

        $variations = StockVariation::with(['size','colour'])
        ->where('stock_id', $product->id)
        ->get();

        return response()->json([
            'product'     => $product,
            'quantity'    => $product->quantity,
            'imeis'       => $imeis,
            'variations'  => $variations,
        ]);


        //return $product = Product::with('metric')->where('id',$request->product)->first();
    }

    public function store(Request $request)
    {

        $request->validate([
            'branch'      => 'required',
            'category'      => 'required',
            'sub_category'  => 'required',
            'product'       => 'required',
            'quantity'      => 'required|numeric|min:0',
        ], 
        [
            'branch.required'       => 'Branch is required.',
            'category.required'     => 'Category is required.',
            'sub_category.required' => 'Sub Category is required.',
            'product.required'      => 'Product is required.',
            'quantity.required'     => 'Quantity is required.',
            'quantity.numeric'      => 'Quantity must be a number.',
            'quantity.min'          => 'Quantity cannot be negative.',
        ]);

        // IMEIs selected by user
        $selectedImeis = $request->imeis ?? [];

        $product = Product::findOrFail($request->product);

        if ($product->quantity == 0) 
        {
            return redirect()->back()->with('toast_error', 'You cant transfer a product with 0 quantity.');
        }

        if ($product->quantity < $request->quantity) 
        {
            return redirect()->back()->with('toast_error', 'Quantity can’t be greater than stock.');
        }

        DB::beginTransaction();

        // Update or create branch stock
        $branchStock = Stock::where([['branch_id', $request->branch],['product_id', $request->product]])->first();

        if ($branchStock) 
        {
            $branchImeis = [];

            // If branch already has IMEIs, append
            if ($branchStock && $branchStock->imei) {
                $branchImeis = explode(',', $branchStock->imei);
            }

            // Merge existing + new IMEIs
            $updatedBranchImeis = array_merge($branchImeis, $selectedImeis);

            $branchStock->update([
                'shop_id'        => Auth::user()->owner_id,
                'branch_id'      => $request->branch,
                'category_id'    => $request->category,
                'sub_category_id'=> $request->sub_category,
                'product_id'     => $request->product,
                'quantity'       => $branchStock->quantity + $request->quantity,
                'is_active'      => 1,
                'imei' => implode(',', $updatedBranchImeis)
            ]);

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Stock Updated','App/Models/Stock','stocks',$branchStock->id,'Update',null,$request,'Success','Stock Updated for this product');
        } 
        else 
        {
            $branchStock = Stock::create([
                'shop_id'        => Auth::user()->owner_id,
                'branch_id'      => $request->branch,
                'category_id'    => $request->category,
                'sub_category_id'=> $request->sub_category,
                'product_id'     => $request->product,
                'quantity'       => $request->quantity,
                'is_active'      => 1,
                'imei' => implode(',', $selectedImeis)
            ]);

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Stock Added','App/Models/Stock','stocks',$branchStock->id,'Insert',null,$request,'Success','Stock Added for this product');
        }

        // Deduct from main shop stock
        $mainStock = Stock::where([['shop_id', Auth::user()->owner_id],['branch_id', null],['product_id', $request->product]])->first();

        
        if ($mainStock) 
        {
            $mainImeis = [];

            if ($mainStock && $mainStock->imei) {
                $mainImeis = explode(',', $mainStock->imei);
            }

            // Remove transferred IMEIs from main shop IMEI list
            $remainingImeis = array_diff($mainImeis, $selectedImeis);

            $mainStock->update([
                'quantity' => $mainStock->quantity - $request->quantity,
                'imei' => implode(',', $remainingImeis)
            ]);
        }

        // Update product table stock
        Product::where('id', $request->product)->update(['quantity' => $product->quantity - $request->quantity]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Quantity Updated','App/Models/Poduct','products',$product->id,'Update',null,$request,'Success','Quantity Updated for this product');

        $lastInvoice = ProductHistory::lockForUpdate()->max('invoice');

        $next = $lastInvoice ? ((int) ltrim($lastInvoice, '0') + 1) : 1;

        $invoice = str_pad($next, 5, '0', STR_PAD_LEFT);

        $transfer = ProductHistory::create([
            'invoice'        => $invoice,
            'from'           => Auth::user()->id,
            'to'             => $request->branch,
            'category_id'    => $request->category,
            'sub_category_id'=> $request->sub_category,
            'product_id'     => $request->product,
            'quantity'       => $request->quantity,
            'transfer_on'    => now(),
            'transfer_by'    => Auth::user()->id,
        ]);

        foreach ($request->variation_qty as $variationId => $qty) 
        {
            if ($qty > 0) {

                $mainV = StockVariation::find($variationId);

                // Find if variation already exists for this branch
                $branchV = StockVariation::where([
                    ['stock_id', $branchStock->id],
                    ['size_id', $mainV->size_id],
                    ['colour_id', $mainV->colour_id],
                    ['product_id', $request->product],
                ])->first();

                if ($branchV) {
                    $branchV->update([
                        'quantity' => $branchV->quantity + $qty
                    ]);
                } else {
                    StockVariation::create([
                        'stock_id'  => $branchStock->id,
                        'product_id'=> $request->product,
                        'size_id'   => $mainV->size_id,
                        'colour_id' => $mainV->colour_id,
                        'quantity'  => $qty,
                        'price'     => $mainV->price
                    ]);
                }
            }
        }


        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Product Transfer','App/Models/ProductHistory','product_histories',$transfer->id,'Create',null,$request,'Success','Product Transfered Successfully');

        //Notification
        $this->notification(Auth::user()->owner_id, null,'App/Models/ProductHistory', $transfer->id, null, json_encode($request->all()), now(), Auth::user()->id, $transfer->product->name.' has been successfully transfered to branch '.$transfer->transfer_to->name,null, null,8);

        //Notification
        $this->notification(null, $request->branch,'App/Models/ProductHistory', $transfer->id, null, json_encode($request->all()), now(), Auth::user()->id, $transfer->product->name.' has been successfully transfered to your branch '.$transfer->transfer_to->name,null, null,8);

        DB::commit();

        return redirect()->back()->with('toast_success', 'Product transferred successfully.');
    }


    public function bulk(Request $request)
    {
        $request->validate([
            'branch' => 'required',
            'file'   => 'required|file|mimes:xlsx,xls|max:10000',
        ]);

        // Generate unique run_id
        do {
            $run_id = rand(100000, 999999);
        } while (BulkUploadLog::where('run_id', $run_id)->exists());

        DB::beginTransaction();

        try {

            $import = new ProductTransferImport(auth()->user()->owner_id);
            Excel::import($import, $request->file('file'));

            // Error handling
            $errors = $import->errors ?? [];
            $errorRecords = count($errors);

            if ($errorRecords > 0) {

                DB::rollBack();

                // Directory
                $directory = "bulk_uploads/product_transfer/{$run_id}";
                Storage::disk('public')->makeDirectory($directory);

                // Save uploaded Excel
                $uploadedFile = $request->file('file');
                $originalName = $uploadedFile->getClientOriginalName();
                $excelPath    = $uploadedFile->storeAs($directory, $originalName, 'public');

                // Build log
                $logContent  = "======================\n";
                $logContent .= "Bulk Product Transfer Report\n";
                $logContent .= "Uploaded On: " . now() . "\n";
                $logContent .= "Run ID: {$run_id}\n";
                $logContent .= "Uploaded File: {$originalName}\n";
                $logContent .= "Error Records: {$errorRecords}\n";
                $logContent .= "Error Details:\n";

                foreach ($errors as $error) {
                    $logContent .= "- Row {$error['row']}: {$error['error']}\n";
                }


                $logContent .= "======================\n\n";

                $logFile = "{$directory}/log.txt";
                Storage::disk('public')->put($logFile, $logContent);

                // Save log record
                $bulk_upload = BulkUploadLog::create([
                    'user_id'            => auth()->id(),
                    'run_id'             => $run_id,
                    'run_on'             => now(),
                    'module'             => 'Product Transfer',
                    'total_record'       => $import->getRowCount(),
                    'successfull_record' => 0,
                    'error_record'       => $errorRecords,
                    'excel'              => $excelPath,
                    'log'                => $logFile,
                ]);

                // Notification
                $this->notification(
                    Auth::user()->owner_id,
                    null,
                    'App/Models/BulkUploadLog',
                    $bulk_upload->id,
                    null,
                    json_encode($request->all()),
                    now(),
                    Auth::user()->id,
                    'Bulk product transfer failed',
                    null,
                    $logFile,
                    1
                );

                return back()->with('toast_success', 'Failed to import excel.');

                // return Excel::download(
                //     new ProductTransferErrorExport($errors),
                //     'bulk_transfer_errors.xlsx'
                // );
            }

            /*
             |--------------------------------------------------------------------------
             | ACTUAL TRANSFER LOGIC HERE
             |--------------------------------------------------------------------------
             */

            // AFTER validation success
            foreach ($import->validRows as $row) {

                $this->transferProduct([
                    'branch_id'       => $request->branch,
                    'category_id'     => $row['category_id'],
                    'sub_category_id' => $row['sub_category_id'],
                    'product_id'      => $row['product_id'],
                    'quantity'        => $row['quantity'],
                    'imeis'           => $row['imeis'],
                ]);
            }


            DB::commit();

            // Counts
            $totalRecords      = $import->getRowCount();
            $successfulRecords = $totalRecords;

            // Directory
            $directory = "bulk_uploads/product_transfer/{$run_id}";
            Storage::disk('public')->makeDirectory($directory);

            // Save uploaded Excel
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();
            $excelPath    = $uploadedFile->storeAs($directory, $originalName, 'public');

            // Log file
            $logContent  = "======================\n";
            $logContent .= "Bulk Product Transfer Report\n";
            $logContent .= "Uploaded On: " . now() . "\n";
            $logContent .= "Run ID: {$run_id}\n";
            $logContent .= "Uploaded File: {$originalName}\n";
            $logContent .= "Total Records: {$totalRecords}\n";
            $logContent .= "Successful Records: {$successfulRecords}\n";
            $logContent .= "Error Records: 0\n";
            $logContent .= "======================\n\n";

            $logFile = "{$directory}/log.txt";
            Storage::disk('public')->put($logFile, $logContent);

            // Save DB log
            $bulk_upload = BulkUploadLog::create([
                'user_id'            => auth()->id(),
                'run_id'             => $run_id,
                'run_on'             => now(),
                'module'             => 'Product Transfer',
                'total_record'       => $totalRecords,
                'successfull_record' => $successfulRecords,
                'error_record'       => 0,
                'excel'              => $excelPath,
                'log'                => $logFile,
            ]);

            // Notification
            $this->notification(
                Auth::user()->owner_id,
                null,
                'App/Models/BulkUploadLog',
                $bulk_upload->id,
                null,
                json_encode($request->all()),
                now(),
                Auth::user()->id,
                'Bulk product transfer completed successfully',
                null,
                $logFile,
                1
            );

            return back()->with('toast_success', 'Bulk transfer completed successfully.');

        } catch (\Exception $e) {

            DB::rollBack();
            return back()->with('toast_error', $e->getMessage());
        }
    }

    private function transferProduct(array $data)
    {
        $selectedImeis = $data['imeis'] ?? [];

        $product = Product::findOrFail($data['product_id']);

        if ($product->quantity == 0) {
            throw new \Exception('You cant transfer a product with 0 quantity.');
        }

        if ($product->quantity < $data['quantity']) {
            throw new \Exception('Quantity can’t be greater than stock.');
        }

        // Branch stock
        $branchStock = Stock::where([
            ['branch_id', $data['branch_id']],
            ['product_id', $data['product_id']]
        ])->first();

        if ($branchStock) {

            $branchImeis = $branchStock->imei
                ? explode(',', $branchStock->imei)
                : [];

            $branchStock->update([
                'quantity' => $branchStock->quantity + $data['quantity'],
                'imei'     => implode(',', array_merge($branchImeis, $selectedImeis)),
            ]);

        } else {

            $branchStock = Stock::create([
                'shop_id'        => Auth::user()->owner_id,
                'branch_id'      => $data['branch_id'],
                'category_id'    => $data['category_id'],
                'sub_category_id'=> $data['sub_category_id'],
                'product_id'     => $data['product_id'],
                'quantity'       => $data['quantity'],
                'is_active'      => 1,
                'imei'           => implode(',', $selectedImeis),
            ]);
        }

        // Deduct from main stock
        $mainStock = Stock::where([
            ['shop_id', Auth::user()->owner_id],
            ['branch_id', null],
            ['product_id', $data['product_id']]
        ])->first();

        if ($mainStock) {

            $mainImeis = $mainStock->imei
                ? explode(',', $mainStock->imei)
                : [];

            $mainStock->update([
                'quantity' => $mainStock->quantity - $data['quantity'],
                'imei'     => implode(',', array_diff($mainImeis, $selectedImeis)),
            ]);
        }

        // Update product quantity
        $product->decrement('quantity', $data['quantity']);

        // History
        ProductHistory::create([
            'from'            => Auth::user()->id,
            'to'              => $data['branch_id'],
            'category_id'     => $data['category_id'],
            'sub_category_id' => $data['sub_category_id'],
            'product_id'      => $data['product_id'],
            'quantity'        => $data['quantity'],
            'transfer_on'     => now(),
            'transfer_by'     => Auth::user()->id,
        ]);
    }




}
