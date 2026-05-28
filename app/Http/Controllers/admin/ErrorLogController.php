<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ErrorReport;

class ErrorLogController extends Controller
{
    public function index(Request $request)
    {
        $error_reports = ErrorReport::paginate(10);
        return view('admin.error_logs.index',compact('error_reports'));
        
    }
}
