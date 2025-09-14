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
                                <a href="{{route('admin.shop.lock', ['id' => $user->id])}}" class="link-success" onclick="return confirm('Are you sure you want to change the shop status?')"><i class="ri-lock-unlock-line align-middle fs-20"></i> Shop Active</a>
                            @else
                                <a href="{{route('admin.shop.lock', ['id' => $user->id])}}" class="link-warning" onclick="return confirm('Are you sure you want to change the shop status?')"><i class="ri-lock-line align-middle fs-20"></i> Shop Locked</a>
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
        
        <div class="col-md-4">
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
                        <p class="fw-medium mb-0">{{$user->slug_name}}</p>
                    </div>
                    <div class="py-3 border-bottom">
                        <h5 class="text-dark fs-12 text-uppercase fw-bold">User Name :</h5>
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
        <div class="col-md-8">
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
            <div class="card">




                <div class="card-header d-flex justify-content-between align-items-center">
                    @php
                        $branch_count = App\Models\User::where('parent_id', $user->id)->count();
                    @endphp
                    <div>
                        <h4 class="card-title mb-0">Branches <span class="badge bg-success badge-pill text-end">{{$branch_count}}</span></h4>
                    </div>
                    <a href="{{route('admin.branch.create', ['id' => $user->id])}}" class="link-dark"><i class="ri-add-circle-line align-middle fs-20"></i>Add New Branch</a>
                </div>

                <form method="get" action="{{route('admin.shop.view', ['id' => $user->id])}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-10">
                            <div class="input-group">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Name/ User Name/ Slug Name/ Phone" name="branch" value="{{ request('branch') }}">
                                <span class="input-group-text"><a href="{{route('admin.shop.view', ['id' => $user->id])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-primary"> Search </button>
                        </div>
                    </div>
                </form>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover table-centered">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th>Image</th>
                                    <th>Branch Name</th>
                                    <th>Slug Name</th>
                                    <th>User Name</th>
                                    <th>Mobile Number</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branches as $branch)
                                    <tr>
                                        <td>
                                            <img src="{{ asset('storage/'.$branch->logo) }}" class="logo-dark me-1" alt="Branch" height="30">
                                        </td>
                                        <td>{{$branch->name}}</td>
                                        <td>{{$branch->slug_name}}</td>
                                        <td>{{$branch->user_name}}</td>
                                        <td>{{$branch->phone}}</td>
                                        <td>
                                            @if($branch->is_lock == 1)
                                                <span class="badge bg-soft-danger text-danger">Locked</span>
                                            @elseif($branch->is_delete == 1)
                                                <span class="badge bg-soft-danger text-danger">Deleted</span>
                                            @elseif($branch->is_active == 0)
                                                <span class="badge bg-soft-danger text-danger">In-active</span>
                                            @else
                                                <span class="badge bg-soft-success text-success">Active</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-3">
                                                <a href="{{route('admin.branch.view', ['id' => $branch->id])}}" class="text-muted"><i class="ri-eye-line align-middle fs-20"></i></a>
                                                <a href="{{route('admin.branch.edit', ['id' => $branch->id])}}" class="link-dark"><i class="ri-edit-line align-middle fs-20"></i></a>

                                                @if($branch->is_delete == 0)
                                                <a href="{{route('admin.branch.delete', ['id' => $branch->id])}}" class="link-danger"  onclick="return confirm('Are you sure you want to delete this branch?');"><i class="ri-delete-bin-5-line align-middle fs-20"></i></a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-0">
                        {!! $branches->withQueryString()->links('pagination::bootstrap-5') !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="{{asset('assets/js/admins/shop.js')}}"></script>
@endsection