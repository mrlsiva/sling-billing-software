<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorPaymentDetail;
use App\Models\VendorPayment;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderRefund;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use App\Models\ShopPayment;
use App\Models\VendorOpeningBalance;
use App\Models\VendorOpeningBalancePayment;
use App\Models\Vendor;
use App\Models\Payment;
use DB;


class ledgerController extends Controller
{
    public function index(Request $request,$company,$id)
    {
        $vendor = Vendor::where('id',$id)->first();
        $shop_payment_ids = ShopPayment::where('shop_id', Auth::user()->owner_id)->pluck('payment_id')->toArray();
        $payment_methods = Payment::whereIn('id',$shop_payment_ids)->get();

        $query = PurchaseOrder::with('details')->where('vendor_id', $id)->orderBy('id', 'desc');

        // ✅ Apply date filters if provided
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('invoice_date', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        // ✅ Apply search if provided
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_no', 'like', '%' . $request->search . '%')
                  ->orWhere('gross_cost', 'like', '%' . $request->search . '%');
            });
        }

        // 👉 Get ALL filtered order IDs BEFORE pagination
        $purchaseOrderIds = $query->pluck('id');

        // ✅ Get payments for ALL filtered orders
        $payments = VendorPaymentDetail::whereIn('purchase_order_id', $purchaseOrderIds)->get();

        // 👉 Paginate AFTER collecting IDs
        $purchase_orders = $query->paginate(10);

        // ✅ Totals
        $refund = PurchaseOrderRefund::whereIn('id', $purchaseOrderIds)->where('need_to_deduct',1)->sum('refund_amount');
        $totalGross = PurchaseOrder::whereIn('id', $purchaseOrderIds)->sum('gross_cost');
        $totalPaid  = $payments->sum('amount') - $refund;
        $remaining_opening_balance = VendorOpeningBalance::where([['vendor_id', $id],['shop_id', Auth::user()->owner_id]])->sum('remaining_amount');
        $balance    = $totalGross - $totalPaid + $remaining_opening_balance;
        $refund = PurchaseOrderRefund::where([['vendor_id', $id],['need_to_deduct',1]])->sum('refund_amount');
        $vendor_payments = VendorPayment::where('vendor_id',$id)->get();
        $totalPaid  = $vendor_payments->sum('amount') - $refund;
        
        

        return view('users.ledgers.index',compact('vendor','purchase_orders','payments','totalGross','totalPaid','balance','payment_methods','remaining_opening_balance'));
    }

    public function payment(Request $request)
    {
        $request->validate([
            'payment' => 'required|exists:payments,id',
            'payment_amount' => 'required|numeric|min:1',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        DB::beginTransaction();

        try {

            $vendor = Vendor::findOrFail($request->vendor_id);

            $vendor_payment = VendorPayment::create([
                'vendor_id'    => $request->vendor_id,
                'payment_id'   => $request->payment,
                'amount'       => $request->payment_amount,
                'paid_on'      => now(),
                'comment'      => $request->comment,
            ]);

            $amountToDistribute = $request->payment_amount;
            $paymentId = $request->payment;
            $comment = $request->comment;

            /*
            |--------------------------------------------------
            | ✅ STEP 1: Clear Vendor Overdues First
            |--------------------------------------------------
            */
            $overdues = VendorOpeningBalance::where('vendor_id', $vendor->id)
                            ->where('remaining_amount', '>', 0)
                            ->orderBy('id')
                            ->get();

            foreach ($overdues as $due) {
                if ($amountToDistribute <= 0) break;

                $remainingDue = $due->remaining_amount;

                $payAmount = min($amountToDistribute, $remainingDue);

                // ✅ Store payment entry
                VendorOpeningBalancePayment::create([
                    'vendor_opening_balance_id' => $due->id,
                    'amount'             => $payAmount,
                    'paid_on'            => now(),
                    'comment'            => $comment ?? 'Paid via vendor payment',
                ]);

                // ✅ Update remaining
                $due->remaining_amount -= $payAmount;
                $due->save();

                $amountToDistribute -= $payAmount;
            }

            /*
            |--------------------------------------------------
            | ✅ STEP 2: Allocate to Purchase Orders
            |--------------------------------------------------
            */
            $purchaseOrders = PurchaseOrder::where([
                    ['vendor_id', $vendor->id],
                    ['status', '!=', 1]
                ])
                ->orderBy('id')
                ->get();

            foreach ($purchaseOrders as $order) {
                if ($amountToDistribute <= 0) break;

                $alreadyPaid = VendorPaymentDetail::where('purchase_order_id', $order->id)->sum('amount');
                $remainingForOrder = $order->gross_cost - $alreadyPaid;

                if ($remainingForOrder <= 0) continue;

                $allocatable = min($amountToDistribute, $remainingForOrder);

                VendorPaymentDetail::create([
                    'vendor_payment_id' => $vendor_payment->id,
                    'purchase_order_id' => $order->id,
                    'payment_id'        => $paymentId,
                    'amount'            => $allocatable,
                    'paid_on'           => now(),
                    'comment'           => $comment,
                ]);

                $amountToDistribute -= $allocatable;

                $totalPaid = VendorPaymentDetail::where('purchase_order_id', $order->id)->sum('amount');

                if ($totalPaid >= $order->gross_cost) {
                    $order->update(['status' => 1]); // paid
                } elseif ($totalPaid > 0) {
                    $order->update(['status' => 2]); // partial
                }
            }

            /*
            |--------------------------------------------------
            | ✅ STEP 3: Store Remaining as Prepaid
            |--------------------------------------------------
            */
            if ($amountToDistribute > 0) {
                $vendor->prepaid_amount += $amountToDistribute;
                $vendor->save();
            }

            DB::commit();

            return redirect()->back()->with('toast_success', 'Payment allocated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('toast_error', $e->getMessage());
        }
    }

    public function getPayment(Request $request,$company,$id)
    {
        $payments = VendorPayment::with('payment')->where('vendor_id', $id)->orderBy('paid_on', 'desc')->get();

        return response()->json($payments);
    }




}
