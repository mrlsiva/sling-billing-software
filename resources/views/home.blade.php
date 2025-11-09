@extends('layouts.landing')

@section('title')
	<title>{{ config('app.name')}} | Home</title>
@endsection

@section('body')
  <!-- Header -->
  <header class="header">
    <div class="nav-container">
      <a href="#" class="logo">
        <img src="assets/images/sling-dark-logo.png" alt="Sling Logo" height="40">
      </a>
      <a href="#" class="nav-button">Free Trial</a>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-container">
      <div class="hero-content">
        <div class="hero-subtitle">
          • Simple • Smart • Secure Billing for Your Business
        </div>
        <h1 class="hero-title">
          <span class="highlight">Sling</span> Billing Software
        </h1>
        <p class="hero-description">
          Simple • Smart • Secure Billing for Your Business
        </p>
        <a href="#" class="cta-button">Free Trial</a>
      </div>
      <div class="hero-image1">
        <img src="assets/images/landing-page/banner.svg" alt="Billing Software Dashboard">
      </div>
    </div>
  </section>

  <!-- Core Features -->
  <section class="features">
    <div class="features-container">
      <h2 class="section-title">Core Features</h2>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon gst">📋</div>
          <h3 class="feature-title">GST Compliant Invoicing</h3>
          <p class="feature-description">Generate GST compliant invoices with automated tax calculations and seamless compliance reporting.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon inventory">📦</div>
          <h3 class="feature-title">Inventory Management</h3>
          <p class="feature-description">Track stock levels, manage products, and get real-time inventory updates across all locations.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon payment">💳</div>
          <h3 class="feature-title">Payment Tracking</h3>
          <p class="feature-description">Monitor payments, track due amounts, and manage cash flow with detailed payment reports.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Why Choose Sling -->
  <section class="why-choose">
    <div class="why-choose-container">
      <div class="why-choose-content">
        <h2>Why <span class="highlight">Choose Sling?</span></h2>
        <p>
          Sling brings billing efficiency to your business with intelligent automation, real-time analytics, and seamless integrations. Whether you're a small business or growing enterprise, our platform scales with your needs while ensuring compliance and accuracy.
        </p>
        <a href="#" class="cta-button">Get Started Free</a>
      </div>
      <div class="why-choose-image">
        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300' viewBox='0 0 400 300'%3E%3Crect width='400' height='300' fill='%23e9ecef'/%3E%3Ctext x='200' y='150' font-family='Arial' font-size='16' fill='%236c757d' text-anchor='middle'%3EFeature Illustration%3C/text%3E%3C/svg%3E" alt="Why Choose Sling">
      </div>
    </div>
  </section>

  <!-- Benefits -->
  <section class="benefits">
    <div class="benefits-container">
      <h2 class="section-title">Why Choose Sling?</h2>
      <div class="benefits-grid">
        <div class="benefit-item">
          <div class="benefit-icon">🎯</div>
          <h3 class="benefit-title">Designed for SMBs</h3>
          <p class="benefit-description">Built specifically for small and medium businesses with intuitive workflows.</p>
        </div>
        <div class="benefit-item">
          <div class="benefit-icon">💰</div>
          <h3 class="benefit-title">Flexible Pricing</h3>
          <p class="benefit-description">Choose from flexible pricing plans that grow with your business needs.</p>
        </div>
        <div class="benefit-item">
          <div class="benefit-icon">🔒</div>
          <h3 class="benefit-title">Secure & Reliable</h3>
          <p class="benefit-description">Bank-grade security with 99.9% uptime guarantee for your peace of mind.</p>
        </div>
        <div class="benefit-item">
          <div class="benefit-icon">📱</div>
          <h3 class="benefit-title">Easy to Use</h3>
          <p class="benefit-description">Simple interface that requires no accounting knowledge to get started.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="testimonials">
    <div class="testimonials-container">
      <h2>What Our Customers Say</h2>
      <div class="testimonial-card">
        <p class="testimonial-text">
          "Sling has transformed how we handle billing and inventory. The automated GST calculations save us hours every week, and the real-time reports help us make better business decisions."
        </p>
        <div class="testimonial-author">Retail Chain Owner</div>
      </div>
    </div>
  </section>

  <!-- Pricing -->
  <section class="pricing">
    <div class="pricing-container">
      <h2 class="section-title">Plans at a Glance</h2>
      <div class="pricing-grid">
        <div class="pricing-card">
          <h3 class="pricing-plan">Free Mobile</h3>
          <div class="pricing-price">₹0</div>
          <div class="pricing-period">Free • For Basic Use</div>
          <ul class="pricing-features">
            <li>Basic invoicing</li>
            <li>Up to 50 transactions</li>
            <li>Mobile app access</li>
            <li>Email support</li>
          </ul>
          <a href="#" class="pricing-button secondary">Get Started</a>
        </div>
        <div class="pricing-card featured">
          <h3 class="pricing-plan">Standard</h3>
          <div class="pricing-price">₹999</div>
          <div class="pricing-period">per month</div>
          <ul class="pricing-features">
            <li>Unlimited invoicing</li>
            <li>Inventory management</li>
            <li>GST compliance</li>
            <li>Priority support</li>
            <li>Advanced reports</li>
          </ul>
          <a href="#" class="pricing-button">Choose Standard</a>
        </div>
        <div class="pricing-card">
          <h3 class="pricing-plan">Pro</h3>
          <div class="pricing-price">₹2499</div>
          <div class="pricing-period">per month</div>
          <ul class="pricing-features">
            <li>Everything in Standard</li>
            <li>Multi-location support</li>
            <li>API integrations</li>
            <li>Custom reports</li>
            <li>Dedicated support</li>
          </ul>
          <a href="#" class="pricing-button">Choose Pro</a>
        </div>
        <div class="pricing-card">
          <h3 class="pricing-plan">Enterprise</h3>
          <div class="pricing-price">Custom</div>
          <div class="pricing-period">contact us</div>
          <ul class="pricing-features">
            <li>Custom features</li>
            <li>White-label options</li>
            <li>Dedicated infrastructure</li>
            <li>24/7 phone support</li>
            <li>Training & onboarding</li>
          </ul>
          <a href="#" class="pricing-button">Contact Sales</a>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="cta-section">
    <div class="cta-container">
      <h2>Ready to Simplify Your Billing?</h2>
      <p>Join thousands of businesses that trust Sling for their billing needs. Start your free trial today.</p>
      <a href="#" class="cta-button">Get Started Free</a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-container">
      <p>© 2025 Sling Billing Software. All rights reserved.</p>
    </div>
  </footer>
@endsection