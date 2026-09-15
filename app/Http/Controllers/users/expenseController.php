<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Expense;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class expenseController extends Controller
{
    use Log;

    // For an HO account expenses are logged against itself (no separate branch), so
    // shop_id and branch_id both point at the HO's own id (Auth::user()->owner_id).
    public function index(Request $request)
    {
        $date = $request->date ?? Carbon::today()->toDateString();
        $ownerId = Auth::user()->owner_id;

        $expensesQuery = Expense::where('shop_id', $ownerId)
            ->where('branch_id', $ownerId)
            ->whereDate('created_at', $date);

        $total_amount = (clone $expensesQuery)->sum('amount');

        $expenses = $expensesQuery->orderBy('id', 'desc')->paginate(10)->withQueryString();

        $titles = Expense::where('shop_id', $ownerId)
            ->where('branch_id', $ownerId)
            ->select('title')
            ->distinct()
            ->orderBy('title')
            ->pluck('title');

        return view('users.expenses.index', compact('expenses', 'titles', 'date', 'total_amount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
        ], [
            'title.required' => 'Title is required.',
            'amount.required' => 'Amount is required.',
            'amount.min' => 'Amount must be greater than 0.',
        ]);

        $ownerId = Auth::user()->owner_id;

        DB::beginTransaction();

        $expense = Expense::create([
            'shop_id' => $ownerId,
            'branch_id' => $ownerId,
            'title' => Str::title(trim($request->title)),
            'amount' => $request->amount,
        ]);

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Expense Create', 'App/Models/Expense', 'expenses', $expense->id, 'Insert', null, $request, 'Success', 'Expense Created Successfully');

        return redirect()->back()->with('toast_success', 'Expense added successfully.');
    }

    public function destroy(Request $request, $company, $id)
    {
        $ownerId = Auth::user()->owner_id;

        $expense = Expense::where('id', $id)
            ->where('branch_id', $ownerId)
            ->where('shop_id', $ownerId)
            ->first();

        if ($expense) {
            $expense->delete();

            $this->addToLog($this->unique(), Auth::user()->id, 'Expense Delete', 'App/Models/Expense', 'expenses', $id, 'Delete', null, $request, 'Success', 'Expense Deleted Successfully');
        }

        return redirect()->back()->with('toast_success', 'Expense deleted successfully.');
    }
}
