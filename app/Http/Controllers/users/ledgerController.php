<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorPaymentDetail;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use App\Models\ShopPayment;
use App\Models\Vendor;
use App\Models\Payment;


class ledgerController extends Controller
{
    public function index(Request $request,$company,$id)
    {
        $vendor = Vendor::where('id',$id)->first();
        $shop_payment_ids = ShopPayment::where('shop_id', Auth::user()->id)->pluck('payment_id')->toArray();
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
        $totalGross = PurchaseOrder::whereIn('id', $purchaseOrderIds)->sum('gross_cost');
        $totalPaid  = $payments->sum('amount');
        $balance    = $totalGross - $totalPaid;

        return view('users.ledgers.index',compact('vendor','purchase_orders','payments','totalGross','totalPaid','balance','payment_methods'));
    }

    public function payment(Request $request)
    {
        $request->validate([
            'payment' => 'required|exists:payments,id',
            'payment_amount' => 'required|numeric|min:1',
        ]);

        $amountToDistribute = $request->payment_amount;
        $paymentId = $request->payment;
        $comment = $request->comment;

        // get vendor purchase orders sorted by id
        $purchaseOrders = PurchaseOrder::where('vendor_id', $request->vendor_id)->orderBy('id')->get();

        foreach ($purchaseOrders as $order) {

            if ($amountToDistribute <= 0) break;

            // already paid for this order
            $alreadyPaid = VendorPaymentDetail::where('purchase_order_id', $order->id)->sum('amount');

            $remainingForOrder = $order->gross_cost - $alreadyPaid;

            if ($remainingForOrder <= 0) {
                continue; // order already fully paid
            }

            // how much to allocate now
            $allocatable = min($amountToDistribute, $remainingForOrder);

            VendorPaymentDetail::create([
                'purchase_order_id' => $order->id,
                'payment_id'        => $paymentId,
                'amount'            => $allocatable,
                'paid_on'           => now(),
                'comment'           => $comment,
            ]);

            // reduce the balance
            $amountToDistribute -= $allocatable;

            // 🔑 Recalculate total paid for this order
            $totalPaid = VendorPaymentDetail::where('purchase_order_id', $order->id)->sum('amount');

            // 🔑 Update status
            if ($totalPaid >= $order->gross_cost) {
                $order->update(['status' => 1]); // fully paid
            } elseif ($totalPaid > 0 && $totalPaid < $order->gross_cost) {
                $order->update(['status' => 2]); // partial
            }
        }

        return redirect()->back()->with('toast_success', 'Payment allocated successfully!');
    }



}
