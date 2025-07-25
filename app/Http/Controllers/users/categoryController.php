<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class categoryController extends Controller
{
    public function index(Request $request)
    {
        return view('users.categories.index');
    }
}
