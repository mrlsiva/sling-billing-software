<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductHistory;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;

class stockController extends Controller
{
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
        $branches = User::where([['parent_id',Auth::user()->parent_id],['id','!=',Auth::user()->id],['is_active',1],['is_lock',0],['is_delete',0]])->orWhere('id',Auth::user()->parent_id)->get();

        $transfers = ProductHistory::where('from', Auth::user()->id)->orWhere('to', Auth::user()->id)->orderBy('transfer_on', 'desc')->paginate(10);


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
        return $product = Product::with('metric')->where('id',$request->product)->first();
    }

}
