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
        <a href="{{ route('login', ['company' => request()->route('company')]) }}" class="btn btn-warning btn-md ">HO
            Login</a>
        @else
        <a href="{{ route('login', ['company' => request()->route('company')]) }}"
            class="btn btn-warning btn-md ">Branch Login</a>
        @endif
    </div>
</nav>
<!-- Hero Section -->
<section class="hero animate__animated animate__fadeIn py-5" style="background: linear-gradient(135deg, 
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
            <div class="col-xl-12 mb-3">
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
                            <h4 class="fw-bold d-flex align-items-center gap-2 mb-0">
                                <img src="{{ asset('storage/' . $user->logo) }}" class="logo-dark me-1" alt="Branch" height="30">
                            </h4>
                            <div class="d-flex flex-column align-items-center justify-content-center  gap-2">
                                <div class="d-flex align-items-center gap-2 pt-2">
                                    <div class="box" style="background-color: {{$user->user_detail->primary_colour}};">
                                    </div>
                                    <div class="box"
                                        style="background-color: {{$user->user_detail->secondary_colour}};"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer border p-3 pt-2">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="w-100 text-center my-3">
                                <p class="mb-0 card-title">{{$branch->name}}</p>
                            </div>
                        </div>
                        <div class="mt-3 w-100">
                            <a href="{{ route('login', ['company' => $branch['slug_name']]) }}" class="btn btn-warning btn-xs w-100">Login</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection