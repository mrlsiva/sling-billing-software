<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Traits\Notifications;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Gender;
use App\Models\Stock;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Finance;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderPaymentDetail;
use App\Models\ShopPayment;
use App\Models\PosSetting;
use Illuminate\Support\Str;
use App\Models\UserDetail;
use App\Models\User;
use App\Models\Staff;
use App\Models\BillSetup;
use App\Traits\Log;
use Carbon\Carbon;
use Session;
use DB;

class billingsController extends Controller
{
    use Log, Notifications;

    public function billing(Request $request)
    {

        $genders = Gender::where('is_active',1)->get();
        $shop_payment_ids = ShopPayment::where([['shop_id', Auth::user()->id],['is_active', 1]])->pluck('payment_id')->toArray();
        $payments = Payment::whereIn('id',$shop_payment_ids)->get();
        $finances = Finance::where([['shop_id',Auth::user()->id],['is_active',1]])->get();
        $categories = Stock::where([['shop_id',Auth::user()->id],['branch_id',null],['is_active',1]])->select('category_id')->get();
        $categories = Category::whereIn('id',$categories)->get();
        $staffs = Staff::where([['shop_id',Auth::user()->id],['branch_id',null],['is_active',1]])->get();

        $pagination = 21;

        $stocks = Stock::where([['shop_id',Auth::user()->id],['branch_id',null],['is_active',1]])
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

        return view('users.billing',compact('stocks','categories','genders','payments','finances','staffs'));
    }

    public function get_sub_category(Request $request)
    {
        return $sub_categories = SubCategory::where([['category_id',$request->id],['is_active',1]])->get();
    }

    public function get_product(Request $request)
    {
        $pagination = 21;
        
        $stocks = Stock::with(['product.category', 'product.sub_category'])
            ->where([['shop_id',Auth::user()->id],['branch_id',null],['is_active',1]])
            ->when($request->category, fn($q, $category) => $q->where('category_id', $category))
            ->when($request->sub_category, fn($q, $subCategory) => $q->where('sub_category_id', $subCategory))
            ->when($request->filter == 1, fn($q) => $q->where('quantity', '>', 0))
            ->when($request->product, function ($q, $product) {
                $q->whereHas('product', function ($sub) use ($product) {
                    $sub->where(function ($inner) use ($product) {
                        $inner->where('name', 'like', "%{$product}%")
                              ->orWhere('code', 'like', "%{$product}%")
                              ->orWhere('hsn_code', 'like', "%{$product}%");
                    });
                });
            })
            ->paginate($pagination);

        // If AJAX request → return JSON
        return response()->json([
            'data' => $stocks->items(),
            'pagination' => (string) $stocks->links('pagination::bootstrap-4') // or tailwind
        ]);

        // Else load Blade normally
        return view('users.billing', compact('stocks'));
    }


    public function get_product_detail(Request $request)
    {
        return $products = Product::with(['tax','sub_category','category','stock' => function ($query) use ($request) {
                $query->where('shop_id', Auth::user()->id)->where('branch_id', null);
            },
        ])->where('id', $request->id)->first();
    }

    public function suggestPhone(Request $request)
    {
        $phones = Customer::where('phone', 'like', $request->phone . '%')
            ->where('user_id', Auth::user()->id)
            ->orderBy('phone')
            ->limit(5)
            ->pluck('phone'); // returns array-like collection

        return response()->json([
            'phones' => $phones
        ]);
    }

    public function get_customer_detail(Request $request)
    {
        return $customer = Customer::with('gender')->where('phone', $request->phone)->where('user_id', Auth::user()->id)->first();
    }

