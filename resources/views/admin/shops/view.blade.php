@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Shop</title>
@endsection

@section('body')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <p class="card-title mb-0"> <img src="{{ asset('storage/' . $user->logo) }}" class="logo-dark me-1" alt="user-profile-image" height="24">{{$user->name}}</p>
                    </div>
                    <div class="d-flex gap-3">
                        @if($user->is_delete == 0)

                            @if($user->is_lock == 0)
                                <a href="{{route('admin.shop.lock', ['id' => $user->id])}}" class="link-success"><i class="ri-lock-unlock-line align-middle fs-20"></i> Shop Active</a>
                            @else
                                <a href="{{route('admin.shop.lock', ['id' => $user->id])}}" class="link-warning"><i class="ri-lock-line align-middle fs-20"></i> Shop Locked</a>
                            @endif
                        @else
                            <a href="#!" class="link-danger"><i class="ri-delete-bin-5-line align-middle fs-20"></i> Deleted </a>   
                        @endif
                        
                        <a href="{{route('admin.shop.edit', ['id' => $user->id])}}" class="link-dark"><i class="ri-edit-line align-middle fs-20"></i>Edit Shop</a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
	<div class="row">
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Shop Info</h4>
                </div>
                <div class="card-body">
                    <div class="pb-3 border-bottom">
                        <h5 class="text-dark fs-12 text-uppercase fw-bold">Shop Name :</h5>
                        <p class="fw-medium mb-0">{{$user->name}}</p>
                    </div>
                    <div class="py-3 border-bottom">
                        <h5 class="text-dark fs-12 text-uppercase fw-bold">Slug Name :</h5>
                        <p class="fw-medium mb-0">{{$user->user_name}}</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Phone Number :</h5>
                            <p class="fw-medium mb-0">{{$user->phone}} @if($user->alt_phone != null) | {{$user->alt_phone}} @endif</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Address :</h5>
                            <p class="fw-medium mb-0">@if($user->user_detail->address != null) {{$user->user_detail->address}} @else - @endif</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Email :</h5>
                            <p class="fw-medium mb-0">@if($user->email != null) {{$user->email}} @else - @endif</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Company GSTin :</h5>
                            <p class="fw-medium mb-0">@if($user->user_detail->gst != null) {{$user->user_detail->gst}} @else - @endif</p>
                    </div>
                    <div class="py-3 border-bottom">
                        <h5 class="text-dark fs-12 text-uppercase fw-bold">Payment Method:</h5>
                        @if($user->user_detail->payment_method == 1)
                            <span class="badge bg-soft-primary text-primary">Monthly</span>
                        @elseif($user->user_detail->payment_method == 2)
                            <span class="badge bg-soft-primary text-primary">Quarterly</span>
                        @elseif($user->user_detail->payment_method == 3)
                            <span class="badge bg-soft-primary text-primary">Semi-Yearly</span>
                        @elseif($user->user_detail->payment_method == 4)
                            <span class="badge bg-soft-primary text-primary">Yearly</span>
                        @else
                            -
                        @endif
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Payment Date:</h5>
                            <p class="fw-medium mb-0">@if($user->user_detail->payment_date != null) {{ \Carbon\Carbon::parse($user->user_detail->payment_date)->format('d M Y') }} @else - @endif</p>
                    </div>

                    @php
                        use Carbon\Carbon;

                        $paymentDate = Carbon::parse($user->user_detail->payment_date);
                        $paymentMethod = $user->user_detail->payment_method;

                        switch ($paymentMethod) {
                            case 1:
                                $nextPaymentDate = $paymentDate->copy()->addMonth();
                                break;
                            case 2:
                                $nextPaymentDate = $paymentDate->copy()->addMonths(3);
                                break;
                            case 3:
                                $nextPaymentDate = $paymentDate->copy()->addMonths(6);
                                break;
                            case 4:
                                $nextPaymentDate = $paymentDate->copy()->addYear();
                                break;
                            default:
                                $nextPaymentDate = null;
                        }
                    @endphp

                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Next Payment Date:</h5>
                            <p class="fw-medium mb-0">{{ $nextPaymentDate ? $nextPaymentDate->format('d M Y') : '-' }}</p>
                    </div>

                    <div class="pt-3">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Primary Color :</h5>
                            <p class="fw-medium mb-0">@if($user->user_detail->primary_colour != null) {{$user->user_detail->primary_colour}} @else - @endif</p>
                    </div>
                    <div class="pt-3">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Secondary Color :</h5>
                            <p class="fw-medium mb-0">@if($user->user_detail->secondary_colour != null) {{$user->user_detail->secondary_colour}} @else - @endif</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Bank Info</h4>
                    <div class="d-flex gap-3">
                        <a class="link-dark"  data-toast data-toast-text="Bank Details Copied Successfully!" data-toast-gravity="bottom" data-toast-position="center" data-toast-duration="3000" data-toast-close="close" ><i class="ri-file-copy-line align-middle fs-14"></i> Copy</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="">
                        <p class="fw-medium mb-0">@if($user->bank_detail->name != null) {{$user->bank_detail->name}} @else - @endif</p>
                        <p class="fw-medium mb-0">@if($user->bank_detail->branch != null) {{$user->bank_detail->branch}} @else - @endif</p>
                        <p class="fw-medium mb-0">@if($user->bank_detail->account_no != null) {{$user->bank_detail->account_no}} @else - @endif</p>
                        <p class="fw-medium mb-0">@if($user->bank_detail->ifsc_code != null) {{$user->bank_detail->ifsc_code}} @else - @endif</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection