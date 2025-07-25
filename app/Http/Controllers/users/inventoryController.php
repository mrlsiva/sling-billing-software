<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class inventoryController extends Controller
{
    public function index(Request $request)
    {
        return view('users.inventories.index');
    }

    public function create(Request $request)
    {
        return view('users.inventories.create');
    }

    public function edit(Request $request)
    {
        return view('users.inventories.edit');
    }

    public function view(Request $request)
    {
        return view('users.inventories.view');
    }
}
