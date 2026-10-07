<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminOrdersExport;
use App\Imports\AdminHistoricalOrderImport;
use App\Models\BulkUploadLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPaymentDetail;
use App\Models\Payment;
use App\Models\User;
use App\Traits\Log;

class orderController extends Controller
{
    use Log;

    /**
     * Shared filters: shop, branch (if that shop is picked), date range and a bill/phone search.
     */
    private function filteredOrders(Request $request)
    {
        return Order::with(['shop', 'branch', 'customer', 'billedBy'])
            ->withSum('refunds as total_refund', 'refund_amount')
            ->when($request->filled('shop_id'), fn ($q) => $q->where('shop_id', $request->shop_id))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('billed_on', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('billed_on', '<=', $request->to))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q2) use ($search) {
                    $q2->where('bill_id', 'like', "%{$search}%")
                       ->orWhereHas('customer', fn ($q3) => $q3->where('phone', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('id');
    }

    public function index(Request $request)
    {
        $shops = User::where([['role_id', 2], ['is_active', 1]])->orderBy('name')->get();

        $branches = $request->filled('shop_id')
            ? User::where([['parent_id', $request->shop_id], ['role_id', 3]])->orderBy('name')->get()
            : collect();

        $orders = $this->filteredOrders($request)->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders', 'shops', 'branches'));
    }

    public function export(Request $request)
    {
        $orders = $this->filteredOrders($request)->get();

        return Excel::download(new AdminOrdersExport($orders), 'Orders_'.now()->format('d-m-Y_h-i_A').'.xlsx');
    }

    /**
     * Loads historical/legacy orders for onboarding. Records the order and one payment
     * line for reporting only — it never touches product stock, since the sale already
     * happened before this system tracked it.
     */
    public function bulkImport(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx|max:10000']);

        $import = new AdminHistoricalOrderImport();
        Excel::import($import, $request->file('file'));

        $payments = Payment::where('is_active', 1)->get()->keyBy(fn ($p) => strtolower($p->name));

        $created = 0;
        $skipped = [];

        foreach ($import->rows as $i => $row) {
            $rowNo = $i + 2; // heading row is row 1

            $shopName = trim($row['shop'] ?? '');
            $branchName = trim($row['branch'] ?? '');
            $billId = trim($row['bill_id'] ?? '');
            $billedOn = trim((string) ($row['billed_on'] ?? ''));
            $phone = trim((string) ($row['customer_phone'] ?? ''));
            $customerName = trim($row['customer_name'] ?? '');
            $amount = trim((string) ($row['amount'] ?? ''));
            $paymentModeName = strtolower(trim($row['payment_mode'] ?? '')) ?: 'cash';
            $paidText = strtolower(trim($row['paid'] ?? 'yes'));

            if ($shopName === '' || $billedOn === '' || $phone === '' || $amount === '') {
                $skipped[] = "Row {$rowNo}: shop, billed on, customer phone and amount are required.";
                continue;
            }

            $shop = User::where('role_id', 2)
                ->whereRaw('LOWER(slug_name) = ?', [strtolower($shopName)])
                ->first();
            if (!$shop) {
                $skipped[] = "Row {$rowNo}: shop '{$shopName}' (slug name) was not found.";
                continue;
            }

            $branch = null;
            if ($branchName !== '') {
                $branch = User::where([['parent_id', $shop->id], ['role_id', 3]])
                    ->whereRaw('LOWER(name) = ?', [strtolower($branchName)])
                    ->first();
                if (!$branch) {
                    $skipped[] = "Row {$rowNo}: branch '{$branchName}' was not found under shop '{$shopName}'.";
                    continue;
                }
            }

            if (!preg_match('/^[0-9]{10}$/', $phone)) {
                $skipped[] = "Row {$rowNo}: customer phone must be exactly 10 digits.";
                continue;
            }

            try {
                $billedOnDate = \Carbon\Carbon::parse($billedOn);
            } catch (\Throwable $e) {
                $skipped[] = "Row {$rowNo}: billed on date '{$billedOn}' could not be read.";
                continue;
            }

            if (!is_numeric($amount) || (float) $amount <= 0) {
                $skipped[] = "Row {$rowNo}: amount must be a positive number.";
                continue;
            }

            if (!isset($payments[$paymentModeName])) {
                $skipped[] = "Row {$rowNo}: payment mode '{$row['payment_mode']}' does not match an existing payment mode.";
                continue;
            }

            DB::beginTransaction();
            try {
                // Customers belong to a shop. Reuse an existing one for this shop and phone
                // instead of creating a duplicate.
                $customer = Customer::where([['user_id', $shop->id], ['phone', $phone]])->first();
                if (!$customer) {
                    if ($customerName === '') {
                        DB::rollBack();
                        $skipped[] = "Row {$rowNo}: customer name is required for a new customer.";
                        continue;
                    }
                    $customer = Customer::create([
                        'user_id' => $shop->id,
                        'name' => $customerName,
                        'phone' => $phone,
                        'address' => '',
                    ]);
                }

                $order = Order::create([
                    'shop_id' => $shop->id,
                    'branch_id' => $branch->id ?? null,
                    'bill_id' => $billId !== '' ? $billId : 'HIST-'.$shop->id.'-'.now()->format('ymd').'-'.rand(1000, 9999),
                    'customer_id' => $customer->id,
                    'bill_amount' => $amount,
                    'billed_on' => $billedOnDate,
                    'is_online_order' => 0,
                    'is_paid' => in_array($paidText, ['no', '0', 'false']) ? 0 : 1,
                ]);

                OrderPaymentDetail::create([
                    'order_id' => $order->id,
                    'payment_id' => $payments[$paymentModeName]->id,
                    'amount' => $amount,
                ]);

                $this->addToLog($this->unique(), Auth::user()->id, 'Historical Order Import', 'App/Models/Order', 'orders', $order->id, 'Insert', null, null, 'Success', 'Historical order imported for shop "'.$shop->name.'"');

                DB::commit();
                $created++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $skipped[] = "Row {$rowNo}: could not be created ({$e->getMessage()}).";
            }
        }

        do {
            $runId = rand(100000, 999999);
        } while (BulkUploadLog::where('run_id', $runId)->exists());

        $directory = "bulk_uploads/orders/{$runId}";
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $excelPath = $uploadedFile->storeAs($directory, $originalName, 'public');

        $logContent = "======================".PHP_EOL
            ."Historical Order Import Report".PHP_EOL
            ."Uploaded On: ".now().PHP_EOL
            ."Run ID: {$runId}".PHP_EOL
            ."Uploaded File: {$originalName}".PHP_EOL
            ."Created: {$created}".PHP_EOL
            ."Skipped: ".count($skipped).PHP_EOL;
        if (!empty($skipped)) {
            $logContent .= "Skipped Details:".PHP_EOL;
            foreach ($skipped as $line) {
                $logContent .= "- {$line}".PHP_EOL;
            }
        }
        $logContent .= "======================".PHP_EOL;
        $logFile = "{$directory}/log.txt";
        Storage::disk('public')->put($logFile, $logContent);

        BulkUploadLog::create([
            'user_id' => Auth::user()->id,
            'run_id' => $runId,
            'run_on' => now(),
            'module' => 'Historical Order',
            'total_record' => $created + count($skipped),
            'successfull_record' => $created,
            'error_record' => count($skipped),
            'excel' => $excelPath,
            'log' => $logFile,
        ]);

        if (!empty($skipped)) {
            return redirect()->route('admin.order.index')
                ->with('toast_error', "Orders imported: {$created}. Skipped: ".implode(' | ', $skipped));
        }

        return redirect()->route('admin.order.index')
            ->with('toast_success', "Orders imported: {$created}.");
    }
}
