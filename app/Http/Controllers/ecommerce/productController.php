<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\SubCategory;
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


    public function list(Request $request, $company)
    {
        $user = User::where('slug_name',$company)->first();

        if($user->role_id == 2)
        {
            $query = Stock::with(['product.category', 'product.sub_category', 'product.metric'])->where('shop_id', $user->owner_id);

            $categories = Category::where([
                ['user_id', $user->owner_id],
                ['is_active', 1],
            ])->get();
        }
        else
        {
            $query = Stock::with(['product.category', 'product.sub_category', 'product.metric'])::where('shop_id', $user->parent_id);

            $categories = Category::where([
                ['user_id', $user->parent_id],
                ['is_active', 1],
            ])->get();
        }

        if ($user->role_id == 2) {
            $query->whereNull('branch_id');
        } else {
            $query->where('branch_id', $user->id);
            
        }

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

        // Show only in-stock items
        $query->when($request->stock_in == 1, function ($query) {
            $query->where('quantity', '>', 0);
        });

        //return $query->sum('quantity');

        $stocks = $query->orderBy('category_id')->orderBy('sub_category_id')->orderBy('product_id')->paginate(10)->withQueryString();

        // Product image URL
        foreach ($stocks as $stock) {
            if ($stock->product) {
                $stock->product->image = $stock->product->image
                    ? asset('storage/' . $stock->product->image)
                    : asset('no-image-icon.svg');
                $stock->product->category->image = $stock->product->category->image
                    ? asset('storage/' . $stock->product->category->image)
                    : asset('no-image-icon.svg');
                $stock->product->sub_category->image = $stock->product->sub_category->image
                    ? asset('storage/' . $stock->product->sub_category->image)
                    : asset('no-image-icon.svg');
            }
        }

        return $this->successResponse(compact('stocks', 'categories'), 200, 'Stock retrieved successfully.');
    }


}
