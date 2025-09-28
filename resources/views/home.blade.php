@extends('layouts.landing')

@section('title')
	<title>{{ config('app.name')}} | Home</title>
@endsection

@section('body')

  <!-- Header -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">Sling Billing Software</a>
    <a href="{{ route('admin.login') }}" class="btn btn-warning btn-md ">Admin</a>

    </div>
  </nav>
<!-- Hero Section -->
<section class="hero animate__animated animate__fadeIn">
  <div class="container">
    <h1 class="display-4 fw-bold">Sling Billing Software</h1>
    <p class="lead">Simple • Smart • Secure Billing for Your Business</p>
    <a href="#plans" class="btn btn-warning btn-lg mt-3">Start Free Trial</a>
  </div>
</section>
<!-- Features -->
<section class="py-5">
  <div class="container">
    <h2 class="text-center mb-5 animate__animated animate__fadeInUp">Core Features</h2>
    <div class="row g-4">
      <div class="col-md-4 animate__animated animate__zoomIn">
        <div class="card h-100 shadow-sm p-4">
          <div class="feature-icon mb-3">📄</div>
          <h5>GST‑Compliant Invoicing</h5>
          <p>Create professional invoices in seconds with templates and branding.</p>
        </div>
      </div>
      <div class="col-md-4 animate__animated animate__zoomIn animate__delay-1s">
        <div class="card h-100 shadow-sm p-4">
          <div class="feature-icon mb-3">📦</div>
          <h5>Inventory Management</h5>
          <p>Track stock in real time, get low‑stock alerts, and barcode support.</p>
        </div>
      </div>
      <div class="col-md-4 animate__animated animate__zoomIn animate__delay-2s">
        <div class="card h-100 shadow-sm p-4">
          <div class="feature-icon mb-3">💳</div>
          <h5>Payment Tracking</h5>
          <p>Monitor dues, send reminders, and improve cash flow effortlessly.</p>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Why Choose -->
<section class="py-5 bg-light">
  <div class="container">
    <h2 class="text-center mb-5 animate__animated animate__fadeIn">Why Choose Sling?</h2>
    <div class="row text-center">
      <div class="col-md-3 animate__animated animate__fadeInUp">
        <h5>🇮🇳 Designed for SMBs</h5>
        <p>Localized for GST, languages, and business needs.</p>
      </div>
      <div class="col-md-3 animate__animated animate__fadeInUp animate__delay-1s">
        <h5>💸 Flexible Pricing</h5>
        <p>Free mobile plan to scalable enterprise options.</p>
      </div>
      <div class="col-md-3 animate__animated animate__fadeInUp animate__delay-2s">
        <h5>🔒 Secure & Reliable</h5>
        <p>Daily backup, encryption, and 24/7 support.</p>
      </div>
      <div class="col-md-3 animate__animated animate__fadeInUp animate__delay-3s">
        <h5>👌 Easy to Use</h5>
        <p>No accounting degree required—intuitive UI.</p>
      </div>
    </div>
  </div>
</section>
<!-- Plans -->
<section id="plans" class="py-5">
  <div class="container">
    <h2 class="text-center mb-5 animate__animated animate__fadeIn">Plans at a Glance</h2>
    <div class="row g-4">
      <div class="col-md-3 animate__animated animate__fadeInUp">
        <div class="card pricing-card shadow-sm text-center p-4">
          <h5>Free Mobile</h5>
          <p>Best for freelancers & kirana shops.</p>
          <p class="fw-bold">₹0</p>
          <a href="#" class="btn btn-outline-primary">Get Started</a>
        </div>
      </div>
      <div class="col-md-3 animate__animated animate__fadeInUp animate__delay-1s">
        <div class="card pricing-card shadow-sm text-center p-4">
          <h5>Standard</h5>
          <p>Best for small traders.</p>
          <p class="fw-bold">₹999/year</p>
          <a href="#" class="btn btn-outline-primary">Choose Plan</a>
        </div>
      </div>
      <div class="col-md-3 animate__animated animate__fadeInUp animate__delay-2s">
        <div class="card pricing-card shadow-sm text-center p-4">
          <h5>Pro</h5>
          <p>For growing SMBs.</p>
          <p class="fw-bold">₹2499/year</p>
          <a href="#" class="btn btn-primary">Choose Plan</a>
        </div>
      </div>
      <div class="col-md-3 animate__animated animate__fadeInUp animate__delay-3s">
        <div class="card pricing-card shadow-sm text-center p-4">
          <h5>Enterprise</h5>
          <p>For multi‑location businesses.</p>
          <p class="fw-bold">Custom</p>
          <a href="#" class="btn btn-outline-primary">Contact Us</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials -->
<section class="py-5 bg-light">
  <div class="container">
    <h2 class="text-center mb-5 animate__animated animate__fadeIn">What Our Customers Say</h2>
    <div class="row g-4">
      <div class="col-md-4 animate__animated animate__fadeInLeft">
        <div class="card shadow-sm p-4 h-100">
          <p>“Sling streamlined billing across outlets—real‑time visibility on receivables.”</p>
          <h6>- Retail Chain Owner</h6>
        </div>
      </div>
      <div class="col-md-4 animate__animated animate__fadeInUp">
        <div class="card shadow-sm p-4 h-100">
          <p>“Inventory restocks instantly after each invoice—no more stockouts!”</p>
          <h6>- Wholesaler</h6>
        </div>
      </div>
      <div class="col-md-4 animate__animated animate__fadeInRight">
        <div class="card shadow-sm p-4 h-100">
          <p>“Automated filing‑ready GST reports saved us hours every month.”</p>
          <h6>- Small Business Owner</h6>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="py-5 text-center">
  <div class="container">
    <h2 class="animate__animated animate__fadeIn">Ready to Simplify Your Billing?</h2>
    <p class="lead">Start your free trial now—or book a personalized demo today!</p>
    <a href="#" class="btn btn-lg btn-success animate__animated animate__pulse animate__infinite">Get Started Free</a>
  </div>
</section>
  <!-- Footer -->
  <footer>
    <div class="container">
        <p>&copy; 2025 Sling Billing Software. All rights reserved.</p>
  </div>
  </footer>
@endsection