<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class notificationController extends Controller
{
    public function notification(Request $request)
    {
        return view('notification');
    }
}
