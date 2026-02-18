<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\GstBill;
use App\Models\User;
use App\Traits\Log;
use DB;

class gstBillController extends Controller
{
    use Log, Notifications;

    public function index(Request $request)
    {
        
        $subQuery = GstBill::where([
            ['shop_id', Auth::user()->parent_id],
            ['branch_id', Auth::user()->id]
        ])->selectRaw('MAX(id) as id')->groupBy('order_id');

        $gst_bills = GstBill::joinSub($subQuery, 'latest', function ($join) {
            $join->on('gst_bills.id', '=', 'latest.id');
        })->select('gst_bills.*')->selectRaw('(SELECT SUM(gross) FROM gst_bills gb WHERE gb.order_id = gst_bills.order_id) as total_gross')->orderByDesc('gst_bills.id')->paginate(10);

        return view('branches.gst_bills.index',compact('gst_bills'));

    }

    public function create(Request $request)
    {
        $categories = Category::where([['user_id',Auth::user()->parent_id],['is_active',1]])->get();
        return view('branches.gst_bills.create',compact('categories'));
    }

    public function get_sub_category(Request $request)
    {
        return $sub_categories = SubCategory::where([['user_id',Auth::user()->parent_id],['category_id',$request->id],['is_active',1]])->get();
    }

    public function get_product(Request $request)
    {
        return $products = Product::where([['user_id',Auth::user()->parent_id],['category_id',$request->category],['sub_category_id',$request->sub_category],['is_active',1]])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id'         => 'required|string|max:50',
            'reference_no'     => 'required|string|max:50',

            'date_time'      => 'required|date',

            'issued_by'        => 'required|string|max:50',
            'sold_by'          => 'required|string|max:50',

            'customer_name'    => 'required|string|max:50',

            'customer_phone'   => ['required','digits:10'],

            'customer_address' => 'required|string|max:200',

            'category'         => 'required|integer',
            'sub_category'     => 'required|integer',

            'product'          => 'required|integer',

            'imie'             => 'nullable|string|max:50',
            'item_code'        => 'required|string|max:50',

            'quantity'         => 'required|integer|min:1',
            'gross'            => 'required|numeric|min:0',

        ], 
        [
            'customer_name.required'  => 'Customer name is required.',
            'customer_phone.required' => 'Customer phone is required.',
            'customer_phone.digits'   => 'Phone must be 10 digits.',
            'transfer_on.required'    => 'Transfer date is required.',
            'quantity.min'            => 'Quantity must be at least 1.',
            'gross.numeric'           => 'Gross must be a valid amount.',
        ]);

        DB::beginTransaction();

        $category = Category::where('id',$request->category)->first()->name;
        $sub_category = SubCategory::where('id',$request->sub_category)->first()->name;
        $product = Product::where('id',$request->product)->first()->name;

        $gst_bill = GstBill::create([
            'shop_id'          => Auth::user()->parent_id,
            'branch_id'        => Auth::user()->id,  
            'order_id'         => $request->order_id,
            'reference_no'     => $request->reference_no,
            'transfer_on'      => $request->date_time,
            'issued_by'        => $request->issued_by,
            'sold_by'          => $request->sold_by,
            'customer_name'    => $request->customer_name,
            'customer_phone'   => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'category'         => $category,
            'sub_category'     => $sub_category,
            'product'          => $product,
            'imie'             => $request->imie,
            'item_code'        => $request->item_code,
            'quantity'         => $request->quantity,
            'gross'            => $request->gross,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Gst Bill Create','App/Models/GstBill','gst_bills',$gst_bill->id,'Insert',null,$request,'Success','Gst Bill Created Successfully');

        return redirect()->back()->with('toast_success', 'GST Bill Created Successfully.');
    }

    public function view_bill(Request $request,$company,$id)
    {
        $user = User::with('user_detail','bank_detail')->where('id',Auth::user()->id)->first();
        $gst_bill = GstBill::where([['shop_id',Auth::user()->parent_id],['branch_id',Auth::user()->id],['id',$id]])->first();

        $gst_bill_details = GstBill::where('order_id',$gst_bill->order_id)->get();

        return view('bills.gst_bill',compact('gst_bill','user','gst_bill_details'));
    }

    
}
