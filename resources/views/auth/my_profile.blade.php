@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Dashboard</title>
@endsection

@section('body')
<div class="row">
    <div class="col-lg-12">
        <!-- {{$user}} -->
        <div class="card overflow-hidden">
            <div class="card-body p-0">
                <div class="bg-primary profile-bg rounded-top position-relative"
                    style="    background-image: url(/assets/images/profile-bg-2.jpg);">
                    <img src="{{ asset('storage/' . $user->logo) }}" alt=""
                        class="avatar-xl mx-auto border border-light border-3 rounded-circle position-absolute top-100 start-50 translate-middle">
                </div>
                <div class="mt-3 px-4 d-flex flex-wrap align-items-end justify-content-between">
                    <div>
                        <h4 class="mb-1 fw-semibold">{{$user->name}} <i
                                class="bx bxs-badge-check text-success align-middle"></i></h4>
                        <p class="mb-4">{{$user->email}}</p>
                        <p class="text-muted fw-medium mb-2 d-flex align-items-start gap-2"><span
                                class="text-dark fs-12 fw-bold text-uppercase d-flex align-items-center gap-1">Mobile:
                            </span> {{$user->phone}} </p>
                        <p class="text-muted fw-medium mb-3 d-flex align-items-start gap-2"><span
                                class="text-dark fs-12 fw-bold text-uppercase d-flex align-items-center gap-1">Location:
                            </span> {{$user->user_detail->address}} </p>
                    </div>
                    <div>
                        <div class="row text-center g-2 mb-4 mobile-width-profile" style="width: 500px;">
                            <div class="col-lg-3 col-4 border-end">
                                <h5 class="mb-1 fw-bold">80</h5>
                                <p class="text-muted mb-0">Posts</p>
                            </div>
                            <div class="col-lg-3 col-4 border-end">
                                <h5 class="mb-1 fw-bold">3.6k</h5>
                                <p class="text-muted mb-0">Followers</p>
                            </div>
                            <div class="col-lg-3 col-4 border-end">
                                <h5 class="mb-1 fw-bold">1.1k</h5>
                                <p class="text-muted mb-0">Following</p>
                            </div>
                            <div class="col-lg-3 col-4">
                                <h5 class="mb-1 fw-bold">6.7k</h5>
                                <p class="text-muted mb-0">Views</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="row">
    <div class="col-xl-4 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Personal Info</h4>
            </div>
            <div class="card-body">
                <div class="pb-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">id :</h5>
                    <p class="fw-medium mb-0">{{$user->id}}</p>
                </div>
                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">role_id : </h5>
                    <p class="fw-medium mb-0">{{$user->role_id}}</p>
                </div>
                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Parent ID :</h5>
                    <p class="fw-medium mb-0">{{ $user->parent_id }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Unique ID :</h5>
                    <p class="fw-medium mb-0">{{ $user->unique_id }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Name :</h5>
                    <p class="fw-medium mb-0">{{ $user->name }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">User Name :</h5>
                    <p class="fw-medium mb-0">{{ $user->user_name }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Slug Name :</h5>
                    <p class="fw-medium mb-0">{{ $user->slug_name }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Email :</h5>
                    <p class="fw-medium mb-0">{{ $user->email }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Email Verified At :</h5>
                    <p class="fw-medium mb-0">{{ $user->email_verified_at }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Phone :</h5>
                    <p class="fw-medium mb-0">{{ $user->phone }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Alternate Phone :</h5>
                    <p class="fw-medium mb-0">{{ $user->alt_phone }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Logo :</h5>
                    <p class="fw-medium mb-0">
                        <img src="{{ asset($user->logo) }}" alt="User Logo" class="img-fluid" style="max-height: 80px;">
                    </p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Is Active :</h5>
                    <p class="fw-medium mb-0">{{ $user->is_active ? 'Yes' : 'No' }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Is Lock :</h5>
                    <p class="fw-medium mb-0">{{ $user->is_lock ? 'Yes' : 'No' }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Is Delete :</h5>
                    <p class="fw-medium mb-0">{{ $user->is_delete ? 'Yes' : 'No' }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Created By :</h5>
                    <p class="fw-medium mb-0">{{ $user->created_by }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Created At :</h5>
                    <p class="fw-medium mb-0">{{ $user->created_at }}</p>
                </div>

                <div class="py-3">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Updated At :</h5>
                    <p class="fw-medium mb-0">{{ $user->updated_at }}</p>
                </div>

            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">User Detail</h4>
            </div>
            <div class="card-body">

                <div class="pb-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Address :</h5>
                    <p class="fw-medium mb-0">{{ $user->user_detail->address }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">GST :</h5>
                    <p class="fw-medium mb-0">{{ $user->user_detail->gst }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Payment Method :</h5>
                    <p class="fw-medium mb-0">{{ $user->user_detail->payment_method }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Payment Date :</h5>
                    <p class="fw-medium mb-0">{{ $user->user_detail->payment_date }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Primary Colour :</h5>
                    <p class="fw-medium mb-0">
                        <span class="badge text-white"
                            style="background-color: {{ $user->user_detail->primary_colour }}">{{ $user->user_detail->primary_colour }}</span>
                    </p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Secondary Colour :</h5>
                    <p class="fw-medium mb-0">
                        <span class="badge text-white"
                            style="background-color: {{ $user->user_detail->secondary_colour }}">{{ $user->user_detail->secondary_colour }}</span>
                    </p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Is Scan Available :</h5>
                    <p class="fw-medium mb-0">{{ $user->user_detail->is_scan_avaiable ? 'Yes' : 'No' }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Created At :</h5>
                    <p class="fw-medium mb-0">{{ $user->user_detail->created_at }}</p>
                </div>

                <div class="py-3">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Updated At :</h5>
                    <p class="fw-medium mb-0">{{ $user->user_detail->updated_at }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Bank Detail</h4>
            </div>
            <div class="card-body">
                <div class="pb-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Bank Name :</h5>
                    <p class="fw-medium mb-0">{{ $user->bank_detail->name }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Holder Name :</h5>
                    <p class="fw-medium mb-0">{{ $user->bank_detail->holder_name }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Branch :</h5>
                    <p class="fw-medium mb-0">{{ $user->bank_detail->branch }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Account Number :</h5>
                    <p class="fw-medium mb-0">{{ $user->bank_detail->account_no }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">IFSC Code :</h5>
                    <p class="fw-medium mb-0">{{ $user->bank_detail->ifsc_code }}</p>
                </div>

                <div class="py-3 border-bottom">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Created At :</h5>
                    <p class="fw-medium mb-0">{{ $user->bank_detail->created_at }}</p>
                </div>

                <div class="py-3">
                    <h5 class="text-dark fs-12 text-uppercase fw-bold">Updated At :</h5>
                    <p class="fw-medium mb-0">{{ $user->bank_detail->updated_at }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection