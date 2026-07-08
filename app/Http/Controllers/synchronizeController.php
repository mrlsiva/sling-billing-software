<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QueueStock;

class synchronizeController extends Controller
{
    public function synchronize_stock(Request $request)
    {
        $filter = request()->get('filter', 'received');

        $latestIds = QueueStock::query()
            ->when($filter === 'transfer', fn($q) => $q->where('from', auth()->id()))
            ->when($filter === 'received', fn($q) => $q->where('to', auth()->id()))
            ->selectRaw('MAX(id) as id')
            ->groupBy('unique_id');

        $stocks = QueueStock::with(['From', 'To', 'initiatedBy', 'updatedBy'])
            ->whereIn('id', $latestIds)
            ->latest()
            ->paginate(10);

        return view('synchronize', compact('stocks'));
    }
}
