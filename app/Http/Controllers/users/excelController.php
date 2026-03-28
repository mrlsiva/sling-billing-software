<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BulkUploadLog;
use Illuminate\Support\Facades\Auth;

class excelController extends Controller
{
    public function history(Request $request)
    {
        $histories = BulkUploadLog::where('user_id', Auth::user()->owner_id)->orderByDesc('run_on')->paginate(10);
        return view('users.excel.history', compact('histories'));
    }

}
