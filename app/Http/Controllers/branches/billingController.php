<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;

class billingController extends Controller
{
    public function billing(Request $request)
    {
        $categories = Stock::where([['branch_id',Auth::user()->id],['is_active',1]])->select('category_id')->get();
        $categories = Category::whereIn('id',$categories)->get();

            $stocks = Stock::where('branch_id', Auth::user()->id)->where('is_active', 1)
                ->when($request->category, function ($query, $category) {
                    $query->where('category_id', $category);
                })
                ->when($request->sub_category, function ($query, $subCategory) {
                    $query->where('sub_category_id', $subCategory);
                })
                ->when($request->filter == 1, function ($query) {
                    $query->where('quantity', '>', 0);
                })
            ->paginate(28);

        return view('branches.billing',compact('stocks','categories'));
    }

    public function get_sub_category(Request $request)
    {
        return $sub_categories = SubCategory::where([['category_id',$request->id],['is_active',1]])->get();
    }
}
