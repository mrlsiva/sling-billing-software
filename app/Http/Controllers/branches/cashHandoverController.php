<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\CashHandover;
use App\Models\CashOpeningSeed;
use App\Services\CashLedgerService;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class cashHandoverController extends Controller
{
    use Log;

    public function index(Request $request, CashLedgerService $cashLedger)
    {
        $date = $request->date ?? Carbon::today()->toDateString();

        $shopId = Auth::user()->parent_id;
        $branchId = Auth::user()->id;

        $handoversQuery = CashHandover::where('shop_id', $shopId)
            ->where('branch_id', $branchId)
            ->whereDate('created_at', $date);

        $total_amount = (clone $handoversQuery)->sum('amount');

        $handovers = $handoversQuery->orderBy('id', 'desc')->paginate(10)->withQueryString();

        $seed = CashOpeningSeed::where('shop_id', $shopId)->where('branch_id', $branchId)->first();

        $summary = $cashLedger->summary($shopId, $branchId, $date);

        return view('branches.cash_handovers.index', compact('handovers', 'seed', 'summary', 'date', 'total_amount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'remarks' => 'nullable|string|max:150',
        ], [
            'amount.required' => 'Amount is required.',
            'amount.min' => 'Amount must be greater than 0.',
        ]);

        DB::beginTransaction();

        $handover = CashHandover::create([
            'shop_id' => Auth::user()->parent_id,
            'branch_id' => Auth::user()->id,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
        ]);

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Cash Handover Create', 'App/Models/CashHandover', 'cash_handovers', $handover->id, 'Insert', null, $request, 'Success', 'Cash Handover to HO Recorded Successfully');

        return redirect()->back()->with('toast_success', 'Cash handover recorded successfully.');
    }

    public function destroy(Request $request, $company, $id)
    {
        $handover = CashHandover::where('id', $id)
            ->where('branch_id', Auth::user()->id)
            ->where('shop_id', Auth::user()->parent_id)
            ->first();

        if ($handover) {
            $handover->delete();

            $this->addToLog($this->unique(), Auth::user()->id, 'Cash Handover Delete', 'App/Models/CashHandover', 'cash_handovers', $id, 'Delete', null, $request, 'Success', 'Cash Handover Deleted Successfully');
        }

        return redirect()->back()->with('toast_success', 'Cash handover deleted successfully.');
    }

    public function storeSeed(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0|max:99999999.99',
            'effective_date' => 'required|date',
        ], [
            'opening_balance.required' => 'Opening balance is required.',
            'effective_date.required' => 'Effective date is required.',
        ]);

        $shopId = Auth::user()->parent_id;
        $branchId = Auth::user()->id;

        DB::beginTransaction();

        $seed = CashOpeningSeed::updateOrCreate(
            ['shop_id' => $shopId, 'branch_id' => $branchId],
            ['opening_balance' => $request->opening_balance, 'effective_date' => $request->effective_date]
        );

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Cash Opening Balance Set', 'App/Models/CashOpeningSeed', 'cash_opening_seeds', $seed->id, 'Upsert', null, $request, 'Success', 'Cash Opening Balance Set Successfully');

        return redirect()->back()->with('toast_success', 'Opening cash balance saved successfully.');
    }
}
