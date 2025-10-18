<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\ShopPayment;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Finance;
use App\Models\Gender;
use App\Models\Stock;
use App\Models\Staff;

class generalController extends Controller
{
    use ResponseHelper;

    public function gender(Request $request)
    {
        $genders = Gender::where('is_active',1)->get();

        return $this->successResponse($genders, 200, 'Successfully returned all gender');
    }

    public function payment_list(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $shop_payment_ids = ShopPayment::where([['shop_id', Auth::user()->owner_id],['is_active', 1]])->pluck('payment_id')->toArray();
            $payments = Payment::whereIn('id',$shop_payment_ids)->get();
        }
        if(Auth::user()->role_id == 3)
        {
            $shop_payment_ids = ShopPayment::where([['shop_id', Auth::user()->parent_id],['is_active', 1]])->pluck('payment_id')->toArray();
            $payments = Payment::whereIn('id',$shop_payment_ids)->get();

        }
        
        return $this->successResponse($payments, 200, 'Successfully returned all payment list');
    }

    public function finance(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $finances = Finance::where([['shop_id',Auth::user()->owner_id],['is_active',1]])->get();
        }
        if(Auth::user()->role_id == 3)
        {
            $finances = Finance::where([['shop_id',Auth::user()->parent_id],['is_active',1]])->get();

        }
        
        return $this->successResponse($finances, 200, 'Successfully returned all finances');
    }

    public function category(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {

            $categories = Stock::where([['shop_id',Auth::user()->owner_id],['branch_id',null],['is_active',1]])->select('category_id')->get();
            $categories = Category::whereIn('id',$categories)->get();

        }

        if(Auth::user()->role_id == 3)
        {

            $categories = Stock::where([['branch_id',Auth::user()->id],['is_active',1]])->select('category_id')->get();
            $categories = Category::whereIn('id',$categories)->get();
        }

        foreach($categories as $category)
        {
            $category->image = $category->image
            ? asset('storage/' . $category->image)
            : asset('no-image-icon.jpg');
        }

        return $this->successResponse($categories, 200, 'Successfully returned all categories');

    }

    public function sub_category(Request $request,$category)
    {
        $sub_categories = SubCategory::where([['category_id',$category],['is_active',1]])->get();

        foreach($sub_categories as $sub_category)
        {
            $sub_category->image = $sub_category->image
            ? asset('storage/' . $sub_category->image)
            : asset('no-image-icon.jpg');
        }

        return $this->successResponse($sub_categories, 200, 'Successfully returned all Sub Categories');
    }

    public function staff(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $staffs = Staff::where([['shop_id',Auth::user()->owner_id],['branch_id',null],['is_active',1]])->get();
        }

        if(Auth::user()->role_id == 3)
        {
            $staffs = Staff::where([['branch_id',Auth::user()->id],['is_active',1]])->get();
        }

        return $this->successResponse($staffs, 200, 'Successfully returned all Staffs');
    }
}
