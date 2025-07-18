@extends('layouts.login')

@section('title')
	<title>{{ config('app.name')}} | Vasantham Login</title>
@endsection

@section('body')
<div class="container-fluid login-container">
  <div class="row h-100">
    <!-- Left Side Logo -->
    <div class="col-md-6 logo-section text-center" style="background: #ccc;">
      <img src="{{asset('assets/images/company/vasantham/logo.png')}}" alt="user-image" class="img-fluid user-avtar">
    </div>
    <!-- Right Side Login Form -->
    <div class="col-md-6 d-flex align-items-center justify-content-center">
      <div class="w-75">
        <h2 class="mb-4 text-center">Login</h2>
        <form>
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" placeholder="Enter your email">
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" placeholder="Enter your password">
          </div>
          <button type="submit" class="btn btn-primary w-100 border-0">Login</button>
          <div class="text-center mt-3">
            <a href="#">Forgot password?</a>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>
@endsection