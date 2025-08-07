@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Branch</title>
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
                                <a href="{{route('admin.branch.lock', ['id' => $user->id])}}" class="link-success" onclick="return confirm('Are you sure you want to change the branch status?')"><i class="ri-lock-unlock-line align-middle fs-20"></i> Branch Active</a>
                            @else
                                <a href="{{route('admin.branch.lock', ['id' => $user->id])}}" class="link-warning" onclick="return confirm('Are you sure you want to change the branch status?')"><i class="ri-lock-line align-middle fs-20"></i> Branch Locked</a>
                            @endif
                        @else
                            <a href="#!" class="link-danger"><i class="ri-delete-bin-5-line align-middle fs-20"></i> Deleted </a>   
                        @endif
                        
                        <!-- <a href="{{route('admin.branch.edit', ['id' => $user->id])}}" class="link-dark"><i class="ri-edit-line align-middle fs-20"></i>Edit Branch</a> -->

                        <a href="{{route('admin.shop.view', ['id' => $user->parent_id])}}" class="link-dark"><i class="ri-arrow-go-back-fill align-middle fs-20"></i>Back</a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
	<div class="row">
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Branch Info</h4>
                </div>
                <div class="card-body">
                    <div class="pb-3 border-bottom">
                        <h5 class="text-dark fs-12 text-uppercase fw-bold">Branch Name :</h5>
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
                        <a class="link-dark"  data-toast data-toast-text="Bank Details Copied Successfully!" data-toast-gravity="bottom" data-toast-position="center" data-toast-duration="3000" data-toast-close="close" href="javascript:void(0);" onclick="copyBankDetails()" ><i class="ri-file-copy-line align-middle fs-14"></i> Copy</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="" id="bank-details">
                        <p class="fw-medium mb-0">@if($user->bank_detail->name != null) {{$user->bank_detail->name}} @else - @endif</p>
                        <p class="fw-medium mb-0">@if($user->bank_detail->holder_name != null) {{$user->bank_detail->holder_name}} @else - @endif</p>
                        <p class="fw-medium mb-0">@if($user->bank_detail->branch != null) {{$user->bank_detail->branch}} @else - @endif</p>
                        <p class="fw-medium mb-0">@if($user->bank_detail->account_no != null) {{$user->bank_detail->account_no}} @else - @endif</p>
                        <p class="fw-medium mb-0">@if($user->bank_detail->ifsc_code != null) {{$user->bank_detail->ifsc_code}} @else - @endif</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection