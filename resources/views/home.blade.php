@extends('layouts.landing')

@section('title')
  <title>Sling Billing Software | Smart Billing for Indian Businesses</title>
@endsection

@section('style')
<style>
  /* ── Shared ── */
  .section-badge {
    display: inline-block;
    background: rgba(255,69,0,0.12);
    color: var(--primary-color);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 4px 14px;
    border-radius: 20px;
    margin-bottom: 12px;
  }
  .section-title {
    font-size: clamp(1.6rem, 3vw, 2.2rem);
    font-weight: 800;
    color: var(--dark-color);
    margin-bottom: 12px;
  }
  .section-sub {
    color: var(--gray-color);
    max-width: 620px;
    margin: 0 auto 40px;
    font-size: 1rem;
    line-height: 1.75;
  }
  .highlight { color: var(--primary-color); }

  /* ── About ── */
  .about-section {
    padding: 80px 20px;
    background: linear-gradient(135deg,#f0f4ff 0%,#fff3f0 100%);
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .about-section::before {
    content:'';
    position:absolute;inset:0;
    background: radial-gradient(ellipse at 20% 50%, rgba(255,69,0,0.06) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 50%, rgba(25,173,159,0.06) 0%, transparent 60%);
  }
  .about-section .section-badge { background:rgba(255,69,0,0.1); color:var(--primary-color); }
  .about-section .section-title { color:var(--dark-color); }
  .about-section .section-sub { color:var(--gray-color); }
  .about-cards {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 24px;
    max-width: 1100px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
  }
  .about-card {
    background: #fff;
    border: 1.5px solid var(--border-color);
    border-radius: 16px;
    padding: 32px 26px;
    text-align: left;
    transition: transform .3s, box-shadow .3s;
    position: relative;
    overflow: hidden;
  }
  .about-card::before {
    content:'';
    position:absolute;top:0;left:0;right:0;height:4px;
    background: linear-gradient(90deg,var(--primary-color),var(--secondary-color));
    border-radius: 16px 16px 0 0;
  }
  .about-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(255,69,0,0.1); border-color: rgba(255,69,0,0.15); }
  .about-icon-box {
    width:52px; height:52px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.5rem; margin-bottom:20px;
    background: linear-gradient(135deg,#fff3f0,#ffe8e0);
    border: 1px solid rgba(255,69,0,0.15);
  }
  .about-card h3 { color:var(--dark-color); font-size:1.05rem; font-weight:700; margin-bottom:10px; }
  .about-card p { color:var(--gray-color); font-size:0.875rem; line-height:1.75; margin-bottom:16px; }
  .about-stat {
    display: inline-flex; align-items:center; gap:6px;
    background: #f0fffe; color: var(--secondary-color);
    font-size:0.78rem; font-weight:700;
    padding:4px 10px; border-radius:20px;
    border: 1px solid rgba(25,173,159,0.2);
  }
  /* ── Demo improvements ── */
  .demo-feature-list { list-style:none; padding:0; margin:20px 0 28px; }
  .demo-feature-list li {
    display:flex; align-items:center; gap:10px;
    padding:8px 0; border-bottom:1px solid var(--border-color);
    font-size:0.875rem; color:var(--dark-gray);
  }
  .demo-feature-list li:last-child { border-bottom:none; }
  .demo-check {
    width:22px; height:22px; border-radius:50%; flex-shrink:0;
    background: linear-gradient(135deg,var(--primary-color),#ff7043);
    color:#fff; font-size:0.65rem; font-weight:700;
    display:flex; align-items:center; justify-content:center;
  }
  .contact-card {
    display:flex; gap:14px; align-items:center;
    background:#fff; border:1.5px solid var(--border-color);
    border-radius:12px; padding:14px 16px; margin-bottom:12px;
    transition: box-shadow .3s, border-color .3s;
  }
  .contact-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.07); border-color:rgba(255,69,0,0.2); }
  .contact-icon3 {
    width:44px; height:44px; border-radius:12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; font-style:normal;
  }
  .contact-icon3.email { background:#fff0e8; }
  .contact-icon3.phone { background:#e8f8f6; }
  .contact-icon3.loc   { background:#eef2ff; }
  .contact-card-text h4 { color:var(--dark-color); font-size:0.82rem; font-weight:700; margin:0 0 2px; text-transform:uppercase; letter-spacing:0.5px; }
  .contact-card-text p  { color:var(--gray-color); font-size:0.875rem; margin:0; }
  .form-row2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
  .form-divider { border:none; border-top:1.5px solid var(--border-color); margin:20px 0; }
  .input-icon-wrap { position:relative; }
  .input-icon-wrap .i-icon {
    position:absolute; left:12px; top:50%; transform:translateY(-50%);
    color:#aaa; font-size:0.9rem; pointer-events:none;
  }
  .input-icon-wrap input { padding-left:34px !important; }

  /* ── What We Do ── */
  .whatwedo-section {
    padding: 80px 20px;
    background: #fff;
    text-align: center;
  }
  .whatwedo-grid {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 20px;
    max-width: 1100px;
    margin: 0 auto;
  }
  .whatwedo-card {
    border: 1.5px solid var(--border-color);
    border-radius: 14px;
    padding: 28px 20px;
    text-align: left;
    transition: all .3s;
    position: relative;
    overflow: hidden;
  }
  .whatwedo-card::after {
    content:'';
    position:absolute;
    bottom:0;left:0;right:0;height:3px;
    background: linear-gradient(90deg,var(--primary-color),var(--secondary-color));
    transform: scaleX(0);
    transition: transform .3s;
  }
  .whatwedo-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.1); transform:translateY(-4px); border-color:transparent; }
  .whatwedo-card:hover::after { transform: scaleX(1); }
  .whatwedo-icon { font-size:1.8rem; margin-bottom:12px; }
  .whatwedo-card h3 { font-size:1rem; font-weight:700; color:var(--dark-color); margin-bottom:8px; }
  .whatwedo-card p { font-size:0.875rem; color:var(--gray-color); line-height:1.7; }

  /* ── Services ── */
  .services-section {
    padding: 80px 20px;
    background: linear-gradient(160deg,#f8f9fa 0%,#fff3f0 100%);
    text-align: center;
  }
  .services-grid {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 18px;
    max-width: 1200px;
    margin: 0 auto;
  }
  .service-card {
    background: #fff;
    border-radius: 14px;
    padding: 24px 18px;
    text-align: left;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid rgba(0,0,0,0.05);
    transition: all .3s;
  }
  .service-card:hover { transform:translateY(-4px); box-shadow:0 10px 30px rgba(255,69,0,0.12); }
  .service-num {
    display:inline-flex; align-items:center; justify-content:center;
    width:32px; height:32px; border-radius:8px;
    background:linear-gradient(135deg,var(--primary-color),#ff7043);
    color:#fff; font-size:0.8rem; font-weight:800;
    margin-bottom:12px;
  }
  .service-card h3 { font-size:0.92rem; font-weight:700; color:var(--dark-color); margin-bottom:6px; }
  .service-card p { font-size:0.82rem; color:var(--gray-color); line-height:1.65; }

  /* ── Why Choose ── */
  .why-section {
    padding: 80px 20px;
    background: linear-gradient(135deg,#1b1e2c 0%,#0f3460 100%);
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .why-section::before {
    content:'';
    position:absolute;inset:0;
    background: radial-gradient(ellipse at 70% 30%, rgba(25,173,159,0.2) 0%, transparent 50%);
  }
  .why-section .section-badge { background:rgba(25,173,159,0.2); color:#19ad9f; }
  .why-section .section-title { color:#fff; }
  .why-section .section-sub { color:rgba(255,255,255,0.65); }
  .why-grid {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 20px;
    max-width: 1000px;
    margin: 0 auto;
    position: relative; z-index:2;
  }
  .why-card {
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 14px;
    padding: 28px 20px;
    text-align:center;
    transition: all .3s;
  }
  .why-card:hover { background:rgba(255,255,255,0.12); transform:translateY(-4px); }
  .why-icon { font-size:2rem; margin-bottom:12px; }
  .why-card h3 { color:#fff; font-size:1rem; font-weight:700; margin-bottom:8px; }
  .why-card p { color:rgba(255,255,255,0.6); font-size:0.875rem; line-height:1.7; }

  /* ── Who For ── */
  .whofor-section {
    padding: 80px 20px;
    background: #fff;
    text-align:center;
  }
  .whofor-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    max-width:1100px;
    margin:0 auto;
  }
  .whofor-card {
    background:linear-gradient(135deg,#f8f9fa,#fff);
    border:1.5px solid var(--border-color);
    border-radius:14px;
    padding:28px 20px;
    text-align:left;
    transition:all .3s;
  }
  .whofor-card:hover { border-color:var(--primary-color); box-shadow:0 6px 24px rgba(255,69,0,0.1); transform:translateY(-3px); }
  .whofor-icon { font-size:2rem; margin-bottom:12px; }
  .whofor-card h3 { font-size:1rem; font-weight:700; color:var(--dark-color); margin-bottom:6px; }
  .whofor-card p { font-size:0.875rem; color:var(--gray-color); line-height:1.7; }

  /* ── Testimonial ── */
  .testimonial-section {
    padding:70px 20px;
    background:linear-gradient(135deg,#fff8f6,#f0fffe);
    text-align:center;
  }
  .testimonial-inner {
    max-width:700px;
    margin:0 auto;
    background:#fff;
    border-radius:20px;
    padding:40px 36px;
    box-shadow:0 8px 40px rgba(0,0,0,0.08);
    position:relative;
  }
  .testimonial-inner::before {
    content:'"';
    position:absolute;
    top:-20px;left:30px;
    font-size:5rem;
    color:var(--primary-color);
    opacity:0.2;
    line-height:1;
    font-family:Georgia,serif;
  }
  .testimonial-text { font-size:1rem; color:#444; line-height:1.8; font-style:italic; margin-bottom:20px; }
  .testimonial-author { font-weight:700; color:var(--dark-color); }
  .testimonial-role { font-size:0.85rem; color:var(--gray-color); }

  /* ── Pricing ── */
  .pricing-section {
    padding:80px 20px;
    background:#fff;
    text-align:center;
  }
  .pricing-cards {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:24px;
    max-width:1000px;
    margin:0 auto;
  }
  .p-card {
    border:2px solid var(--border-color);
    border-radius:18px;
    padding:36px 28px;
    text-align:center;
    transition:all .3s;
    position:relative;
  }
  .p-card:hover { transform:translateY(-6px); box-shadow:0 12px 40px rgba(0,0,0,0.1); }
  .p-card.featured {
    border-color:var(--primary-color);
    background:linear-gradient(160deg,#fff8f6,#fff);
    box-shadow:0 8px 30px rgba(255,69,0,0.15);
  }
  .p-badge {
    position:absolute; top:-13px; left:50%; transform:translateX(-50%);
    background:var(--primary-color); color:#fff;
    font-size:0.7rem; font-weight:700; letter-spacing:1px; text-transform:uppercase;
    padding:4px 14px; border-radius:20px;
  }
  .p-plan { font-size:1.1rem; font-weight:800; color:var(--dark-color); margin-bottom:4px; }
  .p-sub { font-size:0.8rem; color:var(--gray-color); margin-bottom:16px; }
  .p-price { font-size:2.5rem; font-weight:800; color:var(--primary-color); }
  .p-period { font-size:0.8rem; color:var(--gray-color); margin-bottom:20px; }
  .p-btn {
    display:block; width:100%;
    padding:10px; border-radius:10px;
    font-weight:700; font-size:0.9rem;
    text-decoration:none; margin-bottom:20px;
    transition:all .3s; cursor:pointer; border:none;
  }
  .p-btn-primary { background:var(--primary-color); color:#fff; }
  .p-btn-primary:hover { background:var(--primary-dark); color:#fff; }
  .p-btn-outline { background:#fff; color:var(--dark-color); border:2px solid var(--border-color); }
  .p-btn-outline:hover { border-color:var(--primary-color); color:var(--primary-color); }
  .p-features { list-style:none; text-align:left; }
  .p-features li { padding:6px 0; font-size:0.875rem; color:#555; border-bottom:1px solid var(--border-color); }
  .p-features li:last-child { border-bottom:none; }
  .p-features li::before { content:'✓ '; color:var(--secondary-color); font-weight:700; }

  /* ── CTA Banner ── */
  .cta-banner {
    padding:70px 20px;
    background:linear-gradient(135deg,var(--primary-color) 0%,#e63e00 50%,#c0392b 100%);
    text-align:center;
    position:relative; overflow:hidden;
  }
  .cta-banner::before {
    content:'';
    position:absolute;inset:0;
    background:url("data:image/svg+xml,%3Csvg width='60' height='60' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='30' cy='30' r='1' fill='white' opacity='0.15'/%3E%3C/svg%3E") repeat;
  }
  .cta-banner h2 { color:#fff; font-size:clamp(1.6rem,3vw,2.4rem); font-weight:800; margin-bottom:12px; }
  .cta-banner p { color:rgba(255,255,255,0.85); font-size:1.05rem; margin-bottom:28px; }
  .cta-btn-white {
    background:#fff; color:var(--primary-color);
    padding:14px 36px; border-radius:50px;
    font-weight:800; font-size:1rem;
    text-decoration:none; display:inline-block;
    transition:all .3s;
    box-shadow:0 4px 20px rgba(0,0,0,0.2);
  }
  .cta-btn-white:hover { transform:translateY(-3px); box-shadow:0 8px 30px rgba(0,0,0,0.3); color:var(--primary-color); text-decoration:none; }

  /* ── Demo/Contact ── */
  .demo-wrap {
    padding:80px 20px;
    background: linear-gradient(135deg,#f8f9ff 0%,#fff8f6 100%);
    border-top: 1px solid var(--border-color);
  }
  .demo-inner {
    max-width:1100px; margin:0 auto;
    display:grid; grid-template-columns:1fr 1fr; gap:48px; align-items:start;
  }
  .demo-left h2 { color:var(--dark-color); font-size:1.8rem; font-weight:800; margin-bottom:12px; }
  .demo-left p { color:var(--gray-color); line-height:1.8; margin-bottom:24px; }
  .contact-item { display:flex; gap:12px; margin-bottom:20px; align-items:flex-start; }
  .contact-icon2 {
    width:42px; height:42px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    background:rgba(255,69,0,0.1); color:var(--primary-color); font-size:1.1rem;
  }
  .contact-detail h4 { color:var(--dark-color); font-size:0.9rem; font-weight:700; margin-bottom:2px; }
  .contact-detail p { color:var(--gray-color); font-size:0.85rem; margin:0; }
  .demo-form-box {
    background:#fff;
    border:1.5px solid var(--border-color);
    border-radius:18px; padding:32px;
    box-shadow: 0 6px 30px rgba(0,0,0,0.07);
  }
  .demo-form-box h3 { color:var(--dark-color); font-size:1.2rem; font-weight:700; margin-bottom:20px; }
  .form-group { margin-bottom:16px; }
  .form-group label { display:block; color:var(--dark-gray); font-size:0.85rem; font-weight:600; margin-bottom:6px; }
  .form-group input, .form-group textarea {
    width:100%; padding:10px 14px;
    background:#f8f9fa;
    border:1.5px solid var(--border-color);
    border-radius:8px; color:var(--dark-color); font-size:0.9rem;
    outline:none; transition:border .3s;
  }
  .form-group input:focus, .form-group textarea:focus { border-color:var(--primary-color); background:#fff; }
  .form-group input::placeholder { color:#aaa; }
  .demo-btn {
    width:100%; padding:12px;
    background:linear-gradient(135deg,var(--primary-color),#ff7043);
    color:#fff; border:none; border-radius:10px;
    font-weight:700; font-size:0.95rem; cursor:pointer;
    transition:all .3s;
  }
  .demo-btn:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(255,69,0,0.35); }

  /* ── Footer ── */
  .site-footer {
    background:#0d0f1a;
    padding:24px 20px;
    text-align:center;
    color:rgba(255,255,255,0.4);
    font-size:0.85rem;
  }
  .site-footer span { color:rgba(255,255,255,0.6); }

  /* ── Responsive ── */
  @media(max-width:900px) {
    .about-cards,.whatwedo-grid,.why-grid,.whofor-grid,.pricing-cards,.demo-inner { grid-template-columns:1fr 1fr; }
    .services-grid { grid-template-columns:repeat(2,1fr); }
  }
  @media(max-width:600px) {
    .about-cards,.whatwedo-grid,.services-grid,.why-grid,.whofor-grid,.pricing-cards { grid-template-columns:1fr; }
    .demo-inner { grid-template-columns:1fr; }
  }
</style>
@endsection

@section('body')

  <!-- ══ HEADER ══ -->
  <header class="header">
    <div class="nav-container">
      <a href="#" class="logo">
        <img src="assets/images/sling-dark-logo.png" alt="Sling Logo" height="40">
      </a>
      <a href="#" class="nav-button" onclick="openShopDetailsModal()">Free Trial</a>
    </div>
  </header>

  <!-- ══ HERO ══ -->
  <section class="hero">
    <div class="floating-invoice">
      <div class="invoice-header">INVOICE</div>
      <div class="invoice-line"></div>
      <div class="invoice-line"></div>
      <div class="invoice-line"></div>
      <div class="invoice-line"></div>
    </div>
    <div class="billing-stats">
      <div class="stat-item"><span>Invoices Today:</span><span class="stat-value counter">127</span></div>
      <div class="stat-item"><span>Revenue:</span><span class="stat-value counter">₹45,280</span></div>
      <div class="stat-item"><span>GST Saved:</span><span class="stat-value counter">₹8,145</span></div>
    </div>
    <div class="typing-animation">
      <div class="typing-text">Creating Invoice...</div>
      <div class="typing-text">GST Calculation ✓</div>
      <div class="typing-text">Payment Processed ✓</div>
      <div class="typing-text typing-cursor">Ready to send!</div>
    </div>
    <div class="payment-success">✓</div>

    @if ($errors->any())
      <div class="alert alert-danger" style="max-width:600px;margin:0 auto 16px;">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
      </div>
    @endif
    @if(session('success_alert'))
      <div class="alert alert-success" style="max-width:600px;margin:0 auto 16px;">
        <strong>Success! </strong>{{ session('success_alert') }}
      </div>
    @endif

    <div class="hero-container">
      <div class="hero-content">
        <div class="hero-subtitle">Complete Retail &amp; Business Management · Made for India</div>
        <h1 class="hero-title"><span class="highlight">Sling</span> Billing Software</h1>
        <p class="hero-description">One platform to manage billing, inventory, vendors, staff, and GST — for single shops and multi-branch businesses.</p>
        <a href="#" class="cta-button" onclick="openShopDetailsModal()">Start Free Trial</a>
      </div>
      <div class="hero-image">
        <img src="assets/images/landing-page/banner.svg" alt="Billing Software Dashboard">
      </div>
    </div>
  </section>

  <!-- ══ ABOUT ══ -->
  <section class="about-section">
    <div style="position:relative;z-index:2;text-align:center;margin-bottom:48px;">
      <span class="section-badge">About Us</span>
      <h2 class="section-title">Built for Indian Businesses</h2>
      <p class="section-sub">Sling is a powerful, all-in-one retail and business management platform. From POS billing to vendor management, GST compliance, and multi-branch operations — no juggling multiple tools. One login. Everything covered.</p>
    </div>
    <div class="about-cards">
      <div class="about-card">
        <div class="about-icon-box">🏪</div>
        <h3>Single Shop to Multi-Branch</h3>
        <p>Start with one location and scale to unlimited branches — same platform, same workflow, zero disruption.</p>
        <span class="about-stat">✓ Unlimited Branch Support</span>
      </div>
      <div class="about-card">
        <div class="about-icon-box">🧾</div>
        <h3>100% GST Ready</h3>
        <p>CGST/SGST split, HSN codes, tax summaries, and rupee formatting — built in from day one, not bolted on.</p>
        <span class="about-stat">✓ Fully Tax Compliant</span>
      </div>
      <div class="about-card">
        <div class="about-icon-box">🔌</div>
        <h3>API Ready for Growth</h3>
        <p>Connect mobile apps and third-party tools via REST API. Your platform grows as your business evolves.</p>
        <span class="about-stat">✓ REST API Included</span>
      </div>
    </div>
  </section>

  <!-- ══ WHAT WE DO ══ -->
  <section class="whatwedo-section">
    <span class="section-badge">What We Do</span>
    <h2 class="section-title">Everything Your Business Needs</h2>
    <p class="section-sub">We cover your entire business cycle — from purchasing goods from vendors to selling products to customers — with full financial visibility at every step.</p>
    <div class="whatwedo-grid">
      <div class="whatwedo-card">
        <div class="whatwedo-icon">🚀</div>
        <h3>Sell Faster</h3>
        <p>Modern POS with instant product lookup, multi-payment support, and automatic GST invoice generation.</p>
      </div>
      <div class="whatwedo-card">
        <div class="whatwedo-icon">✅</div>
        <h3>Stay Compliant</h3>
        <p>Automatic CGST/SGST calculation, HSN codes, and fully compliant tax invoices — zero manual effort.</p>
      </div>
      <div class="whatwedo-card">
        <div class="whatwedo-icon">📊</div>
        <h3>Track Everything</h3>
        <p>Stock, orders, refunds, vendor payments, and staff activity — all tracked with a complete audit trail.</p>
      </div>
      <div class="whatwedo-card">
        <div class="whatwedo-icon">🏢</div>
        <h3>Manage Multiple Branches</h3>
        <p>Each branch gets independent inventory, billing, and staff while you see everything from one dashboard.</p>
      </div>
      <div class="whatwedo-card">
        <div class="whatwedo-icon">📈</div>
        <h3>Make Smarter Decisions</h3>
        <p>Daily sales, order, and vendor reports — all exportable to PDF and Excel with flexible date filters.</p>
      </div>
      <div class="whatwedo-card">
        <div class="whatwedo-icon">⚡</div>
        <h3>Save Time</h3>
        <p>Bulk import products, customers, and categories via Excel. Automated processes replace hours of manual work.</p>
      </div>
    </div>
  </section>

  <!-- ══ SERVICES ══ -->
  <section class="services-section">
    <span class="section-badge">Our Services</span>
    <h2 class="section-title">12 Modules. One Platform.</h2>
    <p class="section-sub">Everything from billing to refunds, vendor management to notifications — all under one roof.</p>
    <div class="services-grid">
      <div class="service-card">
        <div class="service-num">1</div>
        <h3>Billing &amp; POS</h3>
        <p>Fast billing, multi-payment, IMEI tracking, size &amp; colour variations, GST invoice print.</p>
      </div>
      <div class="service-card">
        <div class="service-num">2</div>
        <h3>Inventory Management</h3>
        <p>Real-time stock across branches, variations, stock transfers, and full history.</p>
      </div>
      <div class="service-card">
        <div class="service-num">3</div>
        <h3>GST Billing &amp; Tax</h3>
        <p>CGST/SGST invoices, HSN codes, multiple tax rates, bulk GST uploads.</p>
      </div>
      <div class="service-card">
        <div class="service-num">4</div>
        <h3>Vendor &amp; Purchase Orders</h3>
        <p>Manage suppliers, POs, vendor payments, refunds, and outstanding ledger.</p>
      </div>
      <div class="service-card">
        <div class="service-num">5</div>
        <h3>Multi-Branch Management</h3>
        <p>Headquarters + branches from one login — independent operations, unified reporting.</p>
      </div>
      <div class="service-card">
        <div class="service-num">6</div>
        <h3>Customer Management</h3>
        <p>Customer database, purchase history, GST numbers, and billing counter auto-complete.</p>
      </div>
      <div class="service-card">
        <div class="service-num">7</div>
        <h3>Refunds &amp; Returns</h3>
        <p>Full/partial refunds, IMEI-specific returns, automatic stock restoration.</p>
      </div>
      <div class="service-card">
        <div class="service-num">8</div>
        <h3>Reports &amp; Analytics</h3>
        <p>Daily, order, stock and vendor reports exportable to PDF &amp; Excel.</p>
      </div>
      <div class="service-card">
        <div class="service-num">9</div>
        <h3>Staff Management</h3>
        <p>Role-based access, staff attribution per transaction, HQ and branch level.</p>
      </div>
      <div class="service-card">
        <div class="service-num">10</div>
        <h3>Bulk Import / Export</h3>
        <p>Excel imports for products, customers, categories — with log tracking.</p>
      </div>
      <div class="service-card">
        <div class="service-num">11</div>
        <h3>Notifications &amp; Audit</h3>
        <p>Real-time alerts and a complete audit trail for every system action.</p>
      </div>
      <div class="service-card">
        <div class="service-num">12</div>
        <h3>Settings &amp; Customization</h3>
        <p>Bill numbering, tax rates, payment methods, logos, bank details — all configurable.</p>
      </div>
    </div>
  </section>

  <!-- ══ WHY CHOOSE ══ -->
  <section class="why-section">
    <div style="position:relative;z-index:2;">
      <span class="section-badge">Why Sling</span>
      <h2 class="section-title" style="color:#fff;">Why Businesses Choose Sling</h2>
      <p class="section-sub">Not just software — a complete business backbone built specifically for Indian retail.</p>
      <div class="why-grid">
        <div class="why-card">
          <div class="why-icon">🇮🇳</div>
          <h3>Made for India</h3>
          <p>GST, rupee formatting, CGST/SGST, HSN codes — the Indian tax system is built in, not an afterthought.</p>
        </div>
        <div class="why-card">
          <div class="why-icon">🏢</div>
          <h3>Multi-Branch Ready</h3>
          <p>Scale from one shop to dozens of branches without switching platforms or losing data visibility.</p>
        </div>
        <div class="why-card">
          <div class="why-icon">🔒</div>
          <h3>Role-Based Access</h3>
          <p>Staff see only what they need. Secure, accountable, and fully controlled from the top down.</p>
        </div>
        <div class="why-card">
          <div class="why-icon">📱</div>
          <h3>API Ready</h3>
          <p>REST API for connecting mobile apps and third-party tools as your tech stack grows.</p>
        </div>
        <div class="why-card">
          <div class="why-icon">⚡</div>
          <h3>One Platform</h3>
          <p>Replace 5+ tools with one — billing, inventory, vendors, customers, reports, all in one login.</p>
        </div>
        <div class="why-card">
          <div class="why-icon">📋</div>
          <h3>Full Audit Trail</h3>
          <p>Every action logged — who did what, when, and with what data. Complete accountability.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ══ WHO IS IT FOR ══ -->
  <section class="whofor-section">
    <span class="section-badge">Who Is It For</span>
    <h2 class="section-title">Built for Your Business Type</h2>
    <p class="section-sub">Whether you sell appliances, furniture, or groceries — Sling adapts to your business.</p>
    <div class="whofor-grid">
      <div class="whofor-card">
        <div class="whofor-icon">🛒</div>
        <h3>Retail Shops</h3>
        <p>POS billing, inventory tracking, and GST-compliant invoices for everyday retail operations.</p>
      </div>
      <div class="whofor-card">
        <div class="whofor-icon">📱</div>
        <h3>Electronics &amp; Appliance Stores</h3>
        <p>IMEI tracking per product, GST compliance, and multi-payment support for high-value sales.</p>
      </div>
      <div class="whofor-card">
        <div class="whofor-icon">🛋️</div>
        <h3>Furniture &amp; Lifestyle Stores</h3>
        <p>Product variations (size &amp; colour), bulk billing, detailed reports, and customer management.</p>
      </div>
      <div class="whofor-card">
        <div class="whofor-icon">🏪</div>
        <h3>Franchise &amp; Multi-Branch</h3>
        <p>Central control with independent branch-level operations, staff, inventory, and billing.</p>
      </div>
      <div class="whofor-card">
        <div class="whofor-icon">🤝</div>
        <h3>Wholesale / B2B</h3>
        <p>Vendor management, purchase orders, vendor ledger, and outstanding balance tracking.</p>
      </div>
      <div class="whofor-card">
        <div class="whofor-icon">🧾</div>
        <h3>Any GST-Registered Business</h3>
        <p>Fully compliant CGST/SGST tax invoices with HSN code support — ready from day one.</p>
      </div>
    </div>
  </section>

  <!-- ══ TESTIMONIAL ══ -->
  <section class="testimonial-section">
    <span class="section-badge">Customer Stories</span>
    <h2 class="section-title" style="margin-bottom:32px;">What Our Customers Say</h2>
    <div class="testimonial-inner">
      <p class="testimonial-text">"Sling has transformed how we handle billing and inventory. The automated GST calculations save us hours every week, and the real-time reports across our branches help us make better business decisions instantly."</p>
      <div class="testimonial-author">Vasantham Home Appliances &amp; Furnitures</div>
      <div class="testimonial-role">Multi-Branch Retail — Tamil Nadu</div>
    </div>
  </section>

  <!-- ══ PRICING ══ -->
  <section class="pricing-section">
    <span class="section-badge">Pricing</span>
    <h2 class="section-title">Simple, Transparent Plans</h2>
    <p class="section-sub">No hidden charges. Pick a plan that fits your business size and grow into the next one.</p>
    <div class="pricing-cards">
      <div class="p-card">
        <div class="p-plan">Starter</div>
        <div class="p-sub">Best for beginners &amp; startups</div>
        <div class="p-price">₹0</div>
        <div class="p-period">/month</div>
        <a href="#" class="p-btn p-btn-outline" onclick="openShopDetailsModal()">Get Started Free</a>
        <ul class="p-features">
          <li>Basic invoicing</li>
          <li>Up to 50 transactions</li>
          <li>Single location</li>
          <li>Email support</li>
        </ul>
      </div>
      <div class="p-card featured">
        <div class="p-badge">Most Popular</div>
        <div class="p-plan">Standard</div>
        <div class="p-sub">Best for small businesses</div>
        <div class="p-price">₹999</div>
        <div class="p-period">/month</div>
        <a href="#" class="p-btn p-btn-primary" onclick="openShopDetailsModal()">Get Started Now</a>
        <ul class="p-features">
          <li>Unlimited invoicing</li>
          <li>Inventory management</li>
          <li>GST compliance</li>
          <li>Priority support</li>
          <li>Advanced reports</li>
        </ul>
      </div>
      <div class="p-card">
        <div class="p-plan">Pro</div>
        <div class="p-sub">Best for growing businesses</div>
        <div class="p-price">₹2499</div>
        <div class="p-period">/month</div>
        <a href="#" class="p-btn p-btn-outline" onclick="openShopDetailsModal()">Get Started Now</a>
        <ul class="p-features">
          <li>Everything in Standard</li>
          <li>Multi-branch support</li>
          <li>API integrations</li>
          <li>Custom reports</li>
          <li>Dedicated support</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- ══ CTA BANNER ══ -->
  <section class="cta-banner">
    <div style="position:relative;z-index:2;">
      <h2>Ready to Simplify Your Business?</h2>
      <p>Start your free trial today — no credit card required. Get set up in minutes.</p>
      <a href="#" class="cta-btn-white" onclick="openShopDetailsModal()">Start Free Trial →</a>
    </div>
  </section>

  <!-- ══ DEMO / CONTACT ══ -->
  <section class="demo-wrap">
    <div class="demo-inner">

      <!-- Left: info -->
      <div class="demo-left">
        <span class="section-badge">Get In Touch</span>
        <h2 style="font-size:2rem;font-weight:800;margin-bottom:10px;">Book a <span class="highlight">Free Demo</span></h2>
        <p style="font-size:0.95rem;">See Sling in action — our team will walk you through the entire platform and answer every question, at no cost.</p>

        <ul class="demo-feature-list">
          <li><span class="demo-check">✓</span> Live walkthrough of POS, billing &amp; inventory</li>
          <li><span class="demo-check">✓</span> GST invoice setup tailored to your business</li>
          <li><span class="demo-check">✓</span> Multi-branch configuration explained</li>
          <li><span class="demo-check">✓</span> No credit card required — completely free</li>
          <li><span class="demo-check">✓</span> Setup support included after demo</li>
        </ul>

        <div class="contact-card">
          <div class="contact-icon3 email">✉️</div>
          <div class="contact-card-text">
            <h4>Email Us</h4>
            <p>support@slingbilling.com</p>
          </div>
        </div>
        <div class="contact-card">
          <div class="contact-icon3 phone">📞</div>
          <div class="contact-card-text">
            <h4>Call Us</h4>
            <p>+91 99940 90424</p>
          </div>
        </div>
        <div class="contact-card">
          <div class="contact-icon3 loc">📍</div>
          <div class="contact-card-text">
            <h4>Location</h4>
            <p>Tamil Nadu, India</p>
          </div>
        </div>
      </div>

      <!-- Right: form -->
      <div class="demo-form-box">
        <h3 style="font-size:1.3rem;font-weight:800;margin-bottom:4px;">Request a Free Demo</h3>
        <p style="font-size:0.82rem;color:var(--gray-color);margin-bottom:20px;">Fill in your details and we'll reach out within 24 hours.</p>
        <hr class="form-divider">
        <form>
          <div class="form-row2">
            <div class="form-group">
              <label>Your Name <span style="color:var(--primary-color);">*</span></label>
              <div class="input-icon-wrap">
                <span class="i-icon">👤</span>
                <input type="text" placeholder="Full name">
              </div>
            </div>
            <div class="form-group">
              <label>Mobile Number <span style="color:var(--primary-color);">*</span></label>
              <div class="input-icon-wrap">
                <span class="i-icon">📱</span>
                <input type="tel" placeholder="+91 00000 00000">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Email Address <span style="color:var(--primary-color);">*</span></label>
            <div class="input-icon-wrap">
              <span class="i-icon">✉</span>
              <input type="email" placeholder="you@company.com">
            </div>
          </div>
          <div class="form-group">
            <label>Business / Shop Name <span style="color:var(--primary-color);">*</span></label>
            <div class="input-icon-wrap">
              <span class="i-icon">🏪</span>
              <input type="text" placeholder="Your business name">
            </div>
          </div>
          <div class="form-group">
            <label>Business Type</label>
            <select style="width:100%;padding:10px 14px;background:#f8f9fa;border:1.5px solid var(--border-color);border-radius:8px;color:var(--dark-color);font-size:0.9rem;outline:none;">
              <option value="">Select your business type</option>
              <option>Retail Shop</option>
              <option>Electronics / Appliance Store</option>
              <option>Furniture / Lifestyle Store</option>
              <option>Franchise / Multi-Branch</option>
              <option>Wholesale / B2B</option>
              <option>Other</option>
            </select>
          </div>
          <button type="button" class="demo-btn" style="margin-top:8px;">
            Book My Free Demo &nbsp;→
          </button>
          <p style="text-align:center;font-size:0.75rem;color:#aaa;margin-top:12px;">🔒 Your information is safe with us. No spam, ever.</p>
        </form>
      </div>

    </div>
  </section>

  <!-- ══ FREE TRIAL MODAL (unchanged) ══ -->
  <div id="shopDetailsModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Shop Details</h2>
        <span class="close" onclick="closeShopDetailsModal()">&times;</span>
      </div>
      <div class="modal-body">
        <form class="row" action="{{route('register')}}" method="post" enctype="multipart/form-data" id="shopCreate">
          @csrf
          <input type="hidden" name="password" value="Test@1234">
          <input type="hidden" name="password_confirmation" value="Test@1234">
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
              <input type="email" id="email" name="email" placeholder="Enter email address" required>
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

  <!-- ══ FOOTER ══ -->
  <footer class="site-footer">
    <span>© 2026 Sling Billing Software.</span> All rights reserved. &nbsp;|&nbsp; GST Compliant · Multi-Branch · Made for India
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
    window.onclick = function(event) {
      const modal = document.getElementById('shopDetailsModal');
      if (event.target == modal) closeShopDetailsModal();
    }
    document.getElementById('shopLogo').addEventListener('change', function(e) {
      const fileName = e.target.files[0]?.name;
      const placeholder = document.querySelector('.file-upload-placeholder span');
      if (fileName) placeholder.textContent = fileName;
    });
  </script>
@endsection
