@extends('layouts.landing')

@section('title')
	<title>{{ config('app.name')}} | Home</title>
  <style>
    :root {
    --brand-1: #19ad9f; /* teal */
    --brand-2: #f8931d; /* orange */
    --brand-3: #1b1e2c; /* deep navy */
}
html,
body {
    height: 100%;
}
body {
    background: radial-gradient(
            1200px 600px at 10% 10%,
            rgba(25, 173, 159, 0.12),
            transparent
        ),
        radial-gradient(
            1000px 500px at 90% 90%,
            rgba(248, 147, 29, 0.1),
            transparent
        ),
        var(--brand-3) !important;
    color: #fff !important;
    font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto,
        "Helvetica Neue", Arial;
}
.brand-logo {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #06221b;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.45);
}
.card-plain {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
}
.countdown {
    letter-spacing: 0.03em;
}
.countdown .unit {
    min-width: 72px;
}
.glass {
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
.social a {
    color: rgba(255, 255, 255, 0.85);
    margin-right: 12px;
    font-size: 1.25rem;
}
@media (max-width: 576px) {
    .countdown .unit {
        min-width: 54px;
        padding: 0.4rem;
    }
}
    </style>
@endsection

@section('body')
 <div class="d-flex align-items-center justify-content-center vh-100">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-2"></div>
        <div class="col-lg-8">
          <div class="p-4 p-md-5 card-plain glass rounded-3">
            <div class="d-flex align-items-center mb-3">
              <div class="brand-logo me-3"><img src="{{ asset('assets/images/share.png') }}" width="68px"></div>
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