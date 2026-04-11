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
.branch-section-header { background: #f4f6fa; border-radius: 10px; padding: 10px 16px; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; }
.branch-section-header i { font-size: 18px; color: #2d3561; }
.branch-section-header span { font-size: 14px; font-weight: 700; color: #1a1f36; text-transform: uppercase; letter-spacing: .5px; }
</style>

@php
    $category_count    = App\Models\Category::where('user_id', Auth::user()->id)->count();
    $sub_category_count = App\Models\SubCategory::where('user_id', Auth::user()->id)->count();
    $product_count     = App\Models\Product::where('user_id', Auth::user()->id)->count();
@endphp

<div class="row mb-3">
	<div class="col-12">
		<div class="card welcome-card">
			<div class="card-body d-flex align-items-center justify-content-between">
				<div>
					<h5 class="text-white fw-bold mb-1">Welcome, {{ Auth::user()->user_name }}</h5>
					<span class="text-white-50" style="font-size:13px;">Head Office Dashboard &mdash; {{ now()->format('d M Y') }}</span>
				</div>
				<i class="ri-building-line text-white-50" style="font-size:36px;"></i>
			</div>
		</div>
	</div>
</div>

<div class="row g-3 mb-4">
	<div class="col-md-3">
		<div class="card dash-stat-card h-100">
			<div class="card-body d-flex align-items-center gap-3">
				<div class="dash-icon" style="background:#e8f4fd; color:#0d6efd;">
					<i class="ri-store-2-line"></i>
				</div>
				<div>
					<div class="dash-label">Branches</div>
					<div class="dash-value">{{ count($branches) }}</div>
					<div class="dash-sub">Active branches</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card dash-stat-card h-100">
			<div class="card-body d-flex align-items-center gap-3">
				<div class="dash-icon" style="background:#e8fdf0; color:#198754;">
					<i class="ri-list-check-2"></i>
				</div>
				<div>
					<div class="dash-label">Categories</div>
					<div class="dash-value">{{ $category_count }}</div>
					<div class="dash-sub">{{ $sub_category_count }} sub-categories</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card dash-stat-card h-100">
			<div class="card-body d-flex align-items-center gap-3">
				<div class="dash-icon" style="background:#fff8e1; color:#f59e0b;">
					<i class="ri-shopping-basket-line"></i>
				</div>
				<div>
					<div class="dash-label">Products</div>
					<div class="dash-value">{{ $product_count }}</div>
					<div class="dash-sub">Total products</div>
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
					<div class="dash-value">&#x20B9; {{ number_format($branches->sum('total_sales'), 0) }}</div>
					<div class="dash-sub">All branches combined</div>
				</div>
			</div>
		</div>
	</div>
</div>

@foreach($branches as $branch)
<div class="branch-section-header mb-0">
	<i class="ri-store-line"></i>
	<span>{{ $branch->user_name }}</span>
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
					<div class="dash-value">{{ $branch->today_orders }}</div>
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
					<div class="dash-value">{{ $branch->total_orders }}</div>
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
					<div class="dash-value">&#x20B9; {{ number_format($branch->today_sales, 2) }}</div>
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
					<div class="dash-value">&#x20B9; {{ number_format($branch->total_sales, 2) }}</div>
					<div class="dash-sub">All time</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endforeach

@endsection