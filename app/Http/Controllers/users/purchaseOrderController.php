<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\ShopPayment;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Vendor;
use App\Traits\Log;
use DB;

class purchaseOrderController extends Controller
{
    use Log;

    public function index(Request $request)
    {
        $purchase_orders = PurchaseOrder::with('vendor')
        ->where('shop_id', Auth::user()->id)
        ->when(request('vendor'), function ($query, $vendor) {
            $query->whereHas('vendor', function ($q) use ($vendor) {
                $q->where('name', 'like', "%{$vendor}%");
            });
        })
        ->orderBy('id', 'desc')
        ->paginate(10);
        return view('users.purchase_orders.index',compact('purchase_orders'));
    }

    public function create(Request $request)
    {
        $vendors = Vendor::where('shop_id', Auth::user()->id)->get();
        $shop_payment_ids = ShopPayment::where('shop_id', Auth::user()->parent_id)->pluck('payment_id')->toArray();
        $payments = Payment::whereIn('id',$shop_payment_ids)->get();
        $categories = Category::where([['user_id',Auth::user()->id],['is_active',1]])->get();
        return view('users.purchase_orders.create',compact('vendors','payments','categories'));
    }

    public function get_product(Request $request)
    {
        return $products = Product::where([['user_id',Auth::user()->id],['category_id',$request->category],['sub_category_id',$request->sub_category],['is_active',1]])->get();
    }

    public function get_product_detail(Request $request)
    {
        return $product = Product::with('metric')->where('id',$request->product)->first();
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor' => 'required',
            'category' => 'required',
            'sub_category' => 'required',
            'product' => 'required',
            'unit' => 'required',
            'quantity' => 'required',
            'price_per_unit' => 'required',
            'net_cost' => 'required',
            'gross_cost' => 'required',
        ], 
        [
            'vendor.required' => 'Vendor is required.',
            'category.required' => 'Category is required.',
            'sub_category.required' => 'Sub Category is required.',
            'product.required' => 'Product is required.',
            'unit.required' => 'Unit is required.',
            'quantity.required' => 'Quantity is required.',
            'price_per_unit.required' => 'Price Per Unit is required.',
            'net_cost.required' => 'Net Cost is required.',
            'gross_cost.required' => 'Gross Cost is required.',
        ]);


        DB::beginTransaction();

        $purchase_order = PurchaseOrder::create([ 
            'shop_id' => Auth::user()->id,
            'vendor_id' => $request->vendor,
            'payment_id' => $request->payment,
            'invoice_no' => $request->invoice,
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'vendor_id' => $request->vendor,
            'category_id' => $request->category,
            'sub_category_id' => $request->sub_category,
            'product_id' => $request->product,
            'imei' => $request->imei,
            'metric_id' => $request->unit,
            'quantity' => $request->quantity,
            'price_per_unit' => $request->price_per_unit,
            'tax' => $request->tax ?: 0,
            'discount' => $request->discount,
            'net_cost' => $request->net_cost,
            'gross_cost' => $request->gross_cost,
        ]);

        $product = Product::where('id',$request->product)->first();
        $product->update(['quantity' => $product->quantity + $request->quantity]);

        $stock = Stock::where([['shop_id',Auth::user()->id],['branch_id',null],['category_id',$request->category],['sub_category_id',$request->sub_category],['product_id',$request->product]])->first();

        $stock->update(['quantity' => $stock->quantity + $request->quantity]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Purchase Order Created','App/Models/PurchaseOrder','purchase_orders',$purchase_order->id,'Insert',null,$request,'Success','Purchase Order Created');

        DB::commit();

        return redirect()->route('vendor.purchase_order.index', ['company' => request()->route('company')])->with('toast_success', 'Purchase order created successfully.');
        
    }


}
