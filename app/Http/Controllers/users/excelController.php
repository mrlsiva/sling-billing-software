<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BulkUploadLog;

class excelController extends Controller
{
    public function history(Request $request)
    {
        $histories = BulkUploadLog::orderByDesc('run_on')->paginate(10);
        return view('users.excel.history', compact('histories'));
    }

}
