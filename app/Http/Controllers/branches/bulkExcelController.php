<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\BulkUploadLog;

class bulkExcelController extends Controller
{
    public function history(Request $request)
    {
        $histories = BulkUploadLog::where('user_id',Auth::user()->id)->orderByDesc('run_on')->paginate(10);
        return view('branches.excel.history', compact('histories'));
    }
}
