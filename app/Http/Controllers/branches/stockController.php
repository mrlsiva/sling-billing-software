<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\Product;

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

    public function qrcode(Request $request,$company,$id)
    {
        $product = Product::where('id',$id)->first();
        return view('branches.products.qrcode',compact('product'));
    }

    public function barcode(Request $request,$company,$id)
    {
        $product = Product::where('id',$id)->first();
        return view('branches.products.barcode',compact('product'));
    }

}
