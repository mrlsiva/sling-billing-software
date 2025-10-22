<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class notificationsController extends Controller
{
    public function notification(Request $request)
    {
        return view('admin.notification');
    }
}
