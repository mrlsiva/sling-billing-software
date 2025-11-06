<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\NotificationType;
use App\Models\Notification;

class notificationController extends Controller
{
    public function notification(Request $request)
    {
        $notification_types = NotificationType::where('is_active', 1)->orderBy('order_by', 'asc')->get();

        $user = Auth::user();
        $notifications = collect(); // Default empty collection

        if ($user->role_id == 2) {

            // Shop owner
            $notifications = Notification::where('shop_id', $user->owner_id)->latest()->get();

        } elseif ($user->role_id == 3) {

            // Branch user
            $notifications = Notification::where('branch_id', $user->id)->latest()->get();
        }

        return view('notifications.index', compact('notification_types', 'notifications'));
    }
}
