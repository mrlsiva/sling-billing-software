@extends('layouts.landing')

@section('title')
	<title>{{ config('app.name')}} | Home</title>
@endsection

@section('body')
 <div class="d-flex align-items-center justify-content-center vh-100">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-2"></div>
        <div class="col-lg-8">
          <div class="p-4 p-md-5 card-plain glass rounded-3">
            <div class="d-flex align-items-center mb-3">
              <div class="brand-logo me-3"><img src="https://slinggroups.in/img/Share.png" width="68px"></div>
              <div>
                <h1 class="h4 mb-0" style="color:var(--brand-1)">Sling Billing</h1>
                <small class="text-white-50">Billing software · POS · Inventory</small>
              </div>
            </div>

            <h2 class="display-6 fw-bold">Our app is <span style="color:var(--brand-2)">coming soon</span></h2>
            <p class="lead text-white-50">We're building a faster, simpler billing experience for small businesses. Join the waitlist to get early access, exclusive discounts, and product updates.</p>

            <div class="row gy-3 align-items-center">
              <div class="col-12 col-sm-12">
                <form id="subscribeForm" class="d-flex" onsubmit="subscribe(event)">
                  <input id="emailInput" type="email" class="form-control me-2" placeholder="Enter your email" aria-label="Email" required>
                  <button class="btn btn-light" type="submit">Request Demo</button>
                </form>
                <div id="msg" class="mt-2 small text-success" style="display:none;">Thanks — we'll be in touch!</div>
              </div>
              
            </div>

            <hr class="my-4" style="border-color:rgba(255,255,255,0.06)">

            <div class="d-flex align-items-center justify-content-between flex-wrap">
              <div class="d-flex countdown text-center">
                <div class="unit me-3">
                  <div class="h3 mb-0" id="days">00</div>
                  <small class="text-white-50">Days</small>
                </div>
                <div class="unit me-3">
                  <div class="h3 mb-0" id="hours">00</div>
                  <small class="text-white-50">Hours</small>
                </div>
                <div class="unit me-3">
                  <div class="h3 mb-0" id="minutes">00</div>
                  <small class="text-white-50">Mins</small>
                </div>
                <div class="unit">
                  <div class="h3 mb-0" id="seconds">00</div>
                  <small class="text-white-50">Secs</small>
                </div>
              </div>

              <div class="social mt-3 mt-sm-0">
                <a href="#" aria-label="twitter"><i class="bi bi-twitter"></i></a>
                <a href="#" aria-label="linkedin"><i class="bi bi-linkedin"></i></a>
                <a href="#" aria-label="facebook"><i class="bi bi-facebook"></i></a>
                <!-- <a href="#" aria-label="youtube"><i class="bi bi-youtube"></i></a> -->
                <!-- <a href="#" aria-label="github"><i class="bi bi-github"></i></a> -->
              </div>
            </div>

          </div>
        </div>

        <div class="col-lg-2 mt-4 mt-lg-0 text-center text-lg-start">

        </div>
      </div>

      <div class="mt-4 text-center text-white-50 small">© <span id="year"></span> Sling Groups · All rights reserved</div>
    </div>
  </div>
@endsection