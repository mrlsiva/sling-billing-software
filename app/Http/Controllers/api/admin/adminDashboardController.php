<?php

namespace App\Http\Controllers\api\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;

class adminDashboardController extends Controller
{
    use ResponseHelper;

    public function dashboard(Request $request)
    {
        if (Auth::user()->role_id !== 1) {
            return $this->errorResponse([], 403, 'Unauthorized.');
        }

        $shops = User::with(['user_detail', 'bank_detail'])
            ->where('role_id', 2)
            ->when($request->shop, function ($q) use ($request) {
                $search = $request->shop;
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('user_name', 'like', "%{$search}%")
                       ->orWhere('slug_name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($shop) {
                $shop->total_branches = User::where('parent_id', $shop->id)->count();
                $shop->total_orders   = Order::where('shop_id', $shop->id)->count();
                $shop->total_sales    = Order::where('shop_id', $shop->id)->sum('bill_amount');
                $shop->is_expired     = $shop->user_detail?->plan_end
                    ? now()->gt($shop->user_detail->plan_end)
                    : false;
                return $shop;
            });

        return $this->successResponse([
            'shops'       => $shops,
            'total_shops' => $shops->count(),
            'active_shops' => $shops->where('is_active', 1)->count(),
        ], 200, 'Admin dashboard retrieved successfully.');
    }

    public function profile(Request $request)
    {
        if (Auth::user()->role_id !== 1) {
            return $this->errorResponse([], 403, 'Unauthorized.');
        }

        $user = User::find(Auth::user()->id);

        return $this->successResponse($user, 200, 'Profile retrieved successfully.');
    }
}