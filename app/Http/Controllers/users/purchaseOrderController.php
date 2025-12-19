<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\VendorPaymentDetail;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderRefund;
use App\Models\ProductImeiNumber;
use App\Models\PurchaseOrder;
use App\Models\ShopPayment;
use App\Models\StockVariation;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Vendor;
use App\Models\Tax;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class purchaseOrderController extends Controller
{
    use Log, Notifications;

    public function index(Request $request)
    {
        $purchase_orders = PurchaseOrder::with('vendor')
            ->where('shop_id', Auth::user()->owner_id)
            ->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')
                    ->from('purchase_orders')
                    ->where('shop_id', Auth::user()->owner_id)
                    ->groupBy('invoice_no');
            })
            ->when(request('vendor'), function ($query, $vendor) {
                $query->whereHas('vendor', function ($q) use ($vendor) {
                    $q->where('name', 'like', "%{$vendor}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('users.purchase_orders.index', compact('purchase_orders'));
    }




    public function create(Request $request)
    {
        $vendors = Vendor::where('shop_id', Auth::user()->owner_id)->get();
        $shop_payment_ids = ShopPayment::where([['shop_id', Auth::user()->owner_id],['is_active', 1]])->pluck('payment_id')->toArray();
        $payments = Payment::whereIn('id',$shop_payment_ids)->get();
        $categories = Category::where([['user_id',Auth::user()->owner_id],['is_active',1]])->get();
        $taxes = Tax::where([['shop_id',Auth::user()->owner_id],['is_active',1]])->get();
        return view('users.purchase_orders.create',compact('vendors','payments','categories','taxes'));
    }

    public function get_product(Request $request)
    {
        return $products = Product::where([['user_id',Auth::user()->owner_id],['category_id',$request->category],['sub_category_id',$request->sub_category],['is_active',1]])->get();
    }

    public function get_product_detail(Request $request)
    {
        return $product = Product::with('metric')->where('id',$request->product)->first();
    }

    public function get_stock_variations(Request $request)
    {
        $stock = Stock::where('product_id', $request->product_id)
            ->where('shop_id', auth()->user()->owner_id)
            ->whereNull('branch_id')
            ->first();

        if (!$stock) {
            return response()->json([
                'stock_id' => null,
                'variations' => []
            ]);
        }

        $variations = StockVariation::with(['size', 'colour'])
            ->where('stock_id', $stock->id)
            ->get();

        return response()->json([
            'stock_id'   => $stock->id,
            'variations' => $variations
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor' => 'required',
            'invoice_date' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.category' => 'required',
            'products.*.sub_category' => 'required',
            'products.*.product' => 'required',
            'products.*.unit' => 'required',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price_per_unit' => 'required|numeric|min:0.01',
            'products.*.net_cost' => 'required|numeric',
            'products.*.gross_cost' => 'required|numeric',
        ], [
            'vendor.required' => 'Vendor is required.',
            'invoice_date.required' => 'Invoice date is required.',
            'products.required' => 'At least one product is required.',
            'products.min' => 'At least one product is required.',
            'products.*.category.required' => 'Category is required for all products.',
            'products.*.sub_category.required' => 'Sub Category is required for all products.',
            'products.*.product.required' => 'Product is required for all products.',
            'products.*.unit.required' => 'Unit is required for all products.',
            'products.*.quantity.required' => 'Quantity is required for all products.',
            'products.*.price_per_unit.required' => 'Price Per Unit is required for all products.',
            'products.*.net_cost.required' => 'Net Cost is required for all products.',
            'products.*.gross_cost.required' => 'Gross Cost is required for all products.',
        ]);

        // Validate unique IMEI numbers across all products
        $allImeis = [];

        foreach ($request->products as $index => $item) {

            if (!empty($item['imei'])) {

                $imeiList = is_array($item['imei']) ? $item['imei'] : explode(',', $item['imei']);

                foreach ($imeiList as $imei) {

                    $imei = trim($imei);

                    // ❗ Skip empty IMEI values
                    if ($imei === '' || $imei === null) {
                        continue;
                    }

                    if (in_array($imei, $allImeis)) {
                        return back()->withErrors([
                            "products.$index.imei" => "IMEI number $imei is duplicated across products."
                        ])->withInput();
                    }

                    $allImeis[] = $imei;
                }
            }
        }


        DB::beginTransaction();

        try {
            $vendor = Vendor::findOrFail($request->vendor);
            $totalGross = 0;

            foreach ($request->products as $item) {
                //return $item;

                if (!empty($item['imei'])) {
                    $imeiList = is_array($item['imei']) ? $item['imei'] : explode(',', $item['imei']);
                } else {
                    $imeiList = [];
                }

                $purchaseOrder = PurchaseOrder::create([
                    'shop_id'        => Auth::user()->owner_id,
                    'vendor_id'      => $request->vendor,
                    'payment_id'     => $request->payment,
                    'invoice_no'     => $request->invoice,
                    'invoice_date'   => $request->invoice_date,
                    'due_date'       => $request->due_date,
                    'category_id'    => $item['category'],
                    'sub_category_id'=> $item['sub_category'],
                    'product_id'     => $item['product'],
                    'metric_id'      => $item['unit'],
                    'quantity'       => $item['quantity'],
                    'price_per_unit' => $item['price_per_unit'],
                    'tax'            => $item['tax'] ?? 0,
                    'discount'       => $item['discount'] ?? 0,
                    'net_cost'       => $item['net_cost'],
                    'gross_cost'     => $item['gross_cost'],
                    'imei'           => !empty($imeiList) ? implode(',', $imeiList) : null,
                    'status'         => 0, // pending
                ]);

                // Update Product quantity
                $product = Product::find($item['product']);
                $product->increment('quantity', $item['quantity']);

                // Check if stock already exists
                $stock = Stock::where([
                    'shop_id'        => Auth::user()->owner_id,
                    'branch_id'      => null,
                    'category_id'    => $item['category'],
                    'sub_category_id'=> $item['sub_category'],
                    'product_id'     => $item['product'],
                ])->first();

                if ($stock) {
                    // Merge IMEI numbers
                    $existingImeis = !empty($stock->imei)
                        ? explode(',', $stock->imei)
                        : [];

                    $mergedImeis = array_unique(array_merge($existingImeis, $imeiList));

                    // Update existing stock
                    $stock->update([
                        'quantity' => $stock->quantity + $item['quantity'],
                        'imei'     => !empty($mergedImeis) ? implode(',', $mergedImeis) : null,
                    ]);

                } else {
                    // Create new stock
                    Stock::create([
                        'shop_id'        => Auth::user()->owner_id,
                        'branch_id'      => null,
                        'category_id'    => $item['category'],
                        'sub_category_id'=> $item['sub_category'],
                        'product_id'     => $item['product'],
                        'quantity'       => $item['quantity'],
                        'imei'           => !empty($imeiList) ? implode(',', $imeiList) : null,
                    ]);
                }
                

                // Handle IMEI Numbers
                if (!empty($item['imei'])) {
                    // Normalize IMEI input: accept both array and comma-separated string
                    $imeiList = is_array($item['imei']) ? $item['imei'] : explode(',', $item['imei']);

                    $imeiNumbers = array_filter(array_map('trim', $imeiList));

                    foreach ($imeiNumbers as $imei) {
                        ProductImeiNumber::create([
                            'purchase_order_id' => $purchaseOrder->id,
                            'product_id'        => $item['product'],
                            'name'              => $imei,
                            'is_sold'           => 0,
                        ]);
                    }
                }

                $productId  = $item['product'];
                $quantity   = (float)$item['quantity'];
                $netCost    = (float)$item['net_cost'];

                $stock = Stock::where('product_id', $productId)
                ->where('shop_id', auth()->user()->owner_id)
                ->whereNull('branch_id')
                ->first();

                if (!empty($item['variation']) && is_array($item['variation'])) 
                {

                    foreach ($item['variation'] as $var) {

                        $stockId  = $var['stock_id'];
                        $sizeId   = $var['size_id'] ?? null;
                        $colourId = $var['colour_id'] ?? null;
                        $qty      = (int) ($var['qty'] ?? 0);

                        if ($qty <= 0) {
                            continue;
                        }

                        // FIND VARIATION (null-safe)
                        $variation = StockVariation::where('stock_id', $stockId)
                            ->where('product_id', $productId)
                            ->where(function ($q) use ($sizeId) {
                                $sizeId === null ? $q->whereNull('size_id') : $q->where('size_id', $sizeId);
                            })
                            ->where(function ($q) use ($colourId) {
                                $colourId === null ? $q->whereNull('colour_id') : $q->where('colour_id', $colourId);
                            })
                            ->lockForUpdate()
                            ->first();

                        if (!$variation) {
                            // Optional: create variation if missing
                            $variation = StockVariation::create([
                                'stock_id'   => $stockId,
                                'product_id' => $productId,
                                'size_id'    => $sizeId,
                                'colour_id'  => $colourId,
                                'quantity'   => 0,
                                'price'      => $product->price,
                            ]);
                        }

                        // UPDATE QUANTITY
                        $variation->update([
                            'quantity' => $variation->quantity + $qty,
                            'price'    => $product->price,
                        ]);
                    }

                } 
                else 
                {

                    // DEFAULT VARIATION (no size / no colour)
                    $defaultVariation = StockVariation::where('stock_id', $stock->id)
                        ->where('product_id', $productId)
                        ->whereNull('size_id')
                        ->whereNull('colour_id')
                        ->lockForUpdate()
                        ->first();

                    if ($defaultVariation) {
                        $defaultVariation->update([
                            'quantity' => $defaultVariation->quantity + $quantity,
                            'price'    => $product->price,
                        ]);
                    }
                }



                $totalGross += $item['gross_cost'];

                // Log and Notification per product row
                $this->addToLog($this->unique(), Auth::user()->id, 'Purchase Order Created', 'App/Models/PurchaseOrder', 'purchase_orders', $purchaseOrder->id, 'Insert', null, json_encode($item), 'Success', 'Purchase Order Created');
                $this->notification(Auth::user()->owner_id, null, 'App/Models/PurchaseOrder', $purchaseOrder->id, null, json_encode($item), now(), Auth::user()->id, 'Purchase order created for product '.$product->name.' done successfully', null, null, 7);
            }

            // --- Handle prepaid balance ---
            $prepaid = $vendor->prepaid_amount ?? 0;

            if ($prepaid > 0) {
                $allocatable = 0;
                $comment = '';
                $status = 0;

                if ($prepaid >= $totalGross) {
                    $allocatable = $totalGross;
                    $vendor->update(['prepaid_amount' => $prepaid - $totalGross]);
                    $comment = 'Fully paid using prepaid balance';
                    $status = 1;
                } else {
                    $allocatable = $prepaid;
                    $vendor->update(['prepaid_amount' => 0]);
                    $comment = 'Partially paid using prepaid balance';
                    $status = 2;
                }

                VendorPaymentDetail::create([
                    'vendor_payment_id' => null,
                    'purchase_order_id' => $purchaseOrder->id, // last created order
                    'payment_id'        => 1,
                    'amount'            => $allocatable,
                    'paid_on'           => now(),
                    'comment'           => $comment,
                ]);

                // Update status for all orders in this invoice
                PurchaseOrder::where('invoice_no', $request->invoice)
                    ->update(['status' => $status]);
            }

            DB::commit();

            return redirect()->route('vendor.purchase_order.index', ['company' => request()->route('company')])
                ->with('toast_success', 'Purchase order created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('toast_error', 'Error creating purchase order: '.$e->getMessage());
        }
    }


    public function update(Request $request)
    {
        $purchase = PurchaseOrder::with('vendor')->findOrFail($request->purchase_order_id);

        DB::beginTransaction();

        try {

            $oldAmount = (float) $request->old_amount;
            $newAmount = (float) $request->new_amount;

            
            $purchase->update(['gross_cost' => $newAmount]);

            
            $purchase_order_detail = PurchaseOrderDetail::create([
                'purchase_order_id' => $purchase->id,
                'old_amount'        => $oldAmount,
                'new_amount'        => $newAmount,
                'updated_on'        => \Carbon\Carbon::now(),
                'comment'           => $request->reason,
            ]);

            
            $this->addToLog(
                $this->unique(),
                Auth::user()->id,
                'Purchase Order Updated',
                'App/Models/PurchaseOrderDetail',
                'purchase_order_details',
                $purchase_order_detail->id,
                'Insert',
                null,
                $request,
                'Success',
                'Purchase Order Updated'
            );

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/PurchaseOrder', $purchase->id, null, json_encode($request->all()), now(), Auth::user()->id, 'purchase order updated for product '.$purchase->product->name.' done successfully',null, null,7);

            
            $paymentsForThis = VendorPaymentDetail::where('purchase_order_id', $purchase->id)->orderBy('id', 'desc')->get();

            $alreadyPaid = (float) $paymentsForThis->sum('amount');

            
            if ($alreadyPaid > $newAmount) {
                $toFree = $alreadyPaid - $newAmount;

                
                $freedByPayment = []; // [payment_id => amount]

                
                foreach ($paymentsForThis as $payRow) {
                    if ($toFree <= 0) break;

                    $rowAmt = (float) $payRow->amount;
                    $reduce = min($rowAmt, $toFree);

                    $newRowAmt = $rowAmt - $reduce;

                    if ($newRowAmt <= 0) {
                        
                        $payRow->delete();
                    } else {
                        $payRow->amount = $newRowAmt;
                        $payRow->save();
                    }

                    // accumulate freed amount mapped to original payment_id
                    $pid = $payRow->payment_id;
                    $freedByPayment[$pid] = ($freedByPayment[$pid] ?? 0) + $reduce;

                    $toFree -= $reduce;
                }

                
                $nextOrders = PurchaseOrder::where('vendor_id', $purchase->vendor_id)->where('status', '!=', 1)->orderBy('id')->get();

                
                foreach ($nextOrders as $next) {
                    
                    if (array_sum($freedByPayment) <= 0) break;

                    $paidNext = (float) VendorPaymentDetail::where('purchase_order_id', $next->id)->sum('amount');
                    $remainingNext = (float) $next->gross_cost - $paidNext;

                    if ($remainingNext <= 0) {
                        
                        continue;
                    }

                    
                    foreach ($freedByPayment as $pid => &$available) {
                        if ($available <= 0) continue;
                        if ($remainingNext <= 0) break;

                        $alloc = min($available, $remainingNext);

                        
                        $existing = VendorPaymentDetail::where('purchase_order_id', $next->id)
                        ->where('payment_id', $pid)
                        ->orderBy('id', 'desc')
                        ->first();

                        if ($existing) {
                            
                            $existing->amount = (float) $existing->amount + $alloc;
                            $existing->save();
                        } else {
                            
                            VendorPaymentDetail::create([
                                'vendor_payment_id' => null,
                                'purchase_order_id' => $next->id,
                                'payment_id'        => $pid,
                                'amount'            => $alloc,
                                'paid_on'           => now(),
                                'comment'           => 'Auto-adjusted from PO #' . $purchase->id,
                            ]);
                        }

                        $available -= $alloc;
                        $remainingNext -= $alloc;
                    }
                    unset($available); // break reference

                    
                    $totalPaidNext = (float) VendorPaymentDetail::where('purchase_order_id', $next->id)->sum('amount');
                    if ($totalPaidNext >= (float) $next->gross_cost) {
                        $next->update(['status' => 1]); // fully paid
                    } elseif ($totalPaidNext > 0) {
                        $next->update(['status' => 3]); // partial
                    } else {
                        $next->update(['status' => 0]); // unpaid
                    }
                }

                
                $remainingFreed = array_sum($freedByPayment);
                if ($remainingFreed > 0) {
                    $vendor = $purchase->vendor;
                    $vendor->prepaid_amount = (float) ($vendor->prepaid_amount ?? 0) + $remainingFreed;
                    $vendor->save();
                }

                
                $currentPaid = (float) VendorPaymentDetail::where('purchase_order_id', $purchase->id)->sum('amount');
                if ($currentPaid >= $newAmount) {
                    $purchase->update(['status' => 1]);
                } elseif ($currentPaid > 0) {
                    $purchase->update(['status' => 3]);
                } else {
                    $purchase->update(['status' => 0]);
                }

            } else {
                
                if ($alreadyPaid >= $newAmount) {
                    $purchase->update(['status' => 1]);
                } elseif ($alreadyPaid > 0) {
                    $purchase->update(['status' => 3]);
                } else {
                    $purchase->update(['status' => 0]);
                }
            }

            DB::commit();

            return redirect()->route('vendor.ledger.index', [
                'company' => request()->route('company'),
                'id'      => $purchase->vendor->id
            ])->with('toast_success', 'Purchase order updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            // keep original behavior — rethrow or return with error message
            return redirect()->back()->with('toast_error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function get_detail($company,$id)
    {
        $purchase_orders = PurchaseOrder::with([
                'vendor',
                'category',
                'sub_category',
                'product',
                'metric'
            ])
            ->where('invoice_no', $id)
            ->get(); // 👈 or ->first() if single invoice

        return view('users.purchase_orders.detail', compact('purchase_orders'));
    }


    public function refund(Request $request)
    {
        DB::beginTransaction();

        $purchase = PurchaseOrder::where('id',$request->purchase_id)->first();

        $refund = PurchaseOrderRefund::create([
            'purchase_order_id'  => $purchase->id,
            'vendor_id'          => $request->vendor,
            'old_amount'         => $request->purchase_amount,
            'quantity'           => $request->refund_quantity,
            'refunded_by'        =>  Auth::user()->id,
            'refund_amount'      => $request->refund_amount,
            'refund_on'          => Carbon::now(),
            'reason'             => $request->comment,
        ]);

        $paid = VendorPaymentDetail::where('purchase_order_id', $purchase->id)->first();

        if($paid)
        {
            $refund->update(['need_to_deduct' => 1]);
        }

        $newQuantity = max(0, $purchase->quantity - $request->refund_quantity);
        $newAmount   = max(0, $purchase->gross_cost - $request->refund_amount);

        $purchase->update([
            'quantity' => $newQuantity, 
            'gross_cost' => $newAmount, 
            'is_refunded' => 1
        ]);

        $purchase = PurchaseOrder::where('id',$request->purchase_id)->first();

        // 🔑 Recalculate total paid for this order
        $totalPaid = VendorPaymentDetail::where('purchase_order_id', $purchase->id)->sum('amount');

        // 🔑 Update status
        if($totalPaid == 0)
        {
            $purchase->update(['status' => 0]);
        }
        elseif ($totalPaid >= $purchase->gross_cost) 
        {
            $purchase->update(['status' => 1]); // fully paid
        } 
        elseif ($totalPaid < $order->gross_cost) 
        {
            $purchase->update(['status' => 2]); // partial paid
        }

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Purchase Order Refund','App/Models/PurchaseOrderRefund','purchase_order_refunds',$refund->id,'Insert',null,json_encode($request->all()),'Success','Purchase Order Refund');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/PurchaseOrderRefund', $refund->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Purchase order return for product '.$purchase->product->name.' done successfully',null, null,7);
        
        DB::commit();

        return redirect()->back()->with('toast_success', 'Purchase refunded successfully!');
    }
}
