<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderPaymentDetail;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\OrderDetail;
use App\Models\PosSetting;
use App\Models\BillSetup;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Stock;
use App\Models\Order;
use App\Models\User;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class posController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function product(Request $request)
    {
        //return $request;
        if(Auth::user()->role_id == 2)
        {
            // Get pagination setting
            $paginationSetting = PosSetting::where([['shop_id', Auth::user()->owner_id],['branch_id',null]])->first();
            $pagination = $paginationSetting ? $paginationSetting->pagination : 21;

            // Build stock query
            $stocks = Stock::with(['product.category', 'product.sub_category'])->where([['shop_id',Auth::user()->owner_id],['branch_id',null],['is_active',1]])
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

        }

        if(Auth::user()->role_id == 3)
        {
            // Get pagination setting
            $paginationSetting = PosSetting::where('branch_id', Auth::user()->id)->first();
            $pagination = $paginationSetting ? $paginationSetting->pagination : 21;

            // Build stock query
            $stocks = Stock::with(['product.category', 'product.sub_category'])->where('branch_id', Auth::user()->id)->where('is_active', 1)
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
        }

         // ✅ Modify image URLs for all items
        foreach ($stocks as $stock) {
            if ($stock->product) {
                // Product image
                $stock->product->image = $stock->product->image
                    ? asset('storage/' . $stock->product->image)
                    : asset('no-image-icon.jpg');

                // Category image
                if ($stock->product->category) {
                    $stock->product->category->image = $stock->product->category->image
                        ? asset('storage/' . $stock->product->category->image)
                        : asset('no-image-icon.jpg');
                }

                // Sub-category image
                if ($stock->product->sub_category) {
                    $stock->product->sub_category->image = $stock->product->sub_category->image
                        ? asset('storage/' . $stock->product->sub_category->image)
                        : asset('no-image-icon.jpg');
                }
            }
        }

        return $this->successResponse($stocks, 200, 'Successfully returned all products');

    }

    public function get_product_detail(Request $request,$product)
    {

        if(Auth::user()->role_id == 2)
        {

            $product = Product::with(['tax','sub_category','category','stock' => function ($query) use ($request) {
                    $query->where('shop_id', Auth::user()->owner_id)->where('branch_id', null);
                },
            ])->where('user_id', Auth::user()->owner_id)->where('id', $product)->first();


        }

        if(Auth::user()->role_id == 3)
        {

            $product = Product::with(['tax','sub_category','category','stock' => function ($query) use ($request) {
                    $query->where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id);
                },
            ])->where('user_id', Auth::user()->parent_id)->where('id', $product)->first();
        }

        if($product)
        {
            $product->image = $product->image
            ? asset('storage/' . $product->image)
            : asset('no-image-icon.jpg');

            $product->category->image = $product->category->image
            ? asset('storage/' . $product->category->image)
            : asset('no-image-icon.jpg');

            $product->sub_category->image = $product->sub_category->image
            ? asset('storage/' . $product->sub_category->image)
            : asset('no-image-icon.jpg');
        }

        return $this->successResponse($product, 200, 'Successfully returned the requested product');
    }

    public function customer(Request $request)
    {

        if(Auth::user()->role_id == 2)
        {
            $customers = Customer::with('gender')->where('phone', 'like', $request->phone . '%')->where('user_id', Auth::user()->owner_id)->orderBy('phone')->get();
        }
        if(Auth::user()->role_id == 3)
        {
            $customers = Customer::with('gender')->where('phone', 'like', $request->phone . '%')->where('user_id', Auth::user()->parent_id)->orderBy('phone')->get();
        }

        return $this->successResponse($customers, 200, 'Successfully returned all customers');

    }

    public function store(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {

            DB::beginTransaction();

            $user = User::where('id',Auth::user()->owner_id)->first();
            $billSetup = BillSetup::where([['shop_id', Auth::user()->owner_id],['branch_id', null],['is_active',1]])->first();

            if (!$billSetup) {

                return $this->errorResponse("No active bill setup found for this branch.",400,"Failed to place a order");
            }

            // Active bill prefix
            $billPrefix = $billSetup->bill_number;

            // Get last order with this branch
            $lastOrder = Order::where([['shop_id', Auth::user()->owner_id],['branch_id', null]])->orderBy('id', 'desc')->first();

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
                [
                    'user_id' => $user->id, // include user_id in the unique key
                    'phone'   => $customerData['phone'],
                ],
                [
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
                'shop_id'                   => $user->id,
                'branch_id'                 => null,
                'bill_id'                   => $newBillNo,
                'billed_by'                 => $request->input('billed_by'),
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
                    'tax_percent'   => $product->tax->name,
                    'discount_type' => $product->discount_type,
                    'discount'      => $product->discount,
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
            $this->notification(Auth::user()->owner_id, null,'App/Models/Order', $order->id, null, json_encode($request->all()), now(), Auth::user()->id, 'HO '.Auth::user()->name. ' placed one order for cutomer '.$customer->name,null, null);

            DB::commit();
        
            return $this->successResponse($order->id, 200, 'Order Placed Successfully');
        }

        if(Auth::user()->role_id == 3)
        {
            //return $request;

            DB::beginTransaction();

            $user = User::where('id',Auth::user()->id)->first();
            $billSetup = BillSetup::where([['branch_id',Auth::user()->id],['is_active',1]])->first();

            if (!$billSetup) {

                return $this->errorResponse("No active bill setup found for this branch.",400,"Failed to place a order");
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
                [
                    'user_id' => $user->parent_id,     // include user_id for composite uniqueness
                    'phone'   => $customerData['phone'],
                ],
                [
                    'alt_phone' => $customerData['alt_phone'] ?? null,
                    'name'      => $customerData['name'],
                    'address'   => $customerData['address'],
                    'pincode'   => $customerData['pincode'] ?? null,
                    'gender_id' => $customerData['gender'] ?? null,
                    'dob'       => $customerData['dob'] ?? null,
                    'gst'       => $customerData['gst'] ?? null,
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
                'billed_by'                 => $request->input('billed_by'),
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
                    'tax_percent'   => $product->tax->name,
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
        
            return $this->successResponse($order->id, 200, 'Order Placed Successfully');

        }

    }


}
