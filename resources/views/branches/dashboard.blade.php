@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Dashboard</title>
@endsection

@section('body')

<style>
.dash-stat-card { border-radius: 12px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,.07); }
.dash-stat-card .card-body { padding: 20px 24px; }
.dash-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
.dash-label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: #8a92a6; margin-bottom: 6px; }
.dash-value { font-size: 24px; font-weight: 700; color: #1a1f36; margin: 0; line-height: 1.2; }
.dash-sub { font-size: 11px; color: #8a92a6; margin-top: 4px; }
.welcome-card { background: linear-gradient(135deg, #1a1f36 0%, #2d3561 100%); border: none; border-radius: 12px; }
.welcome-card .card-body { padding: 20px 24px; }
</style>

<div class="row mb-3">
	<div class="col-12">
		<div class="card welcome-card">
			<div class="card-body d-flex align-items-center justify-content-between">
				<div>
					<h5 class="text-white fw-bold mb-1">Welcome, {{ Auth::user()->user_name }}</h5>
					<span class="text-white-50" style="font-size:13px;">Branch Dashboard &mdash; {{ now()->format('d M Y') }}</span>
				</div>
				<i class="ri-store-2-line text-white-50" style="font-size:36px;"></i>
			</div>
		</div>
	</div>
</div>

<div class="row g-3 mb-4">
	<div class="col-md-3">
		<div class="card dash-stat-card h-100">
			<div class="card-body d-flex align-items-center gap-3">
				<div class="dash-icon" style="background:#e8f4fd; color:#0d6efd;">
					<i class="ri-shopping-bag-line"></i>
				</div>
				<div>
					<div class="dash-label">Today Orders</div>
					<div class="dash-value">{{ $today_orders }}</div>
					<div class="dash-sub">{{ now()->format('d M Y') }}</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card dash-stat-card h-100">
			<div class="card-body d-flex align-items-center gap-3">
				<div class="dash-icon" style="background:#e8fdf0; color:#198754;">
					<i class="ri-stack-line"></i>
				</div>
				<div>
					<div class="dash-label">Total Orders</div>
					<div class="dash-value">{{ $total_orders }}</div>
					<div class="dash-sub">All time</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card dash-stat-card h-100">
			<div class="card-body d-flex align-items-center gap-3">
				<div class="dash-icon" style="background:#fff8e1; color:#f59e0b;">
					<i class="ri-currency-line"></i>
				</div>
				<div>
					<div class="dash-label">Today Sales</div>
					<div class="dash-value">&#x20B9; {{ number_format($today_order_amount, 2) }}</div>
					<div class="dash-sub">{{ now()->format('d M Y') }}</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card dash-stat-card h-100">
			<div class="card-body d-flex align-items-center gap-3">
				<div class="dash-icon" style="background:#fdedf0; color:#dc3545;">
					<i class="ri-bar-chart-line"></i>
				</div>
				<div>
					<div class="dash-label">Total Sales</div>
					<div class="dash-value">&#x20B9; {{ number_format($total_order_amount, 2) }}</div>
					<div class="dash-sub">All time</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row d-none">
	<div class="col-xl-12 col-md-12">
		<div class="card">
			<div class="card-body">
				<div class="container py-4">
					<div class="notification-header">Notification</div>

					<!-- Tabs -->
					<ul class="nav nav-pills mb-4 gap-2" id="notificationTabs" role="tablist">
						<li class="nav-item"><button class="nav-link active" id="all-tab" data-bs-toggle="pill"
								data-bs-target="#all" type="button">All</button></li>
						<li class="nav-item"><button class="nav-link" id="orderPlaced-tab" data-bs-toggle="pill"
								data-bs-target="#orderPlaced" type="button">Order Placed</button></li>
						<li class="nav-item"><button class="nav-link" id="orderPurchased-tab" data-bs-toggle="pill"
								data-bs-target="#orderPurchased" type="button">Order Purchased</button></li>
						<li class="nav-item"><button class="nav-link" id="productAdded-tab" data-bs-toggle="pill"
								data-bs-target="#productAdded" type="button">Product Added</button></li>
						<li class="nav-item"><button class="nav-link" id="categoryAdded-tab" data-bs-toggle="pill"
								data-bs-target="#categoryAdded" type="button">Category Added</button></li>
					</ul>

					<div class="tab-content" id="notificationTabsContent">
						<!-- All Tab -->
						<div class="tab-pane fade show active" id="all" role="tabpanel">
							<h6 class="date-label">Today</h6>
							<div class="notification-item d-flex">
								<div class="notification-line"></div>
								<div>
									<div class="notification-title">
										<span class="fw-semibold">Order Placed </span> 
										<span class="fw-normal">From</span> 
										<span class="text-primary">Branch 1</span> 
										<span class="fw-normal notification-dot"> · </span>
										<span class="fw-normal">Order ID </span>
										<span class="fw-semibold">#INV-202508</span> 
										<span class="fw-normal notification-dot"> · </span>
										<span class="fw-normal">Amount</span>
										<span class="fw-semibold">Rs.20980</span>
									</div>
									<div class="notification-meta">31 March 2025 <span class="fw-normal notification-dot-small"> · </span> 09.02 AM</div>
								</div>
							</div>
							<div class="notification-item d-flex">
								<div class="notification-line"></div>
								<div>
									<div class="notification-title">
										<span class="fw-semibold">Product Added </span> 
										<span class="fw-normal notification-dot"> · </span>
										<span class="fw-normal">Category </span>
										<span class="fw-semibold">Washing Machine</span> 
										<span class="fw-normal notification-dot"> · </span>
										<span class="fw-normal">Sub category</span>
										<span class="fw-semibold">Sony</span>
										<span class="fw-normal notification-dot"> · </span>
										<span class="text-primary">Washing Machine</span> 
									</div>
									
									<div class="notification-meta">31 March 2025 <span class="fw-normal notification-dot-small"> · </span> 09.02 AM</div>
								</div>
							</div>
						</div>

						<!-- Order Placed Tab -->
						<div class="tab-pane fade" id="orderPlaced" role="tabpanel">
							<h6 class="date-label">Today</h6>
							<div class="notification-item d-flex">
								<div class="notification-line"></div>
								<div>
									<div class="notification-title">
										<span class="fw-semibold">Order Placed </span> 
										<span class="fw-normal">From</span> 
										<span class="text-primary">Branch 1</span> 
										<span class="fw-normal notification-dot"> · </span>
										<span class="fw-normal">Order ID </span>
										<span class="fw-semibold">#INV-202508</span> 
										<span class="fw-normal notification-dot"> · </span>
										<span class="fw-normal">Amount</span>
										<span class="fw-semibold">Rs.20980</span>
									</div>
									<div class="notification-meta">31 March 2025 <span class="fw-normal notification-dot-small"> · </span> 09.02 AM</div>
								</div>
							</div>
							<div class="notification-item d-flex">
								<div class="notification-line"></div>
								<div>
									<div class="notification-title">
										<span class="fw-semibold">Product Added </span> 
										<span class="fw-normal notification-dot"> · </span>
										<span class="fw-normal">Category </span>
										<span class="fw-semibold">Washing Machine</span> 
										<span class="fw-normal notification-dot"> · </span>
										<span class="fw-normal">Sub category</span>
										<span class="fw-semibold">Sony</span>
										<span class="fw-normal notification-dot"> · </span>
										<span class="text-primary">Washing Machine</span> 
									</div>
									
									<div class="notification-meta">31 March 2025 <span class="fw-normal notification-dot-small"> · </span> 09.02 AM</div>
								</div>
							</div>
						</div>

						<!-- Order Purchased Tab -->
						<div class="tab-pane fade" id="orderPurchased" role="tabpanel">
							<h6 class="date-label">15 October 2025</h6>
							<div class="notification-item d-flex">
								<div class="notification-line"></div>
								<div>
									<div class="notification-title">
										<span class="fw-semibold">Order Placed </span> 
										<span class="fw-normal">From</span> 
										<span class="text-primary">Branch 1</span> 
										<span class="fw-normal notification-dot"> · </span>
										<span class="fw-normal">Order ID </span>
										<span class="fw-semibold">#INV-202508</span> 
										<span class="fw-normal notification-dot"> · </span>
										<span class="fw-normal">Amount</span>
										<span class="fw-semibold">Rs.20980</span>
									</div>
									<div class="notification-meta">31 March 2025 <span class="fw-normal notification-dot-small"> · </span> 09.02 AM</div>
								</div>
							</div>
							<div class="notification-item d-flex">
								<div class="notification-line"></div>
								<div>
									<div class="notification-title">
										<span class="fw-semibold">Product Added </span> 
										<span class="fw-normal notification-dot"> · </span>
										<span class="fw-normal">Category </span>
										<span class="fw-semibold">Washing Machine</span> 
										<span class="fw-normal notification-dot"> · </span>
										<span class="fw-normal">Sub category</span>
										<span class="fw-semibold">Sony</span>
										<span class="fw-normal notification-dot"> · </span>
										<span class="text-primary">Washing Machine</span> 
									</div>
									
									<div class="notification-meta">31 March 2025 <span class="fw-normal notification-dot-small"> · </span> 09.02 AM</div>
								</div>
							</div>
						</div>
					</div>

				</div>
				@endsection