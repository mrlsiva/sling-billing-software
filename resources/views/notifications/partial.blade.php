@if($notifications->isEmpty())
    <p class="text-muted">No notifications found.</p>
@else
    @foreach($notifications as $notification)
        <div class="notification-item d-flex mb-3">
            <div class="notification-line me-3"></div>
            <div>
                <div class="notification-title">
                    <span class="fw-semibold">{{ $notification->message ?? 'Notification' }}</span>
                    @if($notification->url)
                        <a href="{{ $notification->url }}" class="text-primary ms-2">View</a>
                    @endif
                </div>
                <div class="notification-meta text-muted small">
                    {{ \Carbon\Carbon::parse($notification->created_at)->format('d M Y · h:i A') }}
                </div>
            </div>
        </div>
    @endforeach
@endif