    public function store(Request $request)
    {

        DB::beginTransaction();

        $user = User::where('id',Auth::user()->id)->first();
        $billSetup = BillSetup::where([['shop_id', Auth::user()->id],['branch_id', null],['is_active',1]])->first();

        if (!$billSetup) {
            return response()->json([
                'status'   => 'failure',
                'message'  => 'No active bill setup found.'
            ]);
        }

        // Active bill prefix
        $billPrefix = $billSetup->bill_number;

        // Get last order with this branch
        $lastOrder = Order::where([['shop_id', Auth::user()->id],['branch_id', null]])->orderBy('id', 'desc')->first();

        $newBillNo = $billPrefix . '01'; // default start if no orders

        if ($lastOrder && $lastOrder->bill_id) {
            $lastBillNo = $lastOrder->bill_id;

            if (Str::startsWith($lastBillNo, $billPrefix)) {
                // continue sequence
                $lastNumber = (int) Str::replaceFirst($billPrefix, '', $lastBillNo);
                $newBillNo = $billPrefix . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
            } else {
                // reset sequence for new prefix
                $newBillNo = $billPrefix . '01';
            }
        }

        $customerData = $request->input('customer');
        $customer = Customer::firstOrCreate(
            ['phone' => $customerData['phone']], // unique by phone
            [
                'user_id' => $user->id,
                'branch_id' => null,
                'alt_phone' => $customerData['alt_phone'] ?? null,
                'name'      => $customerData['name'],
                'address'   => $customerData['address'],
                'pincode'   => $customerData['pincode'] ?? null,
                'gender_id' => $customerData['gender'] ?? null,
                'dob'       => $customerData['dob'] ?? null,
            ]
        );

        $cart = $request->input('cart', []);
        $billAmount = collect($cart)->sum(function ($item) {
            return ($item['qty'] * $item['price']);
        });

        $order = Order::create([
            'shop_id'     => $user->id,
            'branch_id'   => null,
            'bill_id'     => $newBillNo,
            'billed_by'   => $request->billed_by,
            'customer_id' => $customer->id,
            'bill_amount' => $billAmount,
            'billed_on'   => Carbon::now(),
        ]);


        foreach ($cart as $item) {

            $product = Product::where('id',$item['product_id'])->first();

            OrderDetail::create([
                'order_id'   => $order->id,
                'product_id' => $item['product_id'],
                'name'       => $product->name,
                'quantity'   => $item['qty'],
                'price'      => $item['price'],
                'tax_amount' => $item['tax_amount'],
            ]);

            $stock = Stock::where([['shop_id',$user->id],['branch_id',null],['product_id',$item['product_id']]])->first();

            $stock->update(['quantity' => $stock->quantity - $item['qty'] ]);

        }

        $payments = $request->input('payments', []);
        foreach ($payments as $payment) {

            $payment_id = Payment::where('name',$payment['method'])->first()->id;
            $extra = $payment['extra'] ?? [];

            OrderPaymentDetail::create([
                'order_id'   => $order->id,
                'payment_id' => $payment_id,
                'amount'     => $payment['amount'],
                'number'     => $extra['cheque_number'] ?? $extra['upi_id'] ?? $extra['card_number'] ?? $extra['finance_card'] ?? null,
                'card'       => $extra['card_name'] ?? null,
                'finance_id' => $extra['finance_type'] ?? null,
            ]);
        }

        

        //Log
        $this->addToLog($this->unique(),Auth::id(),'Order','App/Models/Order','orders',$order->id,'Insert',null,null,'Success','Order Created Successfully');

        //Notifiction
        $this->notification(Auth::user()->id, null,'App/Models/Order', $order->id, null, json_encode($request->all()), now(), Auth::user()->id, 'HO '.Auth::user()->name. ' placed one order for cutomer '.$customer->name,null, null);

        DB::commit();
        
        return response()->json([
            'status'   => 'success',
            'message'  => 'Order saved successfully',
            'order_id' => $order->id
        ]);

    }

    public function get_bill(Request $request,$company,$id)
    {
        $user = User::with('user_detail','bank_detail')->where('id',Auth::user()->id)->first();
        $order = Order::where('id',$id)->first();
        $order_details = OrderDetail::where('order_id',$id)->get();
        $order_payment_details = OrderPaymentDetail::where('order_id',$id)->get();

        $user_detail = UserDetail::where('user_id',Auth::id())->first();
        if($user_detail->bill_type == null || $user_detail->bill_type == 1)
        {
            return view('branches.bill',compact('user','order','order_details','order_payment_details'));
        }
        else
        {
            return view('branches.thermal_bill',compact('user','order','order_details','order_payment_details'));
        }


        // $pdf = Pdf::loadView('branches.bill', [
        //     'user' => $user,
        //     'order' => $order,
        //     'order_details' => $order_details,
        //     'order_payment_details' => $order_payment_details,
        // ])->setPaper('a4', 'landscape');
 
        // return $pdf->stream('bill.pdf'); // to open in browser
        // return $pdf->download('bill.pdf'); // to force download
    }

    public function view_bill(Request $request,$company,$id)
    {
        $user = User::with('user_detail','bank_detail')->where('id',Auth::user()->id)->first();
        $order = Order::where('id',$id)->first();
        $order_details = OrderDetail::where('order_id',$id)->get();
        $order_payment_details = OrderPaymentDetail::where('order_id',$id)->get();

        $user_detail = UserDetail::where('user_id',Auth::id())->first();
        return view('branches.view_bill',compact('user','order','order_details','order_payment_details'));
    }
    
}
