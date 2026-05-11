<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\CreditPayment;
use App\Models\Payment;
use App\Models\Credit;

class creditsController extends Controller
{
    use ResponseHelper;

    // GET /api/ho/credits?date=2025-01-01&customer=
    public function credit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Date is required.');
        }

        $credits = Credit::whereHas('order_payment_detail.order', function ($q) use ($request) {
            $q->where('shop_id', Auth::user()->owner_id)
              ->where('branch_id', null)
              ->whereDate('billed_on', $request->date);

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
        ->paginate(10);

        $payments = Payment::where([['id', '!=', 6], ['is_active', 1]])->get();

        return $this->successResponse(
            compact('credits', 'payments'),
            200,
            'Credits retrieved successfully.'
        );
    }

    // GET /api/ho/credits/{id}/payments
    public function getCreditPayments(Request $request, $id)
    {
        $credit = Credit::with('creditPayments.payment')->find($id);

        if (!$credit) {
            return $this->errorResponse([], 404, 'Credit not found.');
        }

        return $this->successResponse($credit->creditPayments, 200, 'Credit payments retrieved successfully.');
    }

    // POST /api/ho/credits/payments/store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credit_id'  => 'required|exists:credits,id',
            'payment_id' => 'required|exists:payments,id',
            'amount'     => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        $credit = Credit::findOrFail($request->credit_id);

        if ($request->amount > $credit->remaining_amount) {
            return $this->errorResponse([], 422, 'Amount exceeds remaining credit balance.');
        }

        CreditPayment::create([
            'credit_id'  => $credit->id,
            'payment_id' => $request->payment_id,
            'amount'     => $request->amount,
            'paid_on'    => now(),
        ]);

        $credit->remaining_amount -= $request->amount;
        $credit->status = $credit->remaining_amount <= 0 ? 1 : 2;
        $credit->save();

        return $this->successResponse(
            ['remaining_amount' => $credit->remaining_amount, 'status' => $credit->status],
            200,
            'Credit payment recorded successfully.'
        );
    }
}