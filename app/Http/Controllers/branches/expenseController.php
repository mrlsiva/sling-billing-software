<?php

namespace App\Http\Controllers\branches;

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

    public function index(Request $request)
    {
        $date = $request->date ?? Carbon::today()->toDateString();

        $expensesQuery = Expense::where('shop_id', Auth::user()->parent_id)
            ->where('branch_id', Auth::user()->id)
            ->whereDate('created_at', $date);

        $total_amount = (clone $expensesQuery)->sum('amount');

        $expenses = $expensesQuery->orderBy('id', 'desc')->paginate(10)->withQueryString();

        $titles = Expense::where('shop_id', Auth::user()->parent_id)
            ->where('branch_id', Auth::user()->id)
            ->select('title')
            ->distinct()
            ->orderBy('title')
            ->pluck('title');

        return view('branches.expenses.index', compact('expenses', 'titles', 'date', 'total_amount'));
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

        DB::beginTransaction();

        $expense = Expense::create([
            'shop_id' => Auth::user()->parent_id,
            'branch_id' => Auth::user()->id,
            'title' => Str::title(trim($request->title)),
            'amount' => $request->amount,
        ]);

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Expense Create', 'App/Models/Expense', 'expenses', $expense->id, 'Insert', null, $request, 'Success', 'Expense Created Successfully');

        return redirect()->back()->with('toast_success', 'Expense added successfully.');
    }

    public function destroy(Request $request, $company, $id)
    {
        $expense = Expense::where('id', $id)
            ->where('branch_id', Auth::user()->id)
            ->where('shop_id', Auth::user()->parent_id)
            ->first();

        if ($expense) {
            $expense->delete();

            $this->addToLog($this->unique(), Auth::user()->id, 'Expense Delete', 'App/Models/Expense', 'expenses', $id, 'Delete', null, $request, 'Success', 'Expense Deleted Successfully');
        }

        return redirect()->back()->with('toast_success', 'Expense deleted successfully.');
    }
}
