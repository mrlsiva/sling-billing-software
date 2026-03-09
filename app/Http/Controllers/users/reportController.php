<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class reportController extends Controller
{
    public function report(Request $request)
    {

        return view('users.reports.report');
    }
}
