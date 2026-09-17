<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\QueueStock;
use App\Models\Category;
use App\Models\Stock;
use App\Models\User;

class productController extends Controller
{
    use ResponseHelper;

    public function categories(Request $request, $company)
    {
        $user = User::where('slug_name', $company)->first();

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $userId = $user->role_id == 2 ? $user->owner_id : $user->parent_id;

        $categories = Category::with(['sub_categories'])
            ->where([
                ['user_id', $userId],
                ['is_active', 1],
            ])
            ->when($request->name, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->name . '%');
            })->orderBy('id', 'desc')->get();

        foreach ($categories as $category) {
            $category->image = $category->image
                ? asset('storage/' . $category->image)
                : asset('no-image-icon.svg');
        }

        return $this->successResponse($categories, 200, 'Category retrieved successfully.');
    }

    
    public function sub_categories(Request $request, $company)
    {
        $user = User::where('slug_name', $company)->first();

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $userId = $user->role_id == 2 ? $user->owner_id : $user->parent_id;

        $sub_categories = SubCategory::with('category')
            ->where([
                ['user_id', $userId],
                ['category_id', $request->category],
                ['is_active', 1],
            ])
            ->when($request->name, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->name . '%');
            })->orderBy('id', 'desc')->get();

        foreach ($sub_categories as $sub_category) {
            $sub_category->image = $sub_category->image
                ? asset('storage/' . $sub_category->image)
                : asset('no-image-icon.svg');
        }

        return $this->successResponse($sub_categories, 200, 'Sub Category retrieved successfully.');
    }


    // public function list(Request $request, $company)
    // {
    //     $user = User::where('slug_name',$company)->first();

    //     if($user->role_id == 2)
    //     {
    //         $query = Stock::with(['product.category', 'product.sub_category', 'product.metric'])->where('shop_id', $user->owner_id);

    //         $categories = Category::where([
    //             ['user_id', $user->owner_id],
    //             ['is_active', 1],
    //         ])->get();
    //     }
    //     else
    //     {
    //         $query = Stock::with(['product.category', 'product.sub_category', 'product.metric'])::where('shop_id', $user->parent_id);

    //         $categories = Category::where([
    //             ['user_id', $user->parent_id],
    //             ['is_active', 1],
    //         ])->get();
    //     }

    //     if ($user->role_id == 2) {
    //         $query->whereNull('branch_id');
    //     } else {
    //         $query->where('branch_id', $user->id);
            
    //     }

    //     // Product search
    //     $query->when($request->product, function ($query) use ($request) {
    //         $search = $request->product;

    //         $query->where(function ($q) use ($search) {
    //             $q->whereHas('product', function ($q1) use ($search) {
    //                 $q1->where('name', 'like', "%{$search}%");
    //             })
    //             ->orWhereHas('product.category', function ($q2) use ($search) {
    //                 $q2->where('name', 'like', "%{$search}%");
    //             })
    //             ->orWhereHas('product.sub_category', function ($q3) use ($search) {
    //                 $q3->where('name', 'like', "%{$search}%");
    //             });
    //         });
    //     });

    //     // Show only in-stock items
    //     $query->when($request->stock_in == 1, function ($query) {
    //         $query->where('quantity', '>', 0);
    //     });

    //     //return $query->sum('quantity');

    //     $stocks = $query->orderBy('category_id')->orderBy('sub_category_id')->orderBy('product_id')->paginate(10)->withQueryString();

    //     // Product image URL
    //     foreach ($stocks as $stock) {
    //         if ($stock->product) {
    //             $stock->product->image = $stock->product->image
    //                 ? asset('storage/' . $stock->product->image)
    //                 : asset('no-image-icon.svg');
    //             $stock->product->category->image = $stock->product->category->image
    //                 ? asset('storage/' . $stock->product->category->image)
    //                 : asset('no-image-icon.svg');
    //             $stock->product->sub_category->image = $stock->product->sub_category->image
    //                 ? asset('storage/' . $stock->product->sub_category->image)
    //                 : asset('no-image-icon.svg');
    //         }
    //     }

    //     return $this->successResponse(compact('stocks', 'categories'), 200, 'Stock retrieved successfully.');
    // }


    public function list(Request $request, $company)
    {
        $user = User::where('slug_name', $company)->first();

        if (!$user) {
            return $this->errorResponse('Company not found.', 404);
        }

        // Identify the stock owner and branch
        if ($user->role_id == 2) {
            $shopId = $user->owner_id;
            $branchId = null;
        } else {
            $shopId = $user->parent_id;
            $branchId = $user->id;
        }

        // Categories
        $categories = Category::where('user_id', $shopId)
            ->where('is_active', 1)
            ->get();

        // Pending queue quantities, grouped by product
        $queueStocks = QueueStock::where('from', $branchId ?? $shopId)
            ->where('status', 0)
            ->get();

        $queueByProduct = [];
        $queueByVariation = [];

        foreach ($queueStocks as $queue) {
            $productId = $queue->product_id;

            // Total pending quantity for normal stock
            $queueByProduct[$productId] =
                ($queueByProduct[$productId] ?? 0)
                + (int) $queue->quantity;

            // Pending quantity for each variation
            $variations = json_decode($queue->variation, true) ?? [];

            foreach ($variations as $variationId => $qty) {
                $queueByVariation[$variationId] =
                    ($queueByVariation[$variationId] ?? 0)
                    + (int) $qty;
            }
        }

        // Stock query
        $query = Stock::with([
            'product.category',
            'product.sub_category',
            'product.metric',
            'variations.size',
            'variations.colour',
        ])
            ->where('shop_id', $shopId)
            ->where('branch_id', $branchId)
            ->where('quantity', '>', 0);

        // Product search
        $query->when($request->product, function ($query) use ($request) {
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

        // Fetch stock records
        $stocks = $query
            ->orderBy('category_id')
            ->orderBy('sub_category_id')
            ->orderBy('product_id')
            ->get();

        // Calculate available stock and variations
        $stocks = $stocks->map(function ($stock) use (
            $queueByProduct,
            $queueByVariation
        ) {
            $product = $stock->product;

            if (!$product) {
                return null;
            }

            $queueQty = $queueByProduct[$stock->product_id] ?? 0;

            // Available normal stock
            $stock->queue_qty = $queueQty;
            $stock->available_quantity = max(
                0,
                $stock->quantity - $queueQty
            );

            // Variations with available quantities
            $variations = $stock->variations->map(function ($variation) use (
                $queueByVariation
            ) {
                $queueVariationQty =
                    $queueByVariation[$variation->id] ?? 0;

                $variation->queue_qty = $queueVariationQty;
                $variation->available_quantity = max(
                    0,
                    $variation->quantity - $queueVariationQty
                );

                return $variation;
            })->filter(function ($variation) {
                return $variation->available_quantity > 0;
            })->values();

            $stock->setRelation('variations', $variations);

            // Update image URLs
            $product->image = $product->image
                ? asset('storage/' . $product->image)
                : asset('no-image-icon.svg');

            if ($product->category) {
                $product->category->image = $product->category->image
                    ? asset('storage/' . $product->category->image)
                    : asset('no-image-icon.svg');
            }

            if ($product->sub_category) {
                $product->sub_category->image = $product->sub_category->image
                    ? asset('storage/' . $product->sub_category->image)
                    : asset('no-image-icon.svg');
            }

            return $stock;
        })
        ->filter(function ($stock) {
            // Exclude products without available stock
            if (!$stock || $stock->available_quantity <= 0) {
                return false;
            }

            // Normal stock is available, so include it.
            // For variation products, include only if a variation
            // has available quantity.
            if ($stock->variations->isNotEmpty()) {
                return true;
            }

            return true;
        })
        ->values();

        // Apply stock_in filter (available quantity)
        if ($request->stock_in == 1) {
            $stocks = $stocks->filter(function ($stock) {
                return $stock->available_quantity > 0;
            })->values();
        }

        // Paginate after filtering
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;

        $paginatedStocks = new LengthAwarePaginator(
            $stocks->forPage($page, $perPage)->values(),
            $stocks->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return $this->successResponse(
            [
                'stocks' => $paginatedStocks,
                'categories' => $categories,
            ],
            200,
            'Stock retrieved successfully.'
        );
    }


}
