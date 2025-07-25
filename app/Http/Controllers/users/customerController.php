<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class customerController extends Controller
{
    public function index(Request $request)
    {
        return view('users.customers.index');
    }

    public function create(Request $request)
    {
        return view('users.customers.create');
    }

    public function edit(Request $request)
    {
        return view('users.customers.edit');
    }

    public function view(Request $request)
    {
        return view('users.customers.view');
    }
}
