<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderPaymentDetail;
use App\Models\CreditPayment;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Credit;
use App\Models\Order;
use App\Models\User;

class creditsController extends Controller
{
    public function credit(Request $request,$company,$date)
    {
        $credits = Credit::whereHas('order_payment_detail.order', function ($q) use ($date, $request) {

            $q->where('shop_id', Auth::user()->owner_id)
              ->where('branch_id', null)
              ->whereDate('billed_on', $date);

            if ($request->filled('customer')) {
                $search = $request->customer;

                $q->where(function ($q2) use ($search) {
                    $q2->where('bill_id', 'like', "%{$search}%")
                       ->orWhereHas('customer', function ($q3) use ($search) {
                           $q3->where('name', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%");
                       });
                });
            }

        })
        ->with('creditPayments.payment', 'order_payment_detail.order.customer')
        ->paginate(10)
        ->withQueryString();

        $payments = Payment::where([['id','!=',6],['is_active',1]])->get();

        return view('users.credits.index', compact('credits','payments'));

    }

    public function getCreditPayments(Request $request,$company,$id)
    {
        $credit = Credit::with('creditPayments.payment')->findOrFail($id);

        return response()->json($credit->creditPayments);
    }

    public function store(Request $request,$company)
    {
        $request->validate([
            'credit_id' => 'required',
            'payment_id' => 'required',
            'amount' => 'required|numeric|min:1'
        ]);

        $credit = Credit::findOrFail($request->credit_id);

        if($request->amount > $credit->remaining_amount){
            return response()->json(['error' => 'Amount exceeds remaining'], 422);
        }

        CreditPayment::create([
            'credit_id' => $credit->id,
            'payment_id' => $request->payment_id,
            'amount' => $request->amount,
            'paid_on' => now()
        ]);

        // update remaining
        $credit->remaining_amount -= $request->amount;

        if($credit->remaining_amount <= 0){
            $credit->status = 1; // Paid
        }
        else
        {
            $credit->status = 2; // Partial

        }

        $credit->save();

        return response()->json(['success' => true]);
    }

    public function index(Request $request)
    {
        $credits = Credit::whereHas('order_payment_detail.order', function ($q) use ($request) {

            $q->where('shop_id', Auth::user()->owner_id)
              ->where('branch_id', null);

            if ($request->filled('customer')) {
                $search = $request->customer;

                $q->where(function ($q2) use ($search) {
                    $q2->where('bill_id', 'like', "%{$search}%")
                       ->orWhereHas('customer', function ($q3) use ($search) {
                           $q3->where('name', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%");
                       });
                });
            }

        })
        ->with('creditPayments.payment', 'order_payment_detail.order.customer')->where('status','!=',1)
        ->paginate(10)
        ->withQueryString();

        $payments = Payment::where([['id','!=',6],['is_active',1]])->get();

        return view('users.credits.index', compact('credits','payments'));

    }
}
