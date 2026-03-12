<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class reportsController extends Controller
{
    public function report(Request $request)
    {

        return view('branches.reports.report');
    }
}
