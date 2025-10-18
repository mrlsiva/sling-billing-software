<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\PosSetting;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Stock;

class posController extends Controller
{
    use ResponseHelper;

    public function product(Request $request)
    {
        //return $request;
        if(Auth::user()->role_id == 2)
        {
            // Get pagination setting
            $paginationSetting = PosSetting::where([['shop_id', Auth::user()->owner_id],['branch_id',null]])->first();
            $pagination = $paginationSetting ? $paginationSetting->pagination : 21;

            // Build stock query
            $stocks = Stock::with(['product.category', 'product.sub_category'])->where([['shop_id',Auth::user()->owner_id],['branch_id',null],['is_active',1]])
                ->when($request->category, function ($query, $category) {
                    $query->where('category_id', $category);
                })
                ->when($request->sub_category, function ($query, $subCategory) {
                    $query->where('sub_category_id', $subCategory);
                })
                ->when($request->filter == 1, function ($query) {
                    $query->where('quantity', '>', 0);
                })
                ->when($request->product, function ($query, $product) {
                    $query->whereHas('product', function ($q) use ($product) {
                        $q->where(function ($sub) use ($product) {
                            $sub->where('name', 'like', "%{$product}%")
                                ->orWhere('code', 'like', "%{$product}%")
                                ->orWhere('hsn_code', 'like', "%{$product}%");
                        });
                    });
                })
            ->paginate($pagination);

        }

        if(Auth::user()->role_id == 3)
        {
            // Get pagination setting
            $paginationSetting = PosSetting::where('branch_id', Auth::user()->id)->first();
            $pagination = $paginationSetting ? $paginationSetting->pagination : 21;

            // Build stock query
            $stocks = Stock::with(['product.category', 'product.sub_category'])->where('branch_id', Auth::user()->id)->where('is_active', 1)
                ->when($request->category, function ($query, $category) {
                    $query->where('category_id', $category);
                })
                ->when($request->sub_category, function ($query, $subCategory) {
                    $query->where('sub_category_id', $subCategory);
                })
                ->when($request->filter == 1, function ($query) {
                    $query->where('quantity', '>', 0);
                })
                ->when($request->product, function ($query, $product) {
                    $query->whereHas('product', function ($q) use ($product) {
                        $q->where(function ($sub) use ($product) {
                            $sub->where('name', 'like', "%{$product}%")
                                ->orWhere('code', 'like', "%{$product}%")
                                ->orWhere('hsn_code', 'like', "%{$product}%");
                        });
                    });
                })
            ->paginate($pagination);
        }

         // ✅ Modify image URLs for all items
        foreach ($stocks as $stock) {
            if ($stock->product) {
                // Product image
                $stock->product->image = $stock->product->image
                    ? asset('storage/' . $stock->product->image)
                    : asset('no-image-icon.jpg');

                // Category image
                if ($stock->product->category) {
                    $stock->product->category->image = $stock->product->category->image
                        ? asset('storage/' . $stock->product->category->image)
                        : asset('no-image-icon.jpg');
                }

                // Sub-category image
                if ($stock->product->sub_category) {
                    $stock->product->sub_category->image = $stock->product->sub_category->image
                        ? asset('storage/' . $stock->product->sub_category->image)
                        : asset('no-image-icon.jpg');
                }
            }
        }

        return $this->successResponse($stocks, 200, 'Successfully returned all products');

    }

    public function get_product_detail(Request $request,$product)
    {

        if(Auth::user()->role_id == 2)
        {

            $product = Product::with(['tax','sub_category','category','stock' => function ($query) use ($request) {
                    $query->where('shop_id', Auth::user()->owner_id)->where('branch_id', null);
                },
            ])->where('user_id', Auth::user()->owner_id)->where('id', $product)->first();


        }

        if(Auth::user()->role_id == 3)
        {

            $product = Product::with(['tax','sub_category','category','stock' => function ($query) use ($request) {
                    $query->where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id);
                },
            ])->where('user_id', Auth::user()->parent_id)->where('id', $product)->first();
        }

        if($product)
        {
            $product->image = $product->image
            ? asset('storage/' . $product->image)
            : asset('no-image-icon.jpg');

            $product->category->image = $product->category->image
            ? asset('storage/' . $product->category->image)
            : asset('no-image-icon.jpg');

            $product->sub_category->image = $product->sub_category->image
            ? asset('storage/' . $product->sub_category->image)
            : asset('no-image-icon.jpg');
        }

        return $this->successResponse($product, 200, 'Successfully returned the requested product');
    }

    public function customer(Request $request)
    {

        if(Auth::user()->role_id == 2)
        {
            $customers = Customer::with('gender')->where('phone', 'like', $request->phone . '%')->where('user_id', Auth::user()->owner_id)->orderBy('phone')->get();
        }
        if(Auth::user()->role_id == 3)
        {
            $customers = Customer::with('gender')->where('phone', 'like', $request->phone . '%')->where('user_id', Auth::user()->parent_id)->orderBy('phone')->first();
        }

        return $this->successResponse($customers, 200, 'Successfully returned all customers');

    }


}
