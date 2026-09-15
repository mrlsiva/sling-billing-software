<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\BranchDailyReportExport;
use App\Models\ProductHistory;
use App\Models\OrderPaymentDetail;
use Illuminate\Http\Request;
use App\Models\Refund;
use App\Models\Expense;
use App\Models\Order;
use App\Models\User;
use App\Models\Payment;
use App\Services\CashLedgerService;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class dailyReportController extends Controller
{
    use Log;

    public function daily(Request $request, $company, CashLedgerService $cashLedger)
    {
        $date = $request->date ?? Carbon::today()->toDateString();

        /*
        |--------------------------------------------------
        | Orders
        |--------------------------------------------------
        */

        $orderQuery = Order::where('shop_id', Auth::user()->parent_id)
            ->where('branch_id', Auth::user()->id)
            ->whereDate('billed_on', $date);

        $refund = Order::where('shop_id', Auth::user()->parent_id)
            ->where('branch_id', Auth::user()->id)
            ->whereDate('billed_on', $date)->where('is_refunded', 1)->pluck('id');

        $totalRefund = 0;

        if ($refund->isNotEmpty()) {
            $totalRefund = Refund::whereIn('order_id', $refund)
                ->sum('refund_amount');
        }

        $totalSales = $orderQuery->sum('bill_amount');

        $totalSales = $totalSales - $totalRefund;

        $discount_amount = (clone $orderQuery)->sum('order_discount');

        // Monthly Sales (calendar month containing the selected date)
        $monthly_sales = $this->calculateMonthlySales($date);

        // Day-wide payment mode summary (independent of pagination below)
        $allOrderIds = (clone $orderQuery)->pluck('id');
        $paymentSummary = OrderPaymentDetail::select('payment_id', DB::raw('SUM(amount) as total_amount'))
            ->whereIn('order_id', $allOrderIds)
            ->groupBy('payment_id')
            ->with('payment')
            ->get();

        $orders = $orderQuery
            ->with([
                'branch',
                'shop',
                'customer',
                'billedBy',
                'payments.payment','refunds' // ✅ IMPORTANT for mode of payment
            ])
            ->withSum('refunds as total_refund', 'refund_amount')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------
        | Product History (IN / OUT)
        |--------------------------------------------------
        */

        $productIn = ProductHistory::with('product')
            ->whereDate('transfer_on', $date)
            ->where('to', Auth::user()->id)
            ->get();

        $productOut = ProductHistory::with('product')
            ->whereDate('transfer_on', $date)
            ->where('from', Auth::user()->id)
            ->get();

        /*
        |--------------------------------------------------
        | Calculate Amount (price * quantity)
        |--------------------------------------------------
        */

        $productInAmount = $productIn->sum(function ($item) {
            return ($item->product->price ?? 0) * $item->quantity;
        });

        $productOutAmount = $productOut->sum(function ($item) {
            return ($item->product->price ?? 0) * $item->quantity;
        });

        //Credit
        $order_id = Order::where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id)->whereDate('billed_on', $date)->pluck('id');
        $credit_amount = OrderPaymentDetail::whereIn('order_id',$order_id)->where('payment_id', 6)->sum('amount');

        //Expenses
        $expensesQuery = Expense::where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id)->whereDate('created_at', $date);
        $expense_amount = (clone $expensesQuery)->sum('amount');
        $expenses_list = $expensesQuery->orderBy('id', 'desc')->paginate(10, ['*'], 'expenses_page')->withQueryString();

        //Cash Ledger (Opening Balance / Cash Balance / Cash Given to HO)
        $cash_summary = $cashLedger->summary(Auth::user()->parent_id, Auth::user()->id, $date);

        //Outstanding dues collected today via UPI (Cash is already in cash_summary)
        $os_recd_summary = $cashLedger->collectedByAllModes(Auth::user()->parent_id, Auth::user()->id, $date);

        $paymentModes = Payment::where('id', '!=', 6)->where('is_active', 1)->get();

        return view('branches.reports.daily', compact(
            'orders',
            'totalSales',
            'monthly_sales',
            'productIn',
            'productOut',
            'productInAmount',
            'productOutAmount',
            'expenses_list',
            'os_recd_summary',
            'paymentModes',
            'credit_amount',
            'expense_amount',
            'cash_summary',
            'discount_amount',
            'paymentSummary'
        ));
    }

    public function download_excel(Request $request, $company, CashLedgerService $cashLedger)
    {
        $date = $request->date ?? Carbon::today()->toDateString();

        $orders = Order::where('shop_id', Auth::user()->parent_id)
            ->where('branch_id', Auth::user()->id)
            ->whereDate('billed_on', $date)
            ->withSum('refunds as total_refund', 'refund_amount')
            ->with(['branch','customer','billedBy','payments.payment','shop','refunds'])
            ->get();

        $refund = Order::where('shop_id', Auth::user()->parent_id)
            ->where('branch_id', Auth::user()->id)
            ->whereDate('billed_on', $date)
            ->withSum('refunds as total_refund', 'refund_amount')
            ->with(['branch','customer','billedBy','payments.payment','shop'])->where('is_refunded', 1)->pluck('id');

        $totalRefund = 0;

        if ($refund->isNotEmpty()) {
            $totalRefund = Refund::whereIn('order_id', $refund)
                ->sum('refund_amount');
        }

        $totalSales = $orders->sum('bill_amount');

        $totalSales = $totalSales - $totalRefund;



        /*
        |----------------------------------------
        | Product IN / OUT
        |----------------------------------------
        */
        $productIn = ProductHistory::with('product')
            ->whereDate('transfer_on', $date)
            ->where('to', Auth::user()->id)
            ->get();

        $productOut = ProductHistory::with('product')
            ->whereDate('transfer_on', $date)
            ->where('from', Auth::user()->id)
            ->get();

        $productInAmount = $productIn->sum(fn($i) => ($i->product->price ?? 0) * $i->quantity);
        $productOutAmount = $productOut->sum(fn($i) => ($i->product->price ?? 0) * $i->quantity);

        //Credit
        $order_id = Order::where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id)->whereDate('billed_on', $date)->pluck('id');
        $credit_amount = OrderPaymentDetail::whereIn('order_id',$order_id)->where('payment_id', 6)->sum('amount');

        //Expenses
        $expense_amount = Expense::where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id)->whereDate('created_at', $date)->sum('amount');
        $expenses_list = Expense::where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id)->whereDate('created_at', $date)->orderBy('id', 'desc')->get();

        //Cash Ledger (Opening Balance / Cash Balance / Cash Given to HO)
        $cash_summary = $cashLedger->summary(Auth::user()->parent_id, Auth::user()->id, $date);

        //Monthly Sales
        $monthly_sales = $this->calculateMonthlySales($date);

        //Outstanding dues collected today, broken down by every payment mode
        $os_recd_summary = $cashLedger->collectedByAllModes(Auth::user()->parent_id, Auth::user()->id, $date);

        //Payment mode breakdown, built from the already-loaded orders (no extra query)
        $paymentSummary = $this->buildPaymentSummary($orders);

        return Excel::download(
            new BranchDailyReportExport(
                $orders,
                $productIn,
                $productOut,
                $productInAmount,
                $productOutAmount,
                $totalSales,
                $date,
                $credit_amount,
                $expense_amount,
                $cash_summary,
                [
                    'monthly_sales' => $monthly_sales,
                    'os_recd_summary' => $os_recd_summary,
                    'paymentSummary' => $paymentSummary,
                    'expenses_list' => $expenses_list,
                ]

            ),
            'daily_report_' . now()->format('d-m-Y_h-i A') . '.xlsx'
        );
    }

    public function download_pdf(Request $request, CashLedgerService $cashLedger)
    {
        $date = $request->date ?? Carbon::today()->toDateString();

        $orders = Order::where('shop_id', Auth::user()->parent_id)
            ->where('branch_id', Auth::user()->id)
            ->whereDate('billed_on', $date)
            ->withSum('refunds as total_refund', 'refund_amount')
            ->with(['branch','customer','billedBy','payments.payment','shop','refunds'])
            ->get();

        $refund = Order::where('shop_id', Auth::user()->parent_id)
            ->where('branch_id', Auth::user()->id)
            ->whereDate('billed_on', $date)
            ->withSum('refunds as total_refund', 'refund_amount')
            ->with(['branch','customer','billedBy','payments.payment','shop'])->where('is_refunded', 1)->pluck('id');

        $totalRefund = 0;

        if ($refund->isNotEmpty()) {
            $totalRefund = Refund::whereIn('order_id', $refund)
                ->sum('refund_amount');
        }

        $totalSales = $orders->sum('bill_amount');

        $totalSales = $totalSales - $totalRefund;

        $productIn = ProductHistory::with('product')
            ->whereDate('transfer_on', $date)
            ->where('to', Auth::user()->id)
            ->get();

        $productOut = ProductHistory::with('product')
            ->whereDate('transfer_on', $date)
            ->where('from', Auth::user()->id)
            ->get();

        $productInAmount = $productIn->sum(fn($i) => ($i->product->price ?? 0) * $i->quantity);
        $productOutAmount = $productOut->sum(fn($i) => ($i->product->price ?? 0) * $i->quantity);

        //Credit
        $order_id = Order::where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id)->whereDate('billed_on', $date)->pluck('id');
        $credit_amount = OrderPaymentDetail::whereIn('order_id',$order_id)->where('payment_id', 6)->sum('amount');

        //Expenses
        $expense_amount = Expense::where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id)->whereDate('created_at', $date)->sum('amount');
        $expenses_list = Expense::where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id)->whereDate('created_at', $date)->orderBy('id', 'desc')->get();

        //Cash Ledger (Opening Balance / Cash Balance / Cash Given to HO)
        $cash_summary = $cashLedger->summary(Auth::user()->parent_id, Auth::user()->id, $date);

        //Monthly Sales
        $monthly_sales = $this->calculateMonthlySales($date);

        //Outstanding dues collected today, broken down by every payment mode
        $os_recd_summary = $cashLedger->collectedByAllModes(Auth::user()->parent_id, Auth::user()->id, $date);

        //Payment mode breakdown, built from the already-loaded orders (no extra query)
        $paymentSummary = $this->buildPaymentSummary($orders);

        $pdf = Pdf::loadView('branches.exports.daily_report_pdf', compact(
            'orders',
            'totalSales',
            'monthly_sales',
            'productIn',
            'productOut',
            'productInAmount',
            'productOutAmount',
            'expenses_list',
            'os_recd_summary',
            'paymentSummary',
            'date','credit_amount','expense_amount','cash_summary'
        ))->setPaper('a4','landscape');

        return $pdf->download('daily_report_' . now()->format('d-m-Y_h-i A') . '.pdf');
    }

    private function calculateMonthlySales($date)
    {
        $monthStart = Carbon::parse($date)->startOfMonth()->toDateString();
        $monthEnd = Carbon::parse($date)->endOfMonth()->toDateString();

        $monthlyOrderQuery = Order::where('shop_id', Auth::user()->parent_id)
            ->where('branch_id', Auth::user()->id)
            ->whereDate('billed_on', '>=', $monthStart)
            ->whereDate('billed_on', '<=', $monthEnd);

        $monthlySales = (clone $monthlyOrderQuery)->sum('bill_amount');

        $monthlyRefundIds = (clone $monthlyOrderQuery)->where('is_refunded', 1)->pluck('id');

        if ($monthlyRefundIds->isNotEmpty()) {
            $monthlySales -= Refund::whereIn('order_id', $monthlyRefundIds)->sum('refund_amount');
        }

        return $monthlySales;
    }

    private function buildPaymentSummary($orders)
    {
        return $orders->flatMap(fn($order) => $order->payments)
            ->groupBy('payment_id')
            ->map(function ($group) {
                return (object) [
                    'payment_id' => $group->first()->payment_id,
                    'total_amount' => $group->sum('amount'),
                    'payment' => $group->first()->payment,
                ];
            })
            ->values();
    }
}
