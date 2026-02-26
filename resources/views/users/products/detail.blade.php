@extends('layouts.master') 
@section('title') 
    <title>{{ config('app.name')}} | {{$product->name}}</title> 
@endsection
@section('body') 
    <div class="row"> 
        <div class="col-xl-12 col-md-12"> 
            <div class="card"> 
                <div class="card-body"> 
                    <div class="container py-4"> 
                        <div class="notification-header">{{ $product->name }}</div>

@foreach($timeline as $item)
    <div class="notification-item d-flex mb-3">
        <div class="notification-line me-3 
            @if($item['type'] == 'in') bg-success
            @elseif($item['type'] == 'out') bg-danger
            @else bg-primary
            @endif
        " style="width:4px;"></div>

        <div>
            <div class="notification-title">
                <span class="fw-semibold">
                    {{ $item['message'] }}
                </span>
            </div>
            <div class="notification-meta text-muted small">
                {{ \Carbon\Carbon::parse($item['date'])->format('d M Y h:i A') }}
            </div>
        </div>
    </div>
@endforeach
                    </div> 
                </div> 
            </div> 
        </div> 
    </div> 
</div> 
@endsection