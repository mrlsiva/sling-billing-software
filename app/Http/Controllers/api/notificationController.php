<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use App\Models\NotificationType;
use App\Models\Notification;
use App\Traits\Log;


class notificationController extends Controller
{
    use Log, ResponseHelper;

    public function notification(Request $request, $type = null)
    {
        $notification_types = NotificationType::where('is_active', 1)
            ->orderBy('order_by', 'asc')
            ->get();

        $user = Auth::user();
        $notifications = collect(); // Default empty collection

        // Base query
        $query = Notification::with('type')->latest();

        if ($user->role_id == 2) {
            // Shop owner
            $query->where('shop_id', $user->owner_id);
        } elseif ($user->role_id == 3) {
            // Branch user
            $query->where('branch_id', $user->id);
        }

        // Optional filter by type (either ID or slug/name)
        if ($type) {
            $query->whereHas('type', function ($q) use ($type) {
                $q->where('notification_type_id', $type);
            });
        }

        $notifications = $query->get();

        $data = [
            'notification_types' => $notification_types,
            'notifications' => $notifications,
        ];

        return $this->successResponse($data, 200, 'Successfully returned filtered notifications');
    }
}
