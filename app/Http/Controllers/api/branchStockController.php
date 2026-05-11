<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\ProductHistory;
use App\Models\StockVariation;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Traits\Log;
use DB;

class branchStockController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function index(Request $request)
    {
        $stocks = Stock::with(['product.category', 'product.sub_category', 'product.metric'])
            ->where('branch_id', Auth::user()->id)
            ->when($request->stock_in == 1, fn($q) => $q->where('quantity', '>', 0))
            ->when($request->product, function ($q) use ($request) {
                $search = $request->product;
                $q->where(function ($q2) use ($search) {
                    $q2->whereHas('product', fn($q3) => $q3->where('name', 'like', "%{$search}%"))
                       ->orWhereHas('product.category', fn($q3) => $q3->where('name', 'like', "%{$search}%"))
                       ->orWhereHas('product.sub_category', fn($q3) => $q3->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('category_id')
            ->orderBy('sub_category_id')
            ->orderBy('product_id')
            ->paginate(10);

        return $this->successResponse($stocks, 200, 'Branch stock retrieved successfully.');
    }

    public function get_stock_variation(Request $request, $stock)
    {
        $stock = Stock::find($stock);

        if (!$stock) {
            return $this->errorResponse([], 404, 'Stock not found.');
        }

        $variations = StockVariation::with(['size', 'colour'])
            ->where('stock_id', $stock->id)
            ->get();

        return $this->successResponse(
            ['stock' => $stock, 'variations' => $variations],
            200,
            'Stock variations retrieved successfully.'
        );
    }

    public function transfer_list(Request $request)
    {
        $stock_categories = Stock::where('branch_id', Auth::user()->id)->select('category_id')->get();
        $categories       = Category::whereIn('id', $stock_categories)->where('is_active', 1)->get();

        $branches = User::where([
            ['parent_id', Auth::user()->parent_id],
            ['id', '!=', Auth::user()->id],
            ['is_active', 1],
            ['is_lock', 0],
            ['is_delete', 0],
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
            ->when($request->filled('product'), function ($q) use ($request) {
                $q->whereHas('product', fn($q2) => $q2->where('name', 'like', '%' . $request->product . '%'));
            })
            ->when($request->filled('branch'), function ($q) use ($request) {
                $q->where(fn($q2) => $q2->where('to', $request->branch)->orWhere('from', $request->branch));
            })
            ->groupBy('invoice')
            ->with(['transfer_from', 'transfer_to'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return $this->successResponse(
            compact('transfers', 'categories', 'branches'),
            200,
            'Transfer list retrieved successfully.'
        );
    }

    public function get_transfer_bill(Request $request, $id)
    {
        $transfer_detail = ProductHistory::find($id);

        if (!$transfer_detail) {
            return $this->errorResponse([], 404, 'Transfer not found.');
        }

        $transfer_products = ProductHistory::where('invoice', $transfer_detail->invoice)->get();

        return $this->successResponse(
            ['transfer_detail' => $transfer_detail, 'transfer_products' => $transfer_products],
            200,
            'Transfer bill retrieved successfully.'
        );
    }

    public function get_sub_category(Request $request)
    {
        $stock_subcategories = Stock::where('branch_id', Auth::user()->id)->select('sub_category_id')->get();

        $sub_categories = SubCategory::whereIn('id', $stock_subcategories)
            ->where([['user_id', Auth::user()->parent_id], ['category_id', $request->id], ['is_active', 1]])
            ->get();

        return $this->successResponse($sub_categories, 200, 'Sub categories retrieved successfully.');
    }

    public function get_product(Request $request)
    {
        $stock_products = Stock::where('branch_id', Auth::user()->id)->select('product_id')->get();

        $products = Product::whereIn('id', $stock_products)
            ->where([
                ['user_id', Auth::user()->parent_id],
                ['category_id', $request->category],
                ['sub_category_id', $request->sub_category],
                ['is_active', 1],
            ])->get();

        return $this->successResponse($products, 200, 'Products retrieved successfully.');
    }

    public function get_product_detail(Request $request)
    {
        $stock = Stock::with('product.metric')
            ->where([
                ['shop_id', Auth::user()->parent_id],
                ['branch_id', Auth::user()->id],
                ['product_id', $request->product],
            ])->first();

        if (!$stock) {
            return $this->errorResponse([], 404, 'Product stock not found.');
        }

        $imeis = [];
        if (!empty($stock->imei)) {
            $imeis = array_values(array_filter(explode(',', $stock->imei), fn($i) => trim($i) !== ''));
        }

        $variations = StockVariation::with(['size', 'colour'])
            ->where('stock_id', $stock->id)
            ->get();

        return $this->successResponse([
            'product'    => $stock,
            'quantity'   => $stock->quantity,
            'imeis'      => $imeis,
            'variations' => $variations,
        ], 200, 'Product detail retrieved successfully.');
    }

    // Branch → HO or Branch → Branch transfer
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category'    => 'required',
            'sub_category'=> 'required',
            'product'     => 'required',
            'quantity'    => 'required|numeric|min:0',
            'transfer_to' => 'required|in:1,2', // 1=other branch, 2=HO
        ], [
            'category.required'     => 'Category is required.',
            'sub_category.required' => 'Sub Category is required.',
            'product.required'      => 'Product is required.',
            'quantity.required'     => 'Quantity is required.',
            'quantity.numeric'      => 'Quantity must be a number.',
            'quantity.min'          => 'Quantity cannot be negative.',
            'transfer_to.required'  => 'Transfer destination is required.',
            'transfer_to.in'        => 'Transfer destination must be 1 (branch) or 2 (HO).',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        if ($request->transfer_to == 1) {
            $branchValidator = Validator::make($request->all(), ['branch' => 'required'], ['branch.required' => 'Branch is required.']);
            if ($branchValidator->fails()) {
                return $this->validationFailed($branchValidator->errors(), 'Validation failed.');
            }
        }

        $selectedImeis = $request->imeis ?? [];
        $product       = Product::findOrFail($request->product);

        if ($product->quantity == 0) {
            return $this->errorResponse([], 422, 'Cannot transfer a product with 0 quantity.');
        }

        if ($product->quantity < $request->quantity) {
            return $this->errorResponse([], 422, 'Quantity cannot be greater than available stock.');
        }

        DB::beginTransaction();

        $lastInvoice = ProductHistory::where('shop_id', Auth::user()->parent_id)->lockForUpdate()->max('invoice');
        $next        = $lastInvoice ? ((int) ltrim($lastInvoice, '0') + 1) : 1;
        $invoice     = str_pad($next, 5, '0', STR_PAD_LEFT);

        $transfer = null;

        // Case 1: Branch → HO
        if ($request->transfer_to == 2) {

            $hoStock   = Stock::where([['shop_id', Auth::user()->parent_id], ['branch_id', null], ['product_id', $request->product]])->first();
            $hoImeis   = $hoStock && $hoStock->imei ? explode(',', $hoStock->imei) : [];
            $hoStock->update([
                'quantity' => $hoStock->quantity + $request->quantity,
                'imei'     => implode(',', array_merge($hoImeis, $selectedImeis)),
            ]);

            $mainStock = Stock::where([['shop_id', Auth::user()->parent_id], ['branch_id', Auth::user()->id], ['product_id', $request->product]])->first();
            if ($mainStock) {
                $mainImeis = $mainStock->imei ? explode(',', $mainStock->imei) : [];
                $mainStock->update([
                    'quantity' => $mainStock->quantity - $request->quantity,
                    'imei'     => implode(',', array_diff($mainImeis, $selectedImeis)),
                ]);
            }

            Product::where('id', $request->product)->update(['quantity' => $product->quantity + $request->quantity]);

            if (!empty($request->variation_qty) && is_array($request->variation_qty)) {
                foreach ($request->variation_qty as $variationId => $qty) {
                    $qty = (int) $qty;
                    if ($qty > 0) {
                        $mainV = StockVariation::find($variationId);
                        if (!$mainV) continue;

                        $hoV = StockVariation::where([
                            ['stock_id', $hoStock->id],
                            ['size_id', $mainV->size_id],
                            ['colour_id', $mainV->colour_id],
                            ['product_id', $request->product],
                        ])->first();

                        if ($hoV) {
                            $hoV->update(['quantity' => $hoV->quantity + $qty]);
                        } else {
                            StockVariation::create([
                                'stock_id'   => $hoStock->id,
                                'product_id' => $request->product,
                                'size_id'    => $mainV->size_id,
                                'colour_id'  => $mainV->colour_id,
                                'quantity'   => $qty,
                                'price'      => $mainV->price,
                            ]);
                        }

                        $mainV->update(['quantity' => max(0, $mainV->quantity - $qty)]);
                    }
                }
            }

            $transfer = ProductHistory::create([
                'shop_id'         => Auth::user()->parent_id,
                'invoice'         => $invoice,
                'from'            => Auth::user()->id,
                'to'              => Auth::user()->parent_id,
                'category_id'     => $request->category,
                'sub_category_id' => $request->sub_category,
                'product_id'      => $request->product,
                'quantity'        => $request->quantity,
                'transfer_on'     => now(),
                'transfer_by'     => Auth::user()->id,
            ]);

            $this->notification(Auth::user()->owner_id, null, 'App/Models/ProductHistory', $transfer->id, null, json_encode($request->all()), now(), Auth::user()->id, $transfer->product->name . ' has been successfully transferred to HO ' . $transfer->transfer_to->name, null, null, 8);
        }

        // Case 2: Branch → Other Branch
        else {

            $branchStock = Stock::where([['shop_id', Auth::user()->parent_id], ['branch_id', $request->branch], ['product_id', $request->product]])->first();
            $branchImeis = $branchStock && $branchStock->imei ? explode(',', $branchStock->imei) : [];

            if ($branchStock) {
                $branchStock->update([
                    'quantity' => $branchStock->quantity + $request->quantity,
                    'imei'     => implode(',', array_merge($branchImeis, $selectedImeis)),
                ]);
            } else {
                $branchStock = Stock::create([
                    'shop_id'         => Auth::user()->parent_id,
                    'branch_id'       => $request->branch,
                    'category_id'     => $request->category,
                    'sub_category_id' => $request->sub_category,
                    'product_id'      => $request->product,
                    'quantity'        => $request->quantity,
                    'is_active'       => 1,
                    'imei'            => implode(',', $selectedImeis),
                ]);
            }

            $mainStock = Stock::where([['shop_id', Auth::user()->parent_id], ['branch_id', Auth::user()->id], ['product_id', $request->product]])->first();
            if ($mainStock) {
                $mainImeis = $mainStock->imei ? explode(',', $mainStock->imei) : [];
                $mainStock->update([
                    'quantity' => $mainStock->quantity - $request->quantity,
                    'imei'     => implode(',', array_diff($mainImeis, $selectedImeis)),
                ]);
            }

            Product::where('id', $request->product)->update(['quantity' => $product->quantity - $request->quantity]);

            if ($request->variation_qty != null) {
                foreach ($request->variation_qty as $variationId => $qty) {
                    if ($qty > 0) {
                        $mainV = StockVariation::find($variationId);

                        $branchV = StockVariation::where([
                            ['stock_id', $branchStock->id],
                            ['size_id', $mainV->size_id],
                            ['colour_id', $mainV->colour_id],
                            ['product_id', $request->product],
                        ])->first();

                        if ($branchV) {
                            $branchV->update(['quantity' => $branchV->quantity + $qty]);
                        } else {
                            StockVariation::create([
                                'stock_id'   => $branchStock->id,
                                'product_id' => $request->product,
                                'size_id'    => $mainV->size_id,
                                'colour_id'  => $mainV->colour_id,
                                'quantity'   => $qty,
                                'price'      => $mainV->price,
                            ]);
                        }

                        $mainV->update(['quantity' => $mainV->quantity - $qty]);
                    }
                }
            }

            $transfer = ProductHistory::create([
                'shop_id'         => Auth::user()->parent_id,
                'invoice'         => $invoice,
                'from'            => Auth::user()->id,
                'to'              => $request->branch,
                'category_id'     => $request->category,
                'sub_category_id' => $request->sub_category,
                'product_id'      => $request->product,
                'quantity'        => $request->quantity,
                'transfer_on'     => now(),
                'transfer_by'     => Auth::user()->id,
            ]);

            $this->notification(Auth::user()->owner_id, null, 'App/Models/ProductHistory', $transfer->id, null, json_encode($request->all()), now(), Auth::user()->id, $transfer->product->name . ' has been successfully transferred to branch ' . $transfer->transfer_to->name, null, null, 8);
            $this->notification(null, $request->branch, 'App/Models/ProductHistory', $transfer->id, null, json_encode($request->all()), now(), Auth::user()->id, $transfer->product->name . ' has been successfully transferred to your branch ' . $transfer->transfer_to->name, null, null, 8);
        }

        $this->addToLog($this->unique(), Auth::user()->id, 'Product Transfer', 'App/Models/ProductHistory', 'product_histories', $transfer->id, 'Create', null, $request, 'Success', 'Product Transferred Successfully');

        DB::commit();

        return $this->successResponse(
            ['transfer_id' => $transfer->id, 'invoice' => $invoice],
            200,
            'Product transferred successfully.'
        );
    }
}