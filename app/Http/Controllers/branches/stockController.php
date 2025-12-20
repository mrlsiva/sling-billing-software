<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
        ->when(request('product'), function ($query) {
            $search = request('product');
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
        })->orderBy('category_id')->orderBy('sub_category_id')->orderBy('product_id')->paginate(10);

        return view('branches.products.index',compact('stocks'));
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

        $categories = Category::where([['user_id',Auth::user()->parent_id],['is_active',1]])->get();
        $branches = User::where([['parent_id',Auth::user()->parent_id],['id','!=',Auth::user()->id],['is_active',1],['is_lock',0],['is_delete',0]])->get();

        $transfers = ProductHistory::where(function ($q) {
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
                $q->where('to', $branch)->orWhere('from', $branch);
            });
        })
        ->with(['product.metric', 'transfer_from', 'transfer_to'])
        ->orderBy('transfer_on', 'desc')
        ->paginate(10);

        return view('branches.products.transfer',compact('categories','branches','transfers'));
    }

    public function get_sub_category(Request $request)
    {
        return $sub_categories = SubCategory::where([['user_id',Auth::user()->parent_id],['category_id',$request->id],['is_active',1]])->get();
    }


    public function get_product(Request $request)
    {
        return $products = Product::where([['user_id',Auth::user()->parent_id],['category_id',$request->category],['sub_category_id',$request->sub_category],['is_active',1]])->get();
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

    public function store(Request $request)
    {
        $request->validate([
            'category'      => 'required',
            'sub_category'  => 'required',
            'product'       => 'required',
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

        $product = Product::findOrFail($request->product);

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

            $transfer = ProductHistory::create([
                'from' => Auth::user()->id,
                'to'   => Auth::user()->parent_id,
                'category_id'    => $request->category,
                'sub_category_id'=> $request->sub_category,
                'product_id'     => $request->product,
                'quantity'       => $request->quantity,
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


            $transfer = ProductHistory::create([
                'from'           => Auth::user()->id,
                'to'             => $request->branch,
                'category_id'    => $request->category,
                'sub_category_id'=> $request->sub_category,
                'product_id'     => $request->product,
                'quantity'       => $request->quantity,
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

    

}
