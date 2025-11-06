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
use App\Models\PurchaseOrder;
use App\Models\ShopPayment;
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
        $vendors = Vendor::where('shop_id', Auth::user()->owner_id)->get();
        $shop_payment_ids = ShopPayment::where([['shop_id', Auth::user()->parent_id],['is_active', 1]])->pluck('payment_id')->toArray();
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
            'shop_id' => Auth::user()->owner_id,
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

        $stock = Stock::where([['shop_id',Auth::user()->owner_id],['branch_id',null],['category_id',$request->category],['sub_category_id',$request->sub_category],['product_id',$request->product]])->first();

        $stock->update(['quantity' => $stock->quantity + $request->quantity]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Purchase Order Created','App/Models/PurchaseOrder','purchase_orders',$purchase_order->id,'Insert',null,$request,'Success','Purchase Order Created');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/PurchaseOrder', $purchase_order->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Purchase order created for product '.$product->name.' done successfully',null, null,7);

        // --- Handle prepaid balance ---
        $vendor = Vendor::findOrFail($request->vendor);
        $prepaid = $vendor->prepaid_amount ?? 0;
        $grossCost = $request->gross_cost;

        if ($prepaid > 0) {
            $allocatable = 0;
            $comment = '';
            $status  = 0;

            if ($prepaid >= $grossCost) {
                // Full payment from prepaid
                $allocatable = $grossCost;
                $vendor->update(['prepaid_amount' => $prepaid - $grossCost]);
                $comment = 'Fully paid using prepaid balance';
                $status  = 1;
            } else {
                // Partial payment from prepaid
                $allocatable = $prepaid;
                $vendor->update(['prepaid_amount' => 0]);
                $comment = 'Partially paid using prepaid balance';
                $status  = 2;
            }

            // Record the payment detail
            VendorPaymentDetail::create([
                'vendor_payment_id' => null,
                'purchase_order_id' => $purchase_order->id,
                'payment_id'        => 1,
                'amount'            => $allocatable,
                'paid_on'           => now(),
                'comment'           => $comment,
            ]);

            // Update purchase order status
            $purchase_order->update(['status' => $status]);

        }


        DB::commit();

        return redirect()->route('vendor.purchase_order.index', ['company' => request()->route('company')])->with('toast_success', 'Purchase order created successfully.');
        
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
        $purchase = PurchaseOrder::with(['vendor', 'category', 'sub_category', 'product', 'metric']) // if relationships exist
                    ->findOrFail($id);

        return view('users.purchase_orders.detail', compact('purchase'));
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
