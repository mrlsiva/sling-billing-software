<?php

namespace App\Services;

use App\Models\CashHandover;
use App\Models\CashOpeningSeed;
use App\Models\CreditPayment;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderPaymentDetail;
use App\Models\Payment;
use Carbon\Carbon;

class CashLedgerService
{
    // Payments table: id 1 = Cash
    const CASH_PAYMENT_ID = 1;

    /**
     * Outstanding/credit dues collected on a given date, via a given payment mode
     * (e.g. UPI collections received today against an earlier credit sale).
     */
    public function collectedByMode(int $shopId, int $branchId, string $date, int $paymentId): float
    {
        return (float) CreditPayment::where('payment_id', $paymentId)
            ->whereDate('paid_on', $date)
            ->whereHas('credit.order_payment_detail.order', function ($q) use ($shopId, $branchId) {
                $q->where('shop_id', $shopId)->where('branch_id', $branchId);
            })
            ->sum('amount');
    }

    /**
     * Outstanding/credit dues collected on a given date, broken down by every
     * active payment mode (excluding "Credit" itself, id 6 — you can't pay a
     * credit due with more credit). One query per mode; the mode list is small.
     */
    public function collectedByAllModes(int $shopId, int $branchId, string $date)
    {
        return Payment::where('id', '!=', 6)
            ->where('is_active', 1)
            ->get()
            ->map(function ($payment) use ($shopId, $branchId, $date) {
                return (object) [
                    'payment_id' => $payment->id,
                    'name' => $payment->name,
                    'amount' => $this->collectedByMode($shopId, $branchId, $date, $payment->id),
                ];
            });
    }

    /**
     * Compute the opening/closing cash balance for a branch on a given date.
     *
     * Opening Balance is derived, not stored: it is the one-time seed value
     * plus every day's net cash movement (cash in - expenses - handed over
     * to HO) between the seed's effective date and the day before $date.
     * Closing (Cash) Balance is the Opening Balance plus that same day's
     * net cash movement.
     */
    public function summary(int $shopId, int $branchId, string $date): array
    {
        $seed = CashOpeningSeed::where('shop_id', $shopId)->where('branch_id', $branchId)->first();

        if (!$seed) {
            return [
                'seeded' => false,
                'before_tracking' => false,
                'seed_date' => null,
                'opening_balance' => 0,
                'pos_cash_today' => 0,
                'os_cash_today' => 0,
                'cash_in_today' => 0,
                'expenses_today' => 0,
                'handover_today' => 0,
                'closing_balance' => 0,
            ];
        }

        $targetDate = Carbon::parse($date)->startOfDay();
        $effectiveDate = Carbon::parse($seed->effective_date)->startOfDay();

        if ($targetDate->lt($effectiveDate)) {
            return [
                'seeded' => true,
                'before_tracking' => true,
                'seed_date' => $effectiveDate->format('Y-m-d'),
                'opening_balance' => 0,
                'pos_cash_today' => 0,
                'os_cash_today' => 0,
                'cash_in_today' => 0,
                'expenses_today' => 0,
                'handover_today' => 0,
                'closing_balance' => 0,
            ];
        }

        $prior = $this->rangeTotals($shopId, $branchId, $effectiveDate, $targetDate->copy()->subDay());
        $today = $this->rangeTotals($shopId, $branchId, $targetDate, $targetDate);

        $openingBalance = $seed->opening_balance
            + ($prior['pos_cash'] + $prior['os_cash'] - $prior['expenses'] - $prior['handover']);

        $cashInToday = $today['pos_cash'] + $today['os_cash'];

        $closingBalance = $openingBalance + $cashInToday - $today['expenses'] - $today['handover'];

        return [
            'seeded' => true,
            'before_tracking' => false,
            'seed_date' => $effectiveDate->format('Y-m-d'),
            'opening_balance' => $openingBalance,
            'pos_cash_today' => $today['pos_cash'],
            'os_cash_today' => $today['os_cash'],
            'cash_in_today' => $cashInToday,
            'expenses_today' => $today['expenses'],
            'handover_today' => $today['handover'],
            'closing_balance' => $closingBalance,
        ];
    }

    protected function rangeTotals(int $shopId, int $branchId, Carbon $from, Carbon $to): array
    {
        if ($from->gt($to)) {
            return ['pos_cash' => 0, 'os_cash' => 0, 'expenses' => 0, 'handover' => 0];
        }

        $orderIds = Order::where('shop_id', $shopId)
            ->where('branch_id', $branchId)
            ->whereDate('billed_on', '>=', $from)
            ->whereDate('billed_on', '<=', $to)
            ->pluck('id');

        $posCash = OrderPaymentDetail::whereIn('order_id', $orderIds)
            ->where('payment_id', self::CASH_PAYMENT_ID)
            ->sum('amount');

        $osCash = CreditPayment::where('payment_id', self::CASH_PAYMENT_ID)
            ->whereDate('paid_on', '>=', $from)
            ->whereDate('paid_on', '<=', $to)
            ->whereHas('credit.order_payment_detail.order', function ($q) use ($shopId, $branchId) {
                $q->where('shop_id', $shopId)->where('branch_id', $branchId);
            })
            ->sum('amount');

        $expenses = Expense::where('shop_id', $shopId)
            ->where('branch_id', $branchId)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('amount');

        $handover = CashHandover::where('shop_id', $shopId)
            ->where('branch_id', $branchId)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('amount');

        return [
            'pos_cash' => (float) $posCash,
            'os_cash' => (float) $osCash,
            'expenses' => (float) $expenses,
            'handover' => (float) $handover,
        ];
    }
}
