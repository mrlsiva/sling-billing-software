<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class purchaseReportController extends Controller
{
    use Log;

    public function purchase(Request $request)
    {
        $query = PurchaseOrder::query()->where('shop_id',Auth::user()->owner_id)
            ->with(['vendor', 'category', 'sub_category', 'product']);

        // Date filters
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $datas = $query->with(['vendor','category','sub_category','product'])
               ->latest()
               ->paginate(10);

        return view('users.reports.purchase', compact('datas'));
    }
    
}
