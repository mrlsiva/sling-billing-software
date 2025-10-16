@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Dashboard</title>
@endsection

@section('body')

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<p class="card-title">Welcome {{Auth::user()->user_name}} - Branch</p>

				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-xl-12 col-md-12">
		<div class="row">

			<div class="col-md-3 col-md-3">
				<div class="card">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-3 card-title">Today Order </p>

								<h4 class="fw-bold d-flex align-items-center gap-2 mb-0"> {{$today_orders}}</h4>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-md-3">
				<div class="card">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-3 card-title">Total Order</p>
								<h4 class="fw-bold d-flex align-items-center gap-2 mb-0">
									{{$total_orders}}
								</h4>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-md-3">
				<div class="card">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-3 card-title">Today Sales </p>
								<h4 class="fw-bold d-flex align-items-center gap-2 mb-0"> {{$today_order_amount}}</h4>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-md-3">
				<div class="card">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-3 card-title">Total Sales </p>
								<h4 class="fw-bold d-flex align-items-center gap-2 mb-0"> {{$total_order_amount}}</h4>
							</div>
						</div>
					</div>
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