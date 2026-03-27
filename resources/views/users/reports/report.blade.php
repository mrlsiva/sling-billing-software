@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Order Report</title>
@endsection

@section('body')

<style>
.card:hover{
    transform: translateY(-5px);
    transition: 0.3s;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}
</style>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <p class="card-title">Report</p>
                </div>
            </div>
            <div class="card-body pt-2 ">
                

                <div class="row mt-5">

				    <div class="col-xl-3 col-md-6">
				        <div class="card border shadow-sm">
				            <div class="card-body text-center">
				                <i class="ri-shopping-cart-2-line text-primary fs-1"></i>
				                <h5 class="mt-3">Order Report</h5>
				                <p class="text-muted">View all order reports</p>
				                <a href="{{route('report.order', ['company' => request()->route('company'),'branch' => 0])}}" class="btn btn-primary btn-sm">View Report</a>
				            </div>
				        </div>
				    </div>

				    <div class="col-xl-3 col-md-6">
				        <div class="card border shadow-sm">
				            <div class="card-body text-center">
				                <i class="ri-receipt-line text-danger fs-1"></i>
				                <h5 class="mt-3">Daily Report</h5>
				                <p class="text-muted">Track daily progress</p>
				                <a href="{{route('report.daily', ['company' => request()->route('company'),'branch' => 0])}}" class="btn btn-danger btn-sm">View Report</a>
				            </div>
				        </div>
				    </div>

				    <div class="col-xl-3 col-md-6">
				        <div class="card border shadow-sm">
				            <div class="card-body text-center">
				                <i class="ri-shopping-bag-4-line text-success fs-1"></i>
				                <h5 class="mt-3">Purchase Report</h5>
				                <p class="text-muted">Track purchase movements</p>
				                <a href="{{route('report.purchase', ['company' => request()->route('company')])}}" class="btn btn-success btn-sm">View Report</a>
				            </div>
				        </div>
				    </div>

				    <div class="col-xl-3 col-md-6">
				        <div class="card border shadow-sm">
				            <div class="card-body text-center">
				                <i class="ri-money-rupee-circle-line text-warning fs-1"></i>
				                <h5 class="mt-3">Sales Report</h5>
				                <p class="text-muted">Track your sales</p>
				                <a href="{{route('report.sales', ['company' => request()->route('company'),'branch' => 0])}}" class="btn btn-warning btn-sm">View Report</a>
				            </div>
				        </div>
				    </div>

				    <div class="col-xl-3 col-md-6">
				        <div class="card border shadow-sm">
				            <div class="card-body text-center">
				                <i class="ri-money-rupee-circle-line text-warning fs-1"></i>
				                <h5 class="mt-3">Transfer Report</h5>
				                <p class="text-muted">Track your product movements</p>
				                <a href="{{route('report.transfer', ['company' => request()->route('company'),'branch' => 0])}}" class="btn btn-warning btn-sm">View Report</a>
				            </div>
				        </div>
				    </div>

				</div>
            </div>
        </div>
    </div>
</div>

@endsection
