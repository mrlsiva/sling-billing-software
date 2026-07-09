<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductTransferImport;
use App\Exports\StockExport;
use App\Models\BulkUploadLog;
use App\Models\StockVariation;
use App\Models\ProductHistory;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Traits\Log;
use DB;

class stockController extends Controller
{
    use Log, Notifications;

    public function index(Request $request)
    {
        $stocks = Stock::where('branch_id', Auth::user()->id)

            // ✅ stock filter
            ->when($request->stock_in == 1, function ($query) {
                $query->where('quantity', '>', 0);
            })

            // search filter
            ->when($request->product, function ($query) use ($request) {
                $search = $request->product;

                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', function ($q1) use ($search) {
                        $q1->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product.category', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product.sub_category', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%{$search}%");
                    });
                });
            })

            ->orderBy('category_id')
            ->orderBy('sub_category_id')
            ->orderBy('product_id')
            ->paginate(10);

        return view('branches.products.index', compact('stocks'));
    }

    public function download(Request $request)
    {
        $query = Stock::where('branch_id', Auth::user()->id)

            // stock filter
            ->when($request->stock_in == 1, function ($query) {
                $query->where('quantity', '>', 0);
            })

            // search filter
            ->when($request->product, function ($query) use ($request) {
                $search = $request->product;

                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', function ($q1) use ($search) {
                        $q1->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product.category', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product.sub_category', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%{$search}%");
                    });
                });
            });

        // ✅ IMPORTANT: eager loading
        $stocks = $query->with([
            'product.metric',
            'product.category',
            'product.sub_category',
            'category',
            'sub_category',
            'variations.size',
            'variations.colour'
        ])
        ->orderBy('category_id')
        ->orderBy('sub_category_id')
        ->orderBy('product_id')
        ->get();

        return Excel::download(new StockExport($stocks), 'stocks_' . now()->format('d-m-Y_h-i A') . '.xlsx');
    }

    public function get_stock_variation($company, Stock $stock)
    {
        $variations = StockVariation::with(['size', 'colour'])
            ->where('stock_id', $stock->id)
            ->get();

        return view('branches.products.variation', compact('stock', 'variations'));
    }

    public function qrcode(Request $request,$company,Product $product)
    {
        //$product = Product::where('id',$id)->first();
        return view('branches.products.qrcode',compact('product'));

    }

    public function barcode(Request $request,$company,$id)
    {
        $product = Product::where('id',$id)->first();
        return view('branches.products.barcode',compact('product'));
    }

    public function transfer(Request $request)
    {

        $stock_categories = Stock::where('branch_id', Auth::user()->id)->select('category_id')->get();

        $categories = Category::whereIn('id',$stock_categories)->where('is_active', 1)->get();

        $branches = User::where([
            ['parent_id', Auth::user()->parent_id],
            ['id', '!=', Auth::user()->id],
            ['is_active', 1],
            ['is_lock', 0],
            ['is_delete', 0]
        ])->get();

        $transfers = ProductHistory::selectRaw('
                MAX(id) as id,
                invoice,
                MAX(`from`) as `from`,
                MAX(`to`) as `to`,
                MAX(transfer_on) as transfer_on,
                MAX(transfer_by) as transfer_by
            ')
            ->where(function ($q) {
                $q->where('from', Auth::user()->id)
                  ->orWhere('to', Auth::user()->id);
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
            ->with(['transfer_from', 'transfer_to']) // product removed (grouped)
            ->orderBy('id', 'Desc')
            ->paginate(10);

        return view('branches.products.transfer', compact('categories', 'branches', 'transfers')
        );
    }


    public function get_sub_category(Request $request)
    {
        $stock_subcategories = Stock::where('branch_id', Auth::user()->id)->select('sub_category_id')->get();
        return $sub_categories = SubCategory::whereIn('id',$stock_subcategories)->where([['user_id',Auth::user()->parent_id],['category_id',$request->id],['is_active',1]])->get();
    }


    public function get_product(Request $request)
    {
        $stock_products = Stock::where('branch_id', Auth::user()->id)->select('product_id')->get();
        return $products = Product::whereIn('id',$stock_products)->where([['user_id',Auth::user()->parent_id],['category_id',$request->category],['sub_category_id',$request->sub_category],['is_active',1]])->get();
    }

    public function get_product_detail(Request $request)
    {
        $product = Stock::with('product.metric')->where([['shop_id', Auth::user()->parent_id],['branch_id', Auth::user()->id],['product_id', $request->product]])->first();

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
    }

    public function get_bill(Request $request,$company,$id)
    {
        $transfer_detail = ProductHistory::where('id',$id)->first();
        $transfer_products = ProductHistory::where('invoice',$transfer_detail->invoice)->get();

         return view('users.inventories.bill',compact('transfer_detail','transfer_products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category'      => 'required',
            'sub_category'  => 'required',
            'product'       => 'required',
            'price'         => 'required',
            'quantity'      => 'required|numeric|min:0',
        ], [
            'category.required'     => 'Category is required.',
            'sub_category.required' => 'Sub Category is required.',
            'product.required'      => 'Product is required.',
            'quantity.required'     => 'Quantity is required.',
            'quantity.numeric'      => 'Quantity must be a number.',
            'quantity.min'          => 'Quantity cannot be negative.',
        ]);

        // If transfer to branch
        if ($request->transfer_to == 1) 
        {
            $request->validate([
                'branch' => 'required',
            ], [
                'branch.required' => 'Branch is required.',
            ]);
        }

        // Selected IMEIs
        $selectedImeis = $request->imeis ?? [];

        $product = Stock::with('product.metric')->where([['shop_id', Auth::user()->parent_id],['branch_id', Auth::user()->id],['product_id', $request->product]])->first();

        //$product = Product::findOrFail($request->product);

        if ($product->quantity == 0) 
        {
            return back()->with('toast_error', 'You cant transfer a product with 0 quantity.');
        }

        if ($product->quantity < $request->quantity) 
        {
            return back()->with('toast_error', 'Quantity can’t be greater than stock.');
        }

        DB::beginTransaction();


        /* ============================================================
        CASE: Branch → HO (transfer_to = 2)
        ============================================================ */
        if ($request->transfer_to == 2) {

            /** ================ FIND OR CREATE HO STOCK ================== **/
            $hoStock = Stock::where([
                ['shop_id', Auth::user()->parent_id],
                ['branch_id', null],
                ['product_id', $request->product]
            ])->first();

            $hoImeis = $hoStock && $hoStock->imei ? explode(',', $hoStock->imei) : [];

            
            // Append transferred IMEI to existing HO
            $updatedHoImeis = array_merge($hoImeis, $selectedImeis);

            $hoStock->update([
                'quantity' => $hoStock->quantity + $request->quantity,
                'imei'     => implode(',', $updatedHoImeis)
            ]);

            /** ================ REDUCE BRANCH (main) STOCK ================== **/
            $mainStock = Stock::where([
                ['shop_id', Auth::user()->parent_id],
                ['branch_id', Auth::user()->id],
                ['product_id', $request->product]
            ])->first();

            if ($mainStock) {
                $mainImeis = $mainStock->imei ? explode(',', $mainStock->imei) : [];

                // Remove IMEIs transferred back to HO
                $remainingImeis = array_diff($mainImeis, $selectedImeis);

                $mainStock->update([
                    'quantity' => $mainStock->quantity - $request->quantity,
                    'imei'     => implode(',', $remainingImeis)
                ]);
            }

            // Product table quantity update
            Product::where('id', $request->product)->update(['quantity' => $product->quantity + $request->quantity]);

            /** ================ VARIATION TRANSFER: Branch(Main) -> HO ================== **/
            // Make sure request->variation_qty exists and is an array
            if (!empty($request->variation_qty) && is_array($request->variation_qty)) {
                foreach ($request->variation_qty as $variationId => $qty) {
                    $qty = (int) $qty;
                    if ($qty > 0) {

                        // Main variation (from branch/main stock)
                        $mainV = StockVariation::find($variationId);
                        if (! $mainV) {
                            // skip if invalid id (or optionally log)
                            continue;
                        }

                        // Find matching variation record under HO stock
                        $hoV = StockVariation::where([
                            ['stock_id', $hoStock->id],
                            ['size_id', $mainV->size_id],
                            ['colour_id', $mainV->colour_id],
                            ['product_id', $request->product],
                        ])->first();

                        if ($hoV) {
                            // Increase HO variation
                            $hoV->update([
                                'quantity' => $hoV->quantity + $qty
                            ]);
                        } else {
                            // Create HO variation
                            StockVariation::create([
                                'stock_id'  => $hoStock->id,
                                'product_id'=> $request->product,
                                'size_id'   => $mainV->size_id,
                                'colour_id' => $mainV->colour_id,
                                'quantity'  => $qty,
                                'price'     => $mainV->price
                            ]);
                        }

                        // Reduce quantity from main variation
                        // Guard against negative values
                        $newMainQty = max(0, $mainV->quantity - $qty);
                        $mainV->update([
                            'quantity' => $newMainQty
                        ]);
                    }
                }
            }

            else
            {
                $product = Product::where('id', $request->product)->first();
                $branchStockVariation = StockVariation::where([['stock_id',$mainStock->id],['product_id',$request->product],['size_id',null],['colour_id',null]])->first();
                if($branchStockVariation)
                {
                    $branchStockVariation->update([
                        'quantity' => $branchStockVariation->quantity - $request->quantity
                    ]);
                }

                $hoStockTransferVariation = StockVariation::where([['stock_id',$hoStock->id],['product_id',$request->product],['size_id',null],['colour_id',null]])->first();
                if($hoStockTransferVariation)
                {
                    $hoStockTransferVariation->update([
                        'quantity' => $hoStockTransferVariation->quantity + $request->quantity
                    ]);
                }
            }

            $lastInvoice = ProductHistory::where('shop_id',Auth::user()->parent_id)->lockForUpdate()->max('invoice');

            $next = $lastInvoice ? ((int) ltrim($lastInvoice, '0') + 1) : 1;

            $invoice = str_pad($next, 5, '0', STR_PAD_LEFT);

            $transfer = ProductHistory::create([
                'shop_id' => Auth::user()->parent_id,
                'invoice' => $invoice,
                'from' => Auth::user()->id,
                'to'   => Auth::user()->parent_id,
                'category_id'    => $request->category,
                'sub_category_id'=> $request->sub_category,
                'product_id'     => $request->product,
                'quantity'       => $request->quantity,
                'price'          => $request->price,
                'transfer_on'    => now(),
                'transfer_by'    => Auth::user()->id,
            ]);

            // Logs
            $this->addToLog($this->unique(),Auth::user()->id,'Product Transfer','App/Models/ProductHistory',
                'product_histories',$transfer->id,'Create',null,$request,'Success',
                'Product Transfered Successfully');

            // Notification
            $this->notification(Auth::user()->owner_id, null,'App/Models/ProductHistory',
                $transfer->id, null, json_encode($request->all()), now(),
                Auth::user()->id, $transfer->product->name.' has been successfully transfered to HO '.$transfer->transfer_to->name,
                null, null, 8
            );
        }


        /* ============================================================
        CASE 2: Branch → Other BRANCH (transfer_to = 1)
        ============================================================ */
        else {

            /** ================= Other BRANCH STOCK UPDATE ================== **/
            $branchStock = Stock::where([
                ['shop_id', Auth::user()->parent_id],
                ['branch_id', $request->branch],
                ['product_id', $request->product]
            ])->first();

            $branchImeis = $branchStock && $branchStock->imei ? explode(',', $branchStock->imei) : [];

            $updatedBranchImeis = array_merge($branchImeis, $selectedImeis);

            if ($branchStock) 
            {
                $branchStock->update([
                    'quantity' => $branchStock->quantity + $request->quantity,
                    'imei'     => implode(',', $updatedBranchImeis)
                ]);
            } 
            else 
            {
                $branchStock = Stock::create([
                    'shop_id'        => Auth::user()->parent_id,
                    'branch_id'      => $request->branch,
                    'category_id'    => $request->category,
                    'sub_category_id'=> $request->sub_category,
                    'product_id'     => $request->product,
                    'quantity'       => $request->quantity,
                    'is_active'      => 1,
                    'imei'           => implode(',', $selectedImeis)
                ]);
            }

            /** ================= Branch STOCK REDUCE ================== **/
            $mainStock = Stock::where([
                ['shop_id', Auth::user()->parent_id],
                ['branch_id', Auth::user()->id],
                ['product_id', $request->product]
            ])->first();

            if ($mainStock) {
                $mainImeis = $mainStock->imei ? explode(',', $mainStock->imei) : [];

                $remainingImeis = array_diff($mainImeis, $selectedImeis);

                $mainStock->update([
                    'quantity' => $mainStock->quantity - $request->quantity,
                    'imei'     => implode(',', $remainingImeis)
                ]);
            }

            // Product stock deduct
            Product::where('id', $request->product)->update(['quantity' => $product->quantity - $request->quantity]);

            /** ================= VARIATION TRANSFER ================== **/
            if($request->variation_qty != null)
            {
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

                            // 🔥 FIX: Reduce variation quantity from main stock
                            $mainV->update([
                                'quantity' => $mainV->quantity - $qty
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

                        // 🔥 FIX: Reduce variation quantity from main stock
                        $mainV->update([
                            'quantity' => $mainV->quantity - $qty
                        ]);
                    }
                }
            }
            else
            {
                $product = Product::where('id', $request->product)->first();
                $branchStockVariation = StockVariation::where([['stock_id',$mainStock->id],['product_id',$request->product],['size_id',null],['colour_id',null]])->first();
                if($branchStockVariation)
                {
                    $branchStockVariation->update([
                        'quantity' => $branchStockVariation->quantity - $request->quantity
                    ]);
                }

                $branchTransferStockVariation = StockVariation::where([['stock_id',$branchStock->id],['product_id',$request->product],['size_id',null],['colour_id',null]])->first();
                if($branchTransferStockVariation)
                {
                    $branchTransferStockVariation->update([
                        'quantity' => $branchTransferStockVariation->quantity + $request->quantity
                    ]);
                }
                else
                {
                    StockVariation::create([
                        'stock_id'  => $branchStock->id,
                        'product_id'=> $request->product,
                        'size_id'   => null,
                        'colour_id' => null,
                        'quantity'  => $request->quantity,
                        'price'     => $product->price
                    ]);
                }


            }

            $lastInvoice = ProductHistory::where('shop_id',Auth::user()->parent_id)->lockForUpdate()->max('invoice');

            $next = $lastInvoice ? ((int) ltrim($lastInvoice, '0') + 1) : 1;

            $invoice = str_pad($next, 5, '0', STR_PAD_LEFT);


            $transfer = ProductHistory::create([
                'shop_id'        => Auth::user()->parent_id,
                'invoice' => $invoice,
                'from'           => Auth::user()->id,
                'to'             => $request->branch,
                'category_id'    => $request->category,
                'sub_category_id'=> $request->sub_category,
                'product_id'     => $request->product,
                'quantity'       => $request->quantity,
                'price'          => $request->price,
                'transfer_on'    => now(),
                'transfer_by'    => Auth::user()->id,
            ]);

            // Notifications
            $this->notification(Auth::user()->owner_id, null,'App/Models/ProductHistory',
                $transfer->id, null, json_encode($request->all()), now(),
                Auth::user()->id, $transfer->product->name.' has been successfully transfered to branch '.$transfer->transfer_to->name,
                null, null, 8);

            $this->notification(null, $request->branch,'App/Models/ProductHistory',
                $transfer->id, null, json_encode($request->all()), now(),
                Auth::user()->id, $transfer->product->name.' has been successfully transfered to your branch '.$transfer->transfer_to->name,
                null, null, 8);
        }

        DB::commit();

        return redirect()->back()->with('toast_success', 'Product transferred successfully.');
    }

    public function bulk(Request $request)
    {
        // Allow extra time for large Excel files
        set_time_limit(300);

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

            $import = new ProductTransferImport(auth()->user()->parent_id);
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
                    Auth::user()->parent_id,
                    Auth::user()->id,
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

                return back()->with('toast_error', 'Failed to import excel. See errors below.')->with('bulk_errors', $errors);

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

             $lastInvoice = ProductHistory::where('shop_id',Auth::user()->parent_id)->lockForUpdate()->max('invoice');

            $next = $lastInvoice ? ((int) ltrim($lastInvoice, '0') + 1) : 1;

            $invoice = str_pad($next, 5, '0', STR_PAD_LEFT);


            // AFTER validation success
            foreach ($import->validRows as $row) {

                $this->transferProduct([
                    'branch_id'       => $request->branch,
                    'category_id'     => $row['category_id'],
                    'sub_category_id' => $row['sub_category_id'],
                    'product_id'      => $row['product_id'],
                    'quantity'        => $row['quantity'],
                    'price'           => $row['price'],
                    'imeis'           => $row['imeis'],
                    'invoice'         => $invoice,
                    'variation_id'    => $row['variation_id'],
                    'size_id'         => $row['size_id'],
                    'colour_id'       => $row['colour_id'],
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
                Auth::user()->parent_id,
                Auth::user()->id,
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

        if($data['branch_id'] != 0)
        {
            // Branch stock
            $branchStock = Stock::where([
                ['branch_id', $data['branch_id']],
                ['product_id', $data['product_id']]
            ])->first();
        }
        else
        {
            // Branch stock
            $branchStock = Stock::where([
                ['shop_id', Auth::user()->parent_id],
                ['branch_id', null],
                ['product_id', $data['product_id']]
            ])->first();
        }

        if ($branchStock) {

            $branchImeis = $branchStock->imei
                ? explode(',', $branchStock->imei)
                : [];

            $branchStock->update([
                'quantity' => $branchStock->quantity + $data['quantity'],
                'imei'     => implode(',', array_merge($branchImeis, $selectedImeis)),
            ]);

            if (empty($data['variation_id'])) 
            {
                $branchStockVariation = StockVariation::where([
                    ['stock_id', $branchStock->id],
                    ['product_id', $data['product_id']]
                ])->first();


                if($branchStockVariation)
                {
                    $branchStockVariation->update([
                        'quantity' => $branchStockVariation->quantity + $data['quantity'],
                    ]);
                }
                else
                {
                    StockVariation::create([
                        'stock_id'   => $branchStock->id,
                        'product_id' => $data['product_id'],
                        'quantity'   => $data['quantity'],
                        'price'      => $product->price,
                    ]);
                }
            }

        } else {

            if($data['branch_id'] != 0)
            {
                $branchStock = Stock::create([
                    'shop_id'        => Auth::user()->parent_id,
                    'branch_id'      => $data['branch_id'],
                    'category_id'    => $data['category_id'],
                    'sub_category_id'=> $data['sub_category_id'],
                    'product_id'     => $data['product_id'],
                    'quantity'       => $data['quantity'],
                    'is_active'      => 1,
                    'imei'           => implode(',', $selectedImeis),
                ]);
            }
            elseif($data['branch_id'] == 0)
            {
                $branchStock = Stock::create([
                    'shop_id'        => Auth::user()->parent_id,
                    'branch_id'      => null,
                    'category_id'    => $data['category_id'],
                    'sub_category_id'=> $data['sub_category_id'],
                    'product_id'     => $data['product_id'],
                    'quantity'       => $data['quantity'],
                    'is_active'      => 1,
                    'imei'           => implode(',', $selectedImeis),
                ]);
            }

            if (empty($data['variation_id'])) 
            {

                StockVariation::create([
                    'stock_id'   => $branchStock->id,
                    'product_id' => $data['product_id'],
                    'quantity'   => $data['quantity'],
                    'price'      => $product->price,
                ]);
            }
        }

        // Deduct from main stock
        $mainStock = Stock::where([
            ['shop_id', Auth::user()->parent_id],
            ['branch_id', Auth::user()->id],
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

            if (empty($data['variation_id'])) 
            {
                $mainStockVariation = StockVariation::where([
                    ['stock_id', $mainStock->id],
                    ['product_id', $data['product_id']]
                ])->first();

                $mainStockVariation->update([
                    'quantity' => $mainStockVariation->quantity - $data['quantity'],
                ]);
            }
        }

        // Update product quantity
        $product->decrement('quantity', $data['quantity']);

        if($data['branch_id'] == 0)
        {
            $to = Auth::user()->parent_id;
        }
        else
        {
            $to = $data['branch_id'];
        }

        // History
        ProductHistory::create([
            'shop_id'         => Auth::user()->parent_id,
            'invoice'         => $data['invoice'],
            'from'            => Auth::user()->id,
            'to'              => $to,
            'category_id'     => $data['category_id'],
            'sub_category_id' => $data['sub_category_id'],
            'product_id'      => $data['product_id'],
            'quantity'        => $data['quantity'],
            'price'        => $data['price'],
            'transfer_on'     => now(),
            'transfer_by'     => Auth::user()->id,
        ]);

        // ---------------------------------------------
        // VARIATION TRANSFER (IMPORTANT)
        // ---------------------------------------------

        if (!empty($data['variation_id'])) {

            $mainV = StockVariation::find($data['variation_id']);

            if (!$mainV) {
                throw new \Exception('Main variation not found');
            }

            if ($mainV->quantity < $data['quantity']) {
                throw new \Exception('Not enough variation stock');
            }

            // Deduct from main
            $mainV->decrement('quantity', $data['quantity']);

            // Find branch variation
            $branchV = StockVariation::where([
                ['stock_id', $branchStock->id],
                ['product_id', $data['product_id']],
                ['size_id', $data['size_id']],
                ['colour_id', $data['colour_id']],
            ])->first();

            if ($branchV) {
                $branchV->increment('quantity', $data['quantity']);
            } else {
                StockVariation::create([
                    'stock_id'  => $branchStock->id,
                    'product_id'=> $data['product_id'],
                    'size_id'   => $data['size_id'],
                    'colour_id' => $data['colour_id'],
                    'quantity'  => $data['quantity'],
                    'price'     => $mainV->price,
                ]);
            }
        }

    }

    

}
