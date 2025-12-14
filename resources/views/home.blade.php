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
      <a href="#" class="nav-button" onclick="openShopDetailsModal()">Free Trial</a>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <!-- Floating Billing Icons -->
    <!-- <div class="billing-icons">
      <div class="billing-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 5.5V4C15 1.8 13.2 0 11 0S7 1.8 7 4V5.5L1 7V9H3V20C3 21.1 3.9 22 5 22H19C20.1 22 21 21.1 21 20V9H21ZM5 9H19V20H5V9Z"/>
        </svg>
      </div>
      <div class="billing-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM19 19H5V5H19V19ZM17 12H15V17H13V12H11L15 8L17 12Z"/>
        </svg>
      </div>
      <div class="billing-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2ZM18 20H6V4H13V9H18V20Z"/>
        </svg>
      </div>
      <div class="billing-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M20 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 18H4V12H20V18ZM20 8H4V6H20V8Z"/>
        </svg>
      </div>
      <div class="billing-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M16 6L18.29 8.29L13.41 13.17L9.41 9.17L2 16.59L3.41 18L9.41 12L13.41 16L19.71 9.71L22 12V6H16Z"/>
        </svg>
      </div>
    </div> -->

    <!-- Floating Invoice -->
    <div class="floating-invoice">
      <div class="invoice-header">INVOICE</div>
      <div class="invoice-line"></div>
      <div class="invoice-line"></div>
      <div class="invoice-line"></div>
      <div class="invoice-line"></div>
    </div>

    <!-- Billing Statistics -->
    <div class="billing-stats">
      <div class="stat-item">
        <span>Invoices Today:</span>
        <span class="stat-value counter">127</span>
      </div>
      <div class="stat-item">
        <span>Revenue:</span>
        <span class="stat-value counter">₹45,280</span>
      </div>
      <div class="stat-item">
        <span>GST Saved:</span>
        <span class="stat-value counter">₹8,145</span>
      </div>
    </div>

    <!-- Typing Animation -->
    <div class="typing-animation">
      <div class="typing-text">Creating Invoice...</div>
      <div class="typing-text">GST Calculation ✓</div>
      <div class="typing-text">Payment Processed ✓</div>
      <div class="typing-text typing-cursor">Ready to send!</div>
    </div>

    <!-- Payment Success -->
    <div class="payment-success">✓</div>

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
        <a href="#" class="cta-button" onclick="openShopDetailsModal()">Free Trial</a>
      </div>
      <div class="hero-image">
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
        <img src="assets/images/landing-page/why-choose.svg" alt="Why Choose Sling">
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
          <div class="plan-subtitle">Best for beginners & startups</div>
          <div class="pricing-price">₹0</div>
          <div class="pricing-period">/month</div>
          <a href="#" class="pricing-button secondary">Get Started Now</a>
          <ul class="pricing-features">
            <li>✓ Basic invoicing</li>
            <li>✓ Up to 50 transactions</li>
            <li>✓ Mobile app access</li>
            <li>✓ Email support</li>
          </ul>
        </div>
        
        <div class="pricing-card featured">
          
          <h3 class="pricing-plan">Standard</h3>
          <div class="plan-subtitle">Best for small businesses</div>
          <div class="pricing-price">₹999</div>
          <div class="pricing-period">/month</div>
          <a href="#" class="pricing-button">Get Started Now</a>
          <ul class="pricing-features">
            <li>✓ Unlimited invoicing</li>
            <li>✓ Inventory management</li>
            <li>✓ GST compliance</li>
            <li>✓ Priority support</li>
            <li>✓ Advanced reports</li>
          </ul>
        </div>
        
        <div class="pricing-card">
          
          <h3 class="pricing-plan">Pro</h3>
          <div class="plan-subtitle">Best for growing businesses</div>
          <div class="pricing-price">₹2499</div>
          <div class="pricing-period">/month</div>
          <a href="#" class="pricing-button">Get Started Now</a>
          <ul class="pricing-features">
            <li>✓ Everything in Standard</li>
            <li>✓ Multi-location support</li>
            <li>✓ API integrations</li>
            <li>✓ Custom reports</li>
            <li>✓ Dedicated support</li>
          </ul>
        </div>
