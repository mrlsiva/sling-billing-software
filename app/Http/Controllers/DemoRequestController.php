<?php

namespace App\Http\Controllers;

use App\Models\DemoRequest;
use Illuminate\Http\Request;

class DemoRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'mobile'        => 'required|string|max:20',
            'email'         => 'required|email|max:100',
            'shop_name'     => 'required|string|max:150',
            'business_type' => 'nullable|string|max:100',
        ]);

        DemoRequest::create($validated);

        return response()->json(['success' => true, 'message' => 'Thank you! We will contact you within 24 hours.']);
    }
}
