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

class inventoryController extends Controller
{
    use Log, Notifications, ResponseHelper;

    // HO stock list — supports ?shop=&branch=&product=&stock_in=1
    public function stock(Request $request)
    {
        $shop   = $request->shop ?? Auth::user()->owner_id;
        $branch = $request->branch ?? 0;

        $query = Stock::with(['product.category', 'product.sub_category', 'product.metric'])
            ->where('shop_id', $shop);

        if ($branch != 0) {
            $query->where('branch_id', $branch);
        } else {
            $query->whereNull('branch_id');
        }

        $query->when($request->product, function ($q) use ($request) {
            $search = $request->product;
            $q->where(function ($q2) use ($search) {
                $q2->whereHas('product', fn($q3) => $q3->where('name', 'like', "%{$search}%"))
                   ->orWhereHas('product.category', fn($q3) => $q3->where('name', 'like', "%{$search}%"))
                   ->orWhereHas('product.sub_category', fn($q3) => $q3->where('name', 'like', "%{$search}%"));
            });
        });

        $query->when($request->stock_in == 1, fn($q) => $q->where('quantity', '>', 0));

        $stocks = $query->orderBy('category_id')
                        ->orderBy('sub_category_id')
                        ->orderBy('product_id')
                        ->paginate(10);

        $branches = User::where([
            ['parent_id', Auth::user()->owner_id],
            ['is_active', 1],
            ['is_lock', 0],
            ['is_delete', 0],
        ])->get();

        $categories = Category::where([
            ['user_id', Auth::user()->owner_id],
            ['is_active', 1],
        ])->get();

        return $this->successResponse(
            compact('stocks', 'branches', 'categories'),
            200,
            'Stock retrieved successfully.'
        );
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

    // HO transfer list — supports ?product=&branch=
    public function transfer(Request $request)
    {
        $categories = Category::where([
            ['user_id', Auth::user()->owner_id],
            ['is_active', 1],
        ])->get();

        $branches = User::where([
            ['parent_id', Auth::user()->owner_id],
            ['is_active', 1],
            ['is_lock', 0],
            ['is_delete', 0],
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
        $transfer_detail   = ProductHistory::find($id);

        if (!$transfer_detail) {
            return $this->errorResponse([], 404, 'Transfer not found.');
        }

        $transfer_products = ProductHistory::where([
            ['shop_id', Auth::user()->owner_id],
            ['invoice', $transfer_detail->invoice],
        ])->get();

        return $this->successResponse(
            ['transfer_detail' => $transfer_detail, 'transfer_products' => $transfer_products],
            200,
            'Transfer bill retrieved successfully.'
        );
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

    public function get_product_detail(Request $request)
    {
        $stock = Stock::with('product.metric')
            ->where([
                ['shop_id', Auth::user()->owner_id],
                ['branch_id', null],
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

    // HO → Branch transfer
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch'       => 'required',
            'category'     => 'required',
            'sub_category' => 'required',
            'product'      => 'required',
            'quantity'     => 'required|numeric|min:0',
        ], [
            'branch.required'       => 'Branch is required.',
            'category.required'     => 'Category is required.',
            'sub_category.required' => 'Sub Category is required.',
            'product.required'      => 'Product is required.',
            'quantity.required'     => 'Quantity is required.',
            'quantity.numeric'      => 'Quantity must be a number.',
            'quantity.min'          => 'Quantity cannot be negative.',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        $selectedImeis = $request->imeis ?? [];
        $product       = Product::findOrFail($request->product);

        if ($product->quantity == 0) {
            return $this->errorResponse([], 422, '"' . $product->name . '" has 0 quantity. Cannot transfer.');
        }

        if ($product->quantity < $request->quantity) {
            return $this->errorResponse([], 422, 'Quantity cannot be greater than available stock.');
        }

        DB::beginTransaction();

        // Update or create branch stock
        $branchStock = Stock::where([['branch_id', $request->branch], ['product_id', $request->product]])->first();

        if ($branchStock) {
            $branchImeis        = $branchStock->imei ? explode(',', $branchStock->imei) : [];
            $updatedBranchImeis = array_merge($branchImeis, $selectedImeis);

            $branchStock->update([
                'shop_id'         => Auth::user()->owner_id,
                'branch_id'       => $request->branch,
                'category_id'     => $request->category,
                'sub_category_id' => $request->sub_category,
                'product_id'      => $request->product,
                'quantity'        => $branchStock->quantity + $request->quantity,
                'is_active'       => 1,
                'imei'            => implode(',', $updatedBranchImeis),
            ]);

            $this->addToLog($this->unique(), Auth::user()->id, 'Stock Updated', 'App/Models/Stock', 'stocks', $branchStock->id, 'Update', null, $request, 'Success', 'Stock Updated for this product');
        } else {
            $branchStock = Stock::create([
                'shop_id'         => Auth::user()->owner_id,
                'branch_id'       => $request->branch,
                'category_id'     => $request->category,
                'sub_category_id' => $request->sub_category,
                'product_id'      => $request->product,
                'quantity'        => $request->quantity,
                'is_active'       => 1,
                'imei'            => implode(',', $selectedImeis),
            ]);

            $this->addToLog($this->unique(), Auth::user()->id, 'Stock Added', 'App/Models/Stock', 'stocks', $branchStock->id, 'Insert', null, $request, 'Success', 'Stock Added for this product');
        }

        // Deduct from main stock
        $mainStock = Stock::where([
            ['shop_id', Auth::user()->owner_id],
            ['branch_id', null],
            ['product_id', $request->product],
        ])->first();

        if ($mainStock) {
            $mainImeis      = $mainStock->imei ? explode(',', $mainStock->imei) : [];
            $remainingImeis = array_diff($mainImeis, $selectedImeis);

            $mainStock->update([
                'quantity' => $mainStock->quantity - $request->quantity,
                'imei'     => implode(',', $remainingImeis),
            ]);
        }

        Product::where('id', $request->product)->update(['quantity' => $product->quantity - $request->quantity]);

        $this->addToLog($this->unique(), Auth::user()->id, 'Quantity Updated', 'App/Models/Product', 'products', $product->id, 'Update', null, $request, 'Success', 'Quantity Updated for this product');

        // Generate invoice number
        $lastInvoice = ProductHistory::where('shop_id', Auth::user()->owner_id)->lockForUpdate()->max('invoice');
        $next        = $lastInvoice ? ((int) ltrim($lastInvoice, '0') + 1) : 1;
        $invoice     = str_pad($next, 5, '0', STR_PAD_LEFT);

        $transfer = ProductHistory::create([
            'shop_id'         => Auth::user()->owner_id,
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

        // Handle variation transfer
        if ($request->variation_qty != null) {
            foreach ($request->variation_qty as $variationId => $qty) {
                if ($qty > 0) {
                    $mainV = StockVariation::find($variationId);
                    $mainV->update(['quantity' => $mainV->quantity - $qty]);

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
                }
            }
        } else {
            $mainV = StockVariation::where([['stock_id', $mainStock->id], ['product_id', $request->product]])->first();

            if ($mainV) {
                $mainV->update(['quantity' => $mainV->quantity - $request->quantity]);

                $branchV = StockVariation::where([
                    ['stock_id', $branchStock->id],
                    ['size_id', $mainV->size_id],
                    ['colour_id', $mainV->colour_id],
                    ['product_id', $request->product],
                ])->first();

                if ($branchV) {
                    $branchV->update(['quantity' => $branchV->quantity + $request->quantity]);
                } else {
                    StockVariation::create([
                        'stock_id'   => $branchStock->id,
                        'product_id' => $request->product,
                        'size_id'    => $mainV->size_id,
                        'colour_id'  => $mainV->colour_id,
                        'quantity'   => $request->quantity,
                        'price'      => $mainV->price,
                    ]);
                }
            }
        }

        $this->addToLog($this->unique(), Auth::user()->id, 'Product Transfer', 'App/Models/ProductHistory', 'product_histories', $transfer->id, 'Create', null, $request, 'Success', 'Product Transferred Successfully');
        $this->notification(Auth::user()->owner_id, null, 'App/Models/ProductHistory', $transfer->id, null, json_encode($request->all()), now(), Auth::user()->id, $transfer->product->name . ' has been successfully transferred to branch ' . $transfer->transfer_to->name, null, null, 8);
        $this->notification(null, $request->branch, 'App/Models/ProductHistory', $transfer->id, null, json_encode($request->all()), now(), Auth::user()->id, $transfer->product->name . ' has been successfully transferred to your branch ' . $transfer->transfer_to->name, null, null, 8);

        DB::commit();

        return $this->successResponse(
            ['transfer_id' => $transfer->id, 'invoice' => $invoice],
            200,
            'Product transferred successfully.'
        );
    }
}