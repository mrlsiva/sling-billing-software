<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class productController extends Controller
{
    public function index(Request $request)
    {
        return view('users.products.index');
    }

    public function create(Request $request)
    {
        return view('users.products.create');
    }

    public function edit(Request $request)
    {
        return view('users.products.edit');
    }

    public function view(Request $request)
    {
        return view('users.products.view');
    }
}
