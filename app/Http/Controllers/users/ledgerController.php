<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ledgerController extends Controller
{
    public function index(Request $request,$company,$id)
    {
        return view('users.ledgers.index');
    }
}
