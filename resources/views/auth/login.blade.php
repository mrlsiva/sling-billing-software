@extends('layouts.login')

@section('title')
	<title>{{ config('app.name')}} | Login</title>
@endsection

@section('body')
  <div class="container-fluid login-container">
    <div class="row h-100">
      <!-- Left Side Logo -->
      @if(request()->segment(1) === 'admin') 
        <div class="col-md-6 logo-section text-center"  style="background: #080e1c;">
          <img src="{{asset('assets/images/sling-logo.png')}}" alt="user-image" class="img-fluid user-avtar">
        </div>
      @else
        <div class="col-md-6 logo-section text-center" style="background: #ccc;">
          <img src="{{asset('assets/images/company/vasantham/logo.png')}}" alt="user-image" class="img-fluid user-avtar">
        </div>
      @endif
      <!-- Right Side Login Form -->
      <div class="col-md-6 d-flex align-items-center justify-content-center">
        <div class="w-75">

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

          <h2 class="mb-4 text-center">Login</h2>
          <form class="row" action="{{route('sign_in')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label for="email" class="form-label">Email address</label>
              <input type="email" class="form-control" name="email" placeholder="Enter your email" required="">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" name="password" placeholder="Enter your password" required="">
            </div>
            <button type="submit" class="btn btn-primary w-100 border-0">Login</button>
          </form>
          <div class="text-center mt-3">
            <a href="#">Forgot password?</a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection