<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\OrderPaymentDetail;
use App\Models\ProductImeiNumber;
use App\Models\StockVariation;
use App\Models\SubCategory;
use App\Models\BillSetup;
use App\Models\Customer;
use App\Models\Finance;
use App\Models\Gender;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\PosSetting;
use App\Models\Product;
use App\Models\ShopPayment;
use App\Models\Staff;
use App\Models\Stock;
use App\Models\Credit;
use App\Models\User;
use App\Traits\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DB;

class branchBillingController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function billing(Request $request)
    {
        $genders          = Gender::where('is_active', 1)->get();
        $shop_payment_ids = ShopPayment::where([['shop_id', Auth::user()->parent_id], ['is_active', 1]])->pluck('payment_id')->toArray();
        $payments         = Payment::whereIn('id', $shop_payment_ids)->get();
        $finances         = Finance::where([['shop_id', Auth::user()->parent_id], ['is_active', 1]])->get();
        $category_ids     = Stock::where([['branch_id', Auth::user()->id], ['is_active', 1]])->pluck('category_id');
        $categories       = \App\Models\Category::whereIn('id', $category_ids)->get();
        $staffs           = Staff::where([['branch_id', Auth::user()->id], ['is_active', 1]])->get();

        $pagination = PosSetting::where('branch_id', Auth::user()->id)->first();
        $pagination = $pagination ? $pagination->pagination : 15;

        $stocks = Stock::where('branch_id', Auth::user()->id)->where('is_active', 1)
            ->when($request->category, fn($q, $c) => $q->where('category_id', $c))
            ->when($request->sub_category, fn($q, $s) => $q->where('sub_category_id', $s))
            ->when($request->filter == 1, fn($q) => $q->where('quantity', '>', 0))
            ->when($request->product, function ($q, $product) {
                $q->whereHas('product', fn($sub) => $sub->where(fn($inner) =>
                    $inner->where('name', 'like', "%{$product}%")
                          ->orWhere('code', 'like', "%{$product}%")
                          ->orWhere('hsn_code', 'like', "%{$product}%")
                ));
            })
            ->paginate($pagination);

        return $this->successResponse(
            compact('stocks', 'categories', 'genders', 'payments', 'finances', 'staffs'),
            200,
            'Billing data retrieved successfully.'
        );
    }

    public function get_sub_category(Request $request)
    {
        $sub_categories = SubCategory::where([['category_id', $request->id], ['is_active', 1]])->get();
        return $this->successResponse($sub_categories, 200, 'Sub categories retrieved successfully.');
    }

    public function get_imei_product(Request $request)
    {
        $stock = Stock::where([
            ['product_id', $request->product],
            ['shop_id', Auth::user()->parent_id],
            ['branch_id', Auth::user()->id],
        ])->first();

        if (!$stock || empty($stock->imei)) {
            return $this->successResponse([], 200, 'No IMEI numbers found.');
        }

        return $this->successResponse(
            array_values(array_filter(explode(',', $stock->imei))),
            200,
            'IMEI numbers retrieved successfully.'
        );
    }

    public function get_product(Request $request)
    {
        $pagination = PosSetting::where('branch_id', Auth::user()->id)->first();
        $pagination = $pagination ? $pagination->pagination : 15;

        $stocks = Stock::with(['product.category', 'product.sub_category'])
            ->where('branch_id', Auth::id())
            ->where('is_active', 1)
            ->when($request->category, fn($q, $c) => $q->where('category_id', $c))
            ->when($request->sub_category, fn($q, $s) => $q->where('sub_category_id', $s))
            ->when($request->filter == 1, fn($q) => $q->where('quantity', '>', 0))
            ->when($request->product, function ($q, $product) {
                $q->whereHas('product', fn($sub) => $sub->where(fn($inner) =>
                    $inner->where('name', 'like', "%{$product}%")
                          ->orWhere('code', 'like', "%{$product}%")
                          ->orWhere('hsn_code', 'like', "%{$product}%")
                ));
            })
            ->paginate($pagination);

        return $this->successResponse($stocks, 200, 'Products retrieved successfully.');
    }

    public function get_product_detail(Request $request)
    {
        $product = Product::with([
            'tax', 'sub_category', 'category',
            'stock' => fn($q) => $q->where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id),
            'stock.variations.size',
            'stock.variations.colour',
        ])->find($request->id);

        if (!$product) {
            return $this->errorResponse([], 404, 'Product not found.');
        }

        return $this->successResponse([
            'id'           => $product->id,
            'name'         => $product->name,
            'price'        => $product->price,
            'tax_amount'   => $product->tax_amount,
            'tax'          => $product->tax,
            'category'     => $product->category,
            'sub_category' => $product->sub_category,
            'stock'        => $product->stock,
            'variations'   => $product->stock ? $product->stock->variations->map(fn($v) => [
                'id'          => $v->id,
                'size_name'   => optional($v->size)->name,
                'colour_name' => optional($v->colour)->name,
                'quantity'    => $v->quantity,
                'price'       => $v->price,
            ]) : [],
        ], 200, 'Product detail retrieved successfully.');
    }

    public function get_variation_detail(Request $request)
    {
        $variation = StockVariation::with(['size', 'colour', 'stock.product.tax'])->find($request->id);

        if (!$variation) {
            return $this->errorResponse([], 404, 'Variation not found.');
        }

        $product = Product::with([
            'stock' => fn($q) => $q->where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id),
        ])->find($variation->product_id);

        return $this->successResponse([
            'id'           => $variation->id,
            'product_id'   => $variation->product_id,
            'product_name' => $product->name,
            'category'     => $product->category->name,
            'sub_category' => $product->sub_category->name,
            'stock'        => $product->stock,
            'size_name'    => $variation->size->name ?? '',
            'colour_name'  => $variation->colour->name ?? '',
            'quantity'     => $variation->quantity,
            'base_price'   => (float) $product->price,
            'price'        => (float) $product->price,
            'tax_amount'   => (float) $product->tax_amount,
            'tax'          => $product->tax->name ?? '0',
        ], 200, 'Variation detail retrieved successfully.');
    }

    public function suggest_phone(Request $request)
    {
        $phones = Customer::where('phone', 'like', $request->phone . '%')
            ->where('user_id', Auth::user()->parent_id)
            ->orderBy('phone')
            ->pluck('phone');

        return $this->successResponse(['phones' => $phones], 200, 'Phone suggestions retrieved.');
    }

    public function get_customer_detail(Request $request)
    {
        $customer = Customer::with('gender')
            ->where('phone', $request->phone)
            ->where('user_id', Auth::user()->parent_id)
            ->first();

        if (!$customer) {
            return $this->errorResponse([], 404, 'Customer not found.');
        }

        return $this->successResponse($customer, 200, 'Customer detail retrieved successfully.');
    }

    public function customer_store(Request $request)
    {
        $user = User::find(Auth::user()->id);

        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:50',
            'phone'     => ['required', 'digits:10', 'different:alt_phone',
                Rule::unique('customers', 'phone')->where(fn($q) => $q->where('user_id', $user->parent_id)),
            ],
            'alt_phone' => 'nullable|digits:10|different:phone',
            'address'   => 'required|string|max:200',
            'pincode'   => 'nullable|digits:6|regex:/^[1-9][0-9]{5}$/',
        ], [
            'name.required'    => 'Name is required.',
            'phone.required'   => 'Phone is required.',
            'address.required' => 'Address is required.',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        DB::beginTransaction();

        $customer = Customer::create([
            'user_id'   => $user->parent_id,
            'branch_id' => Auth::user()->id,
            'name'      => Str::ucfirst($request->name),
            'phone'     => $request->phone,
            'alt_phone' => $request->alt_phone,
            'address'   => $request->address,
            'pincode'   => $request->pincode,
            'gender_id' => $request->gender,
            'dob'       => $request->dob,
            'gst'       => $request->gst,
        ]);

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Customer Create', 'App/Models/Customer', 'customers', $customer->id, 'Insert', null, $request, 'Success', 'Customer Created Successfully');

        return $this->successResponse($customer, 200, 'Customer created successfully.');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $user      = User::find(Auth::user()->id);
        $billSetup = BillSetup::where([['branch_id', Auth::user()->id], ['is_active', 1]])->first();

        if (!$billSetup) {
            return $this->errorResponse([], 422, 'No active bill setup found for this branch.');
        }

        $billPrefix = $billSetup->bill_number;
        $lastOrder  = Order::where('branch_id', Auth::user()->id)->orderBy('id', 'desc')->first();
        $newBillNo  = $billPrefix . '01';

        if ($lastOrder && $lastOrder->bill_id && Str::startsWith($lastOrder->bill_id, $billPrefix)) {
            $lastNumber = (int) Str::replaceFirst($billPrefix, '', $lastOrder->bill_id);
            $newBillNo  = $billPrefix . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
        }

        $customerData = $request->customer;
        $customer = Customer::firstOrCreate(
            ['user_id' => $user->parent_id, 'phone' => $customerData['phone']],
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

        $cart       = $request->cart ?? [];
        $billAmount = collect($cart)->sum(fn($item) => $item['qty'] * $item['price']);

        $totalProductDiscount = collect($cart)->sum(function ($item) {
            $product = Product::find($item['product_id']);
            if (!$product) return 0;
            if ($product->discount_type == 1) return $product->discount * $item['qty'];
            if ($product->discount_type == 2) return (($product->discount / 100) * $item['price']) * $item['qty'];
            return 0;
        });

        $order = Order::create([
            'shop_id'                => $user->parent_id,
            'branch_id'              => Auth::user()->id,
            'bill_id'                => $newBillNo,
            'billed_by'              => $request->billed_by,
            'customer_id'            => $customer->id,
            'order_discount'         => $request->discount,
            'total_product_discount' => $totalProductDiscount,
            'bill_amount'            => $billAmount,
            'billed_on'              => now(),
        ]);

        foreach ($cart as $item) {
            $product   = Product::find($item['product_id']);
            $variation = !empty($item['variation_id']) ? StockVariation::find($item['variation_id']) : null;

            OrderDetail::create([
                'order_id'      => $order->id,
                'product_id'    => $item['product_id'],
                'name'          => $product->name,
                'quantity'      => $item['qty'],
                'price'         => $item['price'],
                'selling_price' => $item['price'],
                'tax_amount'    => $item['tax_amount'],
                'tax_percent'   => $product->tax->name ?? '0',
                'discount_type' => $product->discount_type,
                'discount'      => $product->discount,
                'imei'          => !empty($item['imeis']) ? implode(',', $item['imeis']) : null,
                'size_id'       => $variation ? $variation->size_id : null,
                'colour_id'     => $variation ? $variation->colour_id : null,
            ]);

            $stock = Stock::where([
                ['shop_id', $user->parent_id],
                ['branch_id', Auth::user()->id],
                ['product_id', $item['product_id']],
            ])->first();

            if ($variation) {
                $variation->quantity -= $item['qty'];
                $variation->save();
            }

            $stock->quantity -= $item['qty'];

            if (!empty($item['imeis'])) {
                $existingImeis  = !empty($stock->imei) ? explode(',', $stock->imei) : [];
                $remainingImeis = array_values(array_diff($existingImeis, $item['imeis']));
                $stock->imei    = implode(',', $remainingImeis);

                ProductImeiNumber::where('product_id', $item['product_id'])
                    ->whereIn('name', $item['imeis'])
                    ->update(['is_sold' => 1]);
            }

            $stock->save();
        }

        foreach ($request->payments ?? [] as $payment) {
            $payment_id  = Payment::where('name', $payment['method'])->first()->id;
            $extra       = $payment['extra'] ?? [];

            $order_payment = OrderPaymentDetail::create([
                'order_id'   => $order->id,
                'payment_id' => $payment_id,
                'amount'     => $payment['amount'],
                'number'     => $extra['cheque_number'] ?? $extra['upi_id'] ?? $extra['card_number'] ?? $extra['finance_card'] ?? null,
                'card'       => $extra['card_name'] ?? null,
                'finance_id' => $extra['finance_type'] ?? null,
            ]);

            if ($payment_id == 6) {
                Credit::create([
                    'order_payment_detail_id' => $order_payment->id,
                    'amount'                  => $payment['amount'],
                    'remaining_amount'        => $payment['amount'],
                ]);
            }
        }

        $this->addToLog($this->unique(), Auth::id(), 'Order', 'App/Models/Order', 'orders', $order->id, 'Insert', null, null, 'Success', 'Order Created Successfully');
        $this->notification(Auth::user()->parent_id, null, 'App/Models/Order', $order->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch ' . Auth::user()->name . ' placed one order for customer ' . $customer->name . ' with an amount of ' . $billAmount . '.', null, null, 14);

        DB::commit();

        return $this->successResponse(['order_id' => $order->id, 'bill_id' => $newBillNo], 200, 'Order placed successfully.');
    }

    public function get_bill(Request $request, $id)
    {
        $order                 = Order::with(['shop', 'branch', 'customer', 'billedBy'])->find($id);
        $order_details         = OrderDetail::where('order_id', $id)->get();
        $order_payment_details = OrderPaymentDetail::where('order_id', $id)->get();

        if (!$order) {
            return $this->errorResponse([], 404, 'Order not found.');
        }

        return $this->successResponse(
            compact('order', 'order_details', 'order_payment_details'),
            200,
            'Bill retrieved successfully.'
        );
    }
}