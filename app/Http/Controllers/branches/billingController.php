<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Gender;
use App\Models\Stock;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Finance;
use Illuminate\Support\Str;
use App\Models\User;
use App\Traits\Log;
use Session;
use DB;

class billingController extends Controller
{
    use Log;
    
    public function billing(Request $request)
    {

        $genders = Gender::where('is_active',1)->get();
        $payments = Payment::where('is_active',1)->get();
        $finances = Finance::where('is_active',1)->get();
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

        return view('branches.billing',compact('stocks','categories','genders','payments','finances'));
    }

    public function get_sub_category(Request $request)
    {
        return $sub_categories = SubCategory::where([['category_id',$request->id],['is_active',1]])->get();
    }

    public function get_product(Request $request)
    {
        $stocks = Stock::with(['product.category', 'product.sub_category'])
            ->where('branch_id', Auth::id())
            ->where('is_active', 1)
            ->when($request->category, fn($q, $category) => $q->where('category_id', $category))
            ->when($request->sub_category, fn($q, $subCategory) => $q->where('sub_category_id', $subCategory))
            ->when($request->filter == 1, fn($q) => $q->where('quantity', '>', 0))
            ->paginate(28);

        // If AJAX request → return JSON
        return response()->json([
            'data' => $stocks->items(),
            'pagination' => (string) $stocks->links('pagination::bootstrap-4') // or tailwind
        ]);

        // Else load Blade normally
        return view('branches.billing', compact('stocks'));
    }


    public function get_product_detail(Request $request)
    {
        return $products = Product::with(['sub_category','category','stock' => function ($query) use ($request) {
                $query->where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id);
            },
        ])->where('id', $request->id)->first();
    }

    public function suggestPhone(Request $request)
    {
        $phones = Customer::where('phone', 'like', $request->phone . '%')
            ->where('user_id', Auth::user()->parent_id)
            ->orderBy('phone')
            ->limit(5)
            ->pluck('phone'); // returns array-like collection

        return response()->json([
            'phones' => $phones
        ]);
    }

    public function get_customer_detail(Request $request)
    {
        return $customer = Customer::with('gender')->where('phone', $request->phone)->where('user_id', Auth::user()->parent_id)->first();
    }

    public function customer_store(Request $request)
    {
        $user = User::where('id',Auth::user()->id)->first();

        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => ['required','digits:10','different:alt_phone',
                Rule::unique('customers', 'phone')->where(function ($query) use ($user) {
                    return $query->where('user_id', $user->parent_id);
                }),
            ],
            'alt_phone' => 'nullable|digits:10|different:phone',
            'address' => 'required|string|max:200',
            'pincode' => 'nullable|digits:6|regex:/^[1-9][0-9]{5}$/',
        ], 
        [
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone is required.',
            'address.required' => 'Phone is required.',
        ]);

        DB::beginTransaction();

        $customer = Customer::create([ 
            'user_id' => $user->parent_id,
            'name' => Str::ucfirst($request->name),
            'phone' => $request->phone,
            'alt_phone' => $request->alt_phone,
            'address' => $request->address,
            'pincode' => $request->pincode,
            'gender_id' => $request->gender,
            'dob' => $request->dob,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Customer Create','App/Models/Customer','customers',$customer->id,'Insert',null,$request,'Success','Customer Created Successfully');

        return true;
    }


}