<!--         
        <div class="pricing-card">

          <h3 class="pricing-plan">Enterprise</h3>
          <div class="plan-subtitle">For large-scale enterprises</div>
          <div class="pricing-price">Custom</div>
          <div class="pricing-period">contact us</div>
          <a href="#" class="pricing-button">Contact Us</a>
          <ul class="pricing-features">
            <li>✓ Custom features</li>
            <li>✓ White-label options</li>
            <li>✓ Dedicated infrastructure</li>
            <li>✓ 24/7 phone support</li>
            <li>✓ Training & onboarding</li>
          </ul>
        </div> -->
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="cta-section">
    <div class="cta-container">
      <h2>Ready to Simplify Your <span class="highlight">Billing?</span></h2>
      <p>Start your free trial now or book a personalized demo today!</p>
      <a href="#" class="cta-button">Get Started Free</a>
    </div>
  </section>

  <!-- Demo Form Section -->
  <section class="demo-section">
    <div class="demo-container">
      <h2 class="demo-title">Get a Free Demo Now</h2>
      <div class="demo-content">
        <div class="demo-form">
          <form class="contact-form">
            <div class="form-group">
              <label for="name">Name</label>
              <input type="text" id="name" name="name" placeholder="Enter your name." required>
            </div>
            <div class="form-group">
              <label for="email">E-mail ID</label>
              <input type="email" id="email" name="email" placeholder="Enter your Email ID." required>
            </div>
            <div class="form-group">
              <label for="phone">Mobile Number</label>
              <input type="tel" id="phone" name="phone" placeholder="Enter your Mobile no." required>
            </div>
            <div class="form-group">
              <label for="company">Company Name</label>
              <input type="text" id="company" name="company" placeholder="Enter your Company name." required>
            </div>
            <button type="button" class="demo-button" >Book a Free Demo</button>
          </form>
        </div>
        <div class="contact-info">
          <h3>Get In Touch</h3>
          <div class="contact-item">
            <div class="contact-icon email">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 8L12 13L4 8V6L12 11L20 6V8Z"/>
              </svg>
            </div>
            <div class="contact-details">
              <h4>Email</h4>
              <p>Company@mail.com</p>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon phone">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M6.62 10.79C8.06 13.62 10.38 15.94 13.21 17.38L15.41 15.18C15.69 14.9 16.08 14.82 16.43 14.93C17.55 15.3 18.75 15.5 20 15.5C20.55 15.5 21 15.95 21 16.5V20C21 20.55 20.55 21 20 21C10.61 21 3 13.39 3 4C3 3.45 3.45 3 4 3H7.5C8.05 3 8.5 3.45 8.5 4C8.5 5.25 8.7 6.45 9.07 7.57C9.18 7.92 9.1 8.31 8.82 8.59L6.62 10.79Z"/>
              </svg>
            </div>
            <div class="contact-details">
              <h4>Phone</h4>
              <p>012 345 678 9101</p>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon location">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22S19 14.25 19 9C19 5.13 15.87 2 12 2ZM12 11.5C10.62 11.5 9.5 10.38 9.5 9S10.62 6.5 12 6.5S14.5 7.62 14.5 9S13.38 11.5 12 11.5Z"/>
              </svg>
            </div>
            <div class="contact-details">
              <h4>Location</h4>
              <p>4517 Washington Ave. Manchester, Kentucky 39495</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Shop Details Modal -->
  <div id="shopDetailsModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Shop Details</h2>
        <span class="close" onclick="closeShopDetailsModal()">&times;</span>
      </div>
      <div class="modal-body">
        <form class="row" action="{{route('register')}}" method="post" enctype="multipart/form-data" id="shopCreate">
          @csrf

          <!-- Hidden fields -->
          <input type="hidden" name="password" value="Password@1234">
          <input type="hidden" name="password_confirmation" value="Password@1234">
          <input type="hidden" name="bill_type" value="1">
          <input type="hidden" name="payment_method" value="1">


          <div class="form-row">
            <div class="form-group">
              <label for="name">Shop Name *</label>
              <input type="text" id="name" name="name" placeholder="Enter your shop name" required>
            </div>
            <div class="form-group">
              <label for="phone">Mobile Number *</label>
              <input type="tel" id="phone" name="phone" placeholder="Enter mobile number" required>
            </div>
            <div class="form-group">
              <label for="phone1">Alternate Mobile Number</label>
              <input type="tel" id="phone1" name="phone1" placeholder="Enter alternate mobile number">
            </div>
          </div>
          
          <div class="form-row address-logo-row">
            <div class="form-group address-group">
              <label for="address">Address</label>
              <textarea id="address" name="address" placeholder="Enter your shop address" rows="5"></textarea>
            </div>
            <div class="form-group logo-group">
              <label for="shopLogo">Upload Shop Logo *</label>
              <div class="file-upload">
                <input type="file" id="shopLogo" name="logo" accept="image/*" required>
                <div class="file-upload-placeholder">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                  </svg>
                  <span>Choose file to upload</span>
                </div>
              </div>
            </div>
          </div>
   

          <div class="form-row">
            <div class="form-group">
              <label for="email">Email *</label>
              <input type="email" id="email" name="email" placeholder="Enter email address" required="">
            </div>
            <div class="form-group">
              <label for="gst">Company GSTin</label>
              <input type="text" id="gst" name="gst" placeholder="Enter GST number">
            </div>
          </div>
          <div class="form-actions">
            <button type="button" class="btn-secondary" onclick="closeShopDetailsModal()">Cancel</button>
            <button type="submit" class="btn-primary">Submit Details</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-container">
      <p>© 2025 Sling Billing Software. All rights reserved.</p>
    </div>
  </footer>

  <script>
    function openShopDetailsModal() {
      document.getElementById('shopDetailsModal').style.display = 'block';
      document.body.style.overflow = 'hidden';
    }

    function closeShopDetailsModal() {
      document.getElementById('shopDetailsModal').style.display = 'none';
      document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('shopDetailsModal');
      if (event.target == modal) {
        closeShopDetailsModal();
      }
    }

    // Handle file upload display
    document.getElementById('shopLogo').addEventListener('change', function(e) {
      const fileName = e.target.files[0]?.name;
      const placeholder = document.querySelector('.file-upload-placeholder span');
      if (fileName) {
        placeholder.textContent = fileName;
      }
    });
  </script>
@endsection