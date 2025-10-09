<?php

namespace App\Http\Controllers\branches;

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

class billingController extends Controller
{
    use Log, Notifications;
    
    public function billing(Request $request)
    {

        $genders = Gender::where('is_active',1)->get();
        $shop_payment_ids = ShopPayment::where([['shop_id', Auth::user()->parent_id],['is_active', 1]])->pluck('payment_id')->toArray();
        $payments = Payment::whereIn('id',$shop_payment_ids)->get();
        $finances = Finance::where([['shop_id',Auth::user()->parent_id],['is_active',1]])->get();
        $categories = Stock::where([['branch_id',Auth::user()->id],['is_active',1]])->select('category_id')->get();
        $categories = Category::whereIn('id',$categories)->get();
        $staffs = Staff::where([['branch_id',Auth::user()->id],['is_active',1]])->get();

        $pagination = PosSetting::where('branch_id',Auth::user()->id)->first();
        if($pagination)
        {
            $pagination = $pagination->pagination;
        }
        else
        {
            $pagination = 21;
        }

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

        return view('branches.billing',compact('stocks','categories','genders','payments','finances','staffs'));
    }

    public function get_sub_category(Request $request)
    {
        return $sub_categories = SubCategory::where([['category_id',$request->id],['is_active',1]])->get();
    }

    public function get_product(Request $request)
    {

        $pagination = PosSetting::where('branch_id',Auth::user()->id)->first();
        if($pagination)
        {
            $pagination = $pagination->pagination;
        }
        else
        {
            $pagination = 21;
        }
        
        $stocks = Stock::with(['product.category', 'product.sub_category'])
            ->where('branch_id', Auth::id())
            ->where('is_active', 1)
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
        return view('branches.billing', compact('stocks'));
    }


    public function get_product_detail(Request $request)
    {
        return $products = Product::with(['tax','sub_category','category','stock' => function ($query) use ($request) {
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
            'branch_id' => Auth::user()->id,
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

    public function store(Request $request)
    {

        DB::beginTransaction();

        $user = User::where('id',Auth::user()->id)->first();
        $billSetup = BillSetup::where([['branch_id',Auth::user()->id],['is_active',1]])->first();

        if (!$billSetup) {
            return response()->json([
                'status'   => 'failure',
                'message'  => 'No active bill setup found for this branch.'
            ]);
        }

        // Active bill prefix
        $billPrefix = $billSetup->bill_number;

        // Get last order with this branch
        $lastOrder = Order::where('branch_id', Auth::user()->id)->orderBy('id', 'desc')->first();

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
                'user_id' => $user->parent_id,
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

        // ✅ Calculate total product discount
        $totalProductDiscount = collect($cart)->sum(function ($item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                return 0;
            }

            // Calculate per-item discount
            if ($product->discount_type == 1) {
                // Flat discount per unit
                $discount = $product->discount * $item['qty'];
            } elseif ($product->discount_type == 2) {
                // Percentage discount
                $discount = (($product->discount / 100) * $item['price']) * $item['qty'];
            } else {
                $discount = 0;
            }

            return $discount;
        });

        $order = Order::create([
            'shop_id'                   => $user->parent_id,
            'branch_id'                 => Auth::user()->id,
            'bill_id'                   => $newBillNo,
            'billed_by'                 => $request->billed_by,
            'customer_id'               => $customer->id,
            'total_product_discount'    => $totalProductDiscount,
            'bill_amount'               => $billAmount,
            'billed_on'                 => Carbon::now(),
        ]);


        foreach ($cart as $item) {

            $product = Product::where('id',$item['product_id'])->first();

            OrderDetail::create([
                'order_id'      => $order->id,
                'product_id'    => $item['product_id'],
                'name'          => $product->name,
                'quantity'      => $item['qty'],
                'price'         => $item['price'],
                'selling_price' => $item['price'],
                'tax_amount'    => $item['tax_amount'],
                'discount_type' => $product->discount_type,
                'discount'      => $product->discount,
            ]);

            $stock = Stock::where([['shop_id',$user->parent_id],['branch_id',Auth::user()->id],['product_id',$item['product_id']]])->first();

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
        $this->notification(Auth::user()->parent_id, null,'App/Models/Order', $order->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch '.Auth::user()->name. ' placed one order for cutomer '.$customer->name,null, null);

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

        return view('bills.'.$user_detail->billType->blade,compact('user','order','order_details','order_payment_details'));
    }

    public function view_bill(Request $request,$company,$id)
    {
        $user = User::with('user_detail','bank_detail')->where('id',Auth::user()->id)->first();
        $order = Order::where('id',$id)->first();
        $order_details = OrderDetail::where('order_id',$id)->get();
        $order_payment_details = OrderPaymentDetail::where('order_id',$id)->get();

        $user_detail = UserDetail::where('user_id',Auth::id())->first();
        return view('bills.view_bill',compact('user','order','order_details','order_payment_details'));
    }



}
