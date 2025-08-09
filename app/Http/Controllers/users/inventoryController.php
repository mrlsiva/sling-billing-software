<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;

class inventoryController extends Controller
{
    public function transfer(Request $request,$company,$shop,$branch)
    {
        $query = Stock::where('shop_id', $shop);

        if ($branch != 0) {
            $query->where('branch_id', $branch);
        }

        $stocks = $query->orderBy('category_id')->orderBy('sub_category_id')->orderBy('product_id')->paginate(30);

        $branches = User::where([['parent_id',Auth::user()->id],['is_active',1],['is_lock',0],['is_delete',0]])->get();
        $categories = Category::where([['user_id',Auth::user()->id],['is_active',1]])->get();

        return view('users.inventories.transfer',compact('stocks','branches','categories'));
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
        return $product = Product::with('metric')->where('id',$request->product)->first();
    }
}
