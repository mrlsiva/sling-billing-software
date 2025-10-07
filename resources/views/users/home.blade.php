@extends('layouts.landing')

@section('title')
	<title>{{ config('app.name')}} | Home</title>
@endsection

@section('body')

  <!-- Header -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">{{$user->name}}</a>
        @if($user->parent_id == null)
            <a href="{{ route('login', ['company' => request()->route('company')]) }}" class="btn btn-warning btn-md ">HO Login</a>
        @else
            <a href="{{ route('login', ['company' => request()->route('company')]) }}" class="btn btn-warning btn-md ">Branch Login</a>
        @endif
    </div>
  </nav>
<!-- Hero Section -->
<section class="hero animate__animated animate__fadeIn py-5" 
    style="background: linear-gradient(135deg, 
        {{ $user->user_detail->primary_colour ?? '#000000' }}, 
        {{ $user->user_detail->secondary_colour ?? '#1B1E2C' }} );">
  <div class="container">
    <h1 class="display-4 fw-bold text-white">{{$user->name}} Billing Software</h1>
    <p class="lead text-white">{{$user->phone}} | {{$user->email}} | {{$user->user_detail->address}}</p>
    <!-- <a href="{{ route('login', ['company' => request()->route('company')]) }}" class="btn btn-warning btn-lg mt-3">Login</a> -->
  </div>
</section>
<section class="hero animate__animated animate__fadeIn py-5">
  <div class="container">
    <div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<p class="card-title mb-0">Welcome to {{$user->name}}</p>
				</div>
			</div>
		</div>
	</div>
</div>
    <div class="row">
        @php
            $branches = App\Models\User::with(['user_detail', 'bank_detail'])->where('parent_id', $user->id)->get();
        @endphp

        @foreach($branches as $branch)
            <div class="col-md-3 col-md-3">
                <div class="card">
                    <div class="card-body pt-3 pb-2 px-4">
                        <div class="d-flex align-items-center justify-content-between w-100">
                        <h4 class="fw-bold d-flex align-items-center gap-2 mb-0"><img src="{{ asset('storage/' . $user->logo) }}" class="logo-dark me-1" alt="Branch" height="30"></h4>
                        <div class="d-flex flex-column align-items-center justify-content-center  gap-2">
                            <div class="d-flex align-items-center gap-2 pt-2">
                                <div class="box" style="background-color: {{$user->user_detail->primary_colour}};"></div>
                                <div class="box" style="background-color: {{$user->user_detail->secondary_colour}};"></div>
                            </div>
                        </div> 
                    </div>

                    <div class="card-footer border p-3 pt-2">
                        <div class="d-flex align-items-center justify-content-between w-100">
                             <div class="w-100 text-center my-3">
                                <p class="mb-0 card-title">{{$user->name}}</p> 
                            </div>
                        </div>
                        <div class="mt-3 w-100">
                            <a href="{{route('login', ['company' => request()->route('company')])}}" class="btn btn-warning btn-xs w-100">Login</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
  </div>
</section>
@endsection
<div class="card" style="display:none">
    <a href="{{ route('login', ['company' => request()->route('company')]) }}">
        <button type="button" class="btn btn-primary w-100"><i class="ri-save-line"></i> Login</button></a>
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
            <p class="fw-medium mb-0">@if($user->user_detail->address != null) {{$user->user_detail->address}} @else -
                @endif</p>
        </div>
        <div class="py-3 border-bottom">
            <h5 class="text-dark fs-12 text-uppercase fw-bold">Email :</h5>
            <p class="fw-medium mb-0">@if($user->email != null) {{$user->email}} @else - @endif</p>
        </div>
        <div class="py-3 border-bottom">
            <h5 class="text-dark fs-12 text-uppercase fw-bold">Company GSTin :</h5>
            <p class="fw-medium mb-0">@if($user->user_detail->gst != null) {{$user->user_detail->gst}} @else - @endif
            </p>
        </div>
        <div class="pt-3">
            <h5 class="text-dark fs-12 text-uppercase fw-bold">Primary Color :</h5>
            <p class="fw-medium mb-0">@if($user->user_detail->primary_colour != null)
                {{$user->user_detail->primary_colour}} @else - @endif</p>
        </div>
        <div class="pt-3">
            <h5 class="text-dark fs-12 text-uppercase fw-bold">Secondary Color :</h5>
            <p class="fw-medium mb-0">@if($user->user_detail->secondary_colour != null)
                {{$user->user_detail->secondary_colour}} @else - @endif</p>
        </div>

        <div class="" id="bank-details">
            <p class="fw-medium mb-0">@if($user->bank_detail->name != null) {{$user->bank_detail->name}} @else - @endif
            </p>
            <p class="fw-medium mb-0">@if($user->bank_detail->holder_name != null) {{$user->bank_detail->holder_name}}
                @else - @endif</p>
            <p class="fw-medium mb-0">@if($user->bank_detail->branch != null) {{$user->bank_detail->branch}} @else -
                @endif</p>
            <p class="fw-medium mb-0">@if($user->bank_detail->account_no != null) {{$user->bank_detail->account_no}}
                @else - @endif</p>
            <p class="fw-medium mb-0">@if($user->bank_detail->ifsc_code != null) {{$user->bank_detail->ifsc_code}} @else
                - @endif</p>
        </div>

    </div>
</div>