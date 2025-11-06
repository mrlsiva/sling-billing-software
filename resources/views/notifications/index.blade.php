@extends('layouts.master') 
@section('title') 
    <title>{{ config('app.name')}} | Notification</title> 
@endsection
@section('body') 
    <div class="row"> 
        <div class="col-xl-12 col-md-12"> 
            <div class="card"> 
                <div class="card-body"> 
                    <div class="container py-4"> 
                        <div class="notification-header">Notification</div> 
                            <ul class="nav nav-pills mb-4 gap-2" id="notificationTabs" role="tablist"> 
                                <li class="nav-item"><button class="nav-link active" id="all-tab" data-bs-toggle="pill" data-bs-target="#all" type="button">All</button></li>

                                @foreach($notification_types as $notification_type) 
                                    <li class="nav-item">
                                        <button class="nav-link" id="{{$notification_type->name}}_{{$notification_type->id}}" data-bs-toggle="pill" data-bs-target="#{{ Str::slug($notification_type->name) }}" type="button">{{$notification_type->name}}</button>
                                    </li> 
                                @endforeach
                            </ul> 

                            <div class="tab-content" id="notificationTabsContent">

                                <!-- All Tab -->
                                <div class="tab-pane fade show active" id="all" role="tabpanel">
                                    @if($notifications->isEmpty())
                                        <p class="text-muted">No notifications found.</p>
                                    @else
                                        @php
                                            // Group notifications by relative time
                                            $groupedNotifications = $notifications->groupBy(function ($notification) {
                                                $date = Carbon\Carbon::parse($notification->created_at);
                                                if ($date->isToday()) {
                                                    return 'Today';
                                                } elseif ($date->isYesterday()) {
                                                    return 'Yesterday';
                                                } elseif ($date->greaterThanOrEqualTo(Carbon\Carbon::now()->startOfWeek())) {
                                                    return 'This Week';
                                                } else {
                                                    return 'Earlier';
                                                }
                                            });
                                        @endphp

                                        @foreach($groupedNotifications as $label => $group)
                                            <h6 class="date-label mb-3 mt-4">{{ $label }}</h6>
                                            @foreach($group as $notification)
                                                <div class="notification-item d-flex mb-3">
                                                    <div class="notification-line me-3"></div>
                                                    <div>
                                                        <div class="notification-title">
                                                            <span class="fw-semibold">{{ $notification->message ?? 'New Notification' }}</span>
                                                            @if($notification->url)
                                                                <a href="{{ $notification->url }}" class="text-primary ms-2">View</a>
                                                            @endif
                                                        </div>
                                                        <div class="notification-meta text-muted small">
                                                            {{ Carbon\Carbon::parse($notification->created_at)->format('d M Y · h:i A') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    @endif
                                </div>

                                <!-- Dynamic Notification Type Tabs -->
                                @foreach($notification_types as $notification_type)
                                    <div class="tab-pane fade" id="{{ Str::slug($notification_type->name) }}" role="tabpanel">
                                        @php
                                            $filtered = $notifications->where('notification_type_id', $notification_type->id);

                                            $groupedFiltered = $filtered->groupBy(function ($notification) {
                                                $date = Carbon\Carbon::parse($notification->created_at);
                                                if ($date->isToday()) {
                                                    return 'Today';
                                                } elseif ($date->isYesterday()) {
                                                    return 'Yesterday';
                                                } elseif ($date->greaterThanOrEqualTo(Carbon\Carbon::now()->startOfWeek())) {
                                                    return 'This Week';
                                                } else {
                                                    return 'Earlier';
                                                }
                                            });
                                        @endphp

                                        @if($filtered->isEmpty())
                                            <p class="text-muted">No {{ $notification_type->name }} notifications.</p>
                                        @else
                                            @foreach($groupedFiltered as $label => $group)
                                                <h6 class="date-label mb-3 mt-4">{{ $label }}</h6>
                                                @foreach($group as $notification)
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
                                                                {{ Carbon\Carbon::parse($notification->created_at)->format('d M Y · h:i A') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endforeach
                                        @endif
                                    </div>
                                @endforeach

                            </div>

                        </div> 
                    </div> 
                </div> 
            </div> 
        </div> 
    </div> 
</div> 
@endsection