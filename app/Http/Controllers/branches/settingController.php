<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class settingController extends Controller
{
    public function index(Request $request)
    {
        return view('branches.settings.index');
    }
}
