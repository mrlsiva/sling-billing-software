@extends('layouts.login')

@section('title')
<title>{{ config('app.name')}} | Login</title>
@endsection

@section('body')
<div class="container-fluid login-container">
  <div class="row h-100">
    <!-- Left Side Logo -->
    @if(request()->segment(1) === 'admin')
    <div class="col-md-6 logo-section text-center hide-mobile" style="background: #080e1c;" >
      <img src="{{asset('assets/images/sling-logo.png')}}" alt="user-image" class="img-fluid user-avtar">
    </div>
    @else
    @php
    $user = App\Models\User::where('slug_name',request()->segment(1))->first();
    @endphp
    <div class="col-md-6 logo-section text-center hide-mobile" style="background: #ccc;">
      <img src="{{ asset('storage/' . $user->logo) }}" alt="user-image" class="img-fluid user-avtar">
    </div>
    @endif
    <!-- Right Side Login Form -->
    <div class="col-md-6 d-flex align-items-center justify-content-center">
      <div class="w-75">
        <div class="hide-web right-logo text-center mb-5">
          @if(request()->segment(1) === 'admin')
            <img src="{{asset('assets/images/sling-logo.png')}}" alt="user-image" class="img-fluid user-avtar">
          @else
          @php
          $user = App\Models\User::where('slug_name',request()->segment(1))->first();
          @endphp
            <img src="{{ asset('storage/' . $user->logo) }}" alt="user-image" class="img-fluid user-avtar">
          @endif
        </div>
        @if ($errors->any())
        <div class="alert alert-danger">
          <strong>Whoops!</strong> There were some problems with your input.<br><br>
          <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif

        @if(session('error_alert'))
        <div class="alert alert-danger">
          <strong>Warning! </strong>{{ session('error_alert') }}<br>
        </div>
        @endif

        <h2 class="mb-4 text-center hide-mobile">Login</h2>
        @if(request()->segment(1) === 'admin')
        <form action="{{ route('admin.sign_in') }}" method="post" enctype="multipart/form-data">
          @else
          <form action="{{ route('sign_in', ['company' => request()->route('company')]) }}" method="post"
            enctype="multipart/form-data">
            @endif

            @csrf

            @if(request()->segment(1) === 'admin')
            <input type="hidden" name="slug_name" value="admin">
            @else
            <input type="hidden" name="slug_name" value="{{request()->route('company')}}">
            @endif

            <div class="mb-3">
              <label for="user_name" class="form-label">Username</label>
              <input type="text" class="form-control" name="user_name" placeholder="Enter your Username" required="">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" name="password" placeholder="Enter your password" required="">
            </div>
            <button type="submit" class="btn btn-primary w-100 border-0">Login</button>
          </form>
          <!-- <div class="text-center mt-3">
            <a href="#">Forgot password?</a>
          </div> -->
      </div>
    </div>
  </div>
</div>
@endsection