@extends('layouts.landing')

@section('title')
	<title>{{ config('app.name')}} | Home</title>
@endsection

@section('body')

  <!-- Header -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">Sling Billing Software</a>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <h1>All-in-One Business Management</h1>
    <p>Billing, Inventory, Online Orders & Reporting – everything you need in one software</p>
    <a href="#features" class="btn btn-light mt-3">Explore Features</a>
  </section>

  <!-- Features Section -->
  <section id="features" class="py-5">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold">Our Core Features</h2>
        <p class="text-muted">Manage your business seamlessly with these powerful modules</p>
      </div>

      <div class="row g-4">

        <!-- Billing Feature -->
        <div class="col-md-3">
          <div class="card feature-card text-center p-4">
            <div class="feature-icon">
              💳
            </div>
            <h5 class="card-title">Billing</h5>
            <p class="text-muted small">
              Create professional invoices, track payments, and manage customer billing with ease.
            </p>
          </div>
        </div>

        <!-- Inventory Feature -->
        <div class="col-md-3">
          <div class="card feature-card text-center p-4">
            <div class="feature-icon">
              📦
            </div>
            <h5 class="card-title">Inventory</h5>
            <p class="text-muted small">
              Monitor stock levels, track products, and receive low-stock alerts automatically.
            </p>
          </div>
        </div>

        <!-- Online Order Feature -->
        <div class="col-md-3">
          <div class="card feature-card text-center p-4">
            <div class="feature-icon">
              🛒
            </div>
            <h5 class="card-title">Online Orders</h5>
            <p class="text-muted small">
              Accept online orders, sync them with inventory, and process them quickly.
            </p>
          </div>
        </div>

        <!-- Reporting Feature -->
        <div class="col-md-3">
          <div class="card feature-card text-center p-4">
            <div class="feature-icon">
              📊
            </div>
            <h5 class="card-title">Reporting</h5>
            <p class="text-muted small">
              Get detailed sales, tax, and stock reports to make informed business decisions.
            </p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- Call to Action -->
  <section class="py-5 bg-light text-center">
    <div class="container">
      <h3 class="fw-bold">Ready to Simplify Your Business?</h3>
      <p class="text-muted">Start managing billing, inventory, and reporting effortlessly.</p>
      <a href="#" class="btn btn-success btn-lg mt-2">Get Started</a>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Sling Billing Software. All rights reserved.</p>
  </footer>
@endsection