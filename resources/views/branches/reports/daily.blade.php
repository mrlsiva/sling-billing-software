@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Daily Report</title>
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
    
                <p class="card-title mb-0">Daily Report</p>

                <a href="{{route('branch.report', ['company' => request()->route('company')])}}" class="btn btn-sm btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </a>

            </div>
            <div class="card-body pt-2 ">



            	<form method="get" action="{{route('branch.report.daily', ['company' => request()->route('company'),'branch' => request('branch')])}}">
            		<div class="row mb-2">
            			<div class="col-md-11">
            				<div class="mb-2">
            					<label for="date" class="form-label">Date</label>
            					<input type="date" id="date" name="date" value="{{ request('date') }}" class="form-control">
            				</div>
            			</div>

            			<div class="col-md-1 mt-4">
            				<button class="btn btn-primary"> Search </button>
            			</div>
            		</div>
            	</form>

            	<div class="d-flex justify-content-end p-3 gap-2">
            		<form method="get" action="{{route('branch.report.daily.download_excel', ['company' => request()->route('company'),'branch' => request('branch')])}}">
            			<input type="hidden" name="date" value="{{ request('date') }}">
            			<button class="btn btn-success">
            				<i class="ri-file-excel-2-line"></i> Excel
            			</button>
            		</form>

            		<form method="get" action="{{route('branch.report.daily.download_pdf', ['company' => request()->route('company'),'branch' => request('branch')])}}">
            			<input type="hidden" name="date" value="{{ request('date') }}">
            			<button class="btn btn-success">
            				<i class="ri-file-pdf-2-line"></i> PDF
            			</button>
            		</form>
            	</div>

            	<div class="row mb-3">

            		<div class="col-md-3">
                        <div class="card border shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="text-muted">
                                    Today Sales
                                    <i class="ri-eye-fill text-primary me-2"
                                       style="cursor:pointer;"
                                       data-bs-toggle="modal"
                                       data-bs-target="#salesModal"></i>
                                </h6>
                                <h4 class="text-success">₹ {{ number_format($totalSales,2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Product In</h6>
                                <h4 class="text-success">₹ {{ number_format($productInAmount, 2) }} </h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Product Out</h6>
                                <h4 class="text-danger">₹ {{ number_format($productOutAmount, 2) }} </h4>
                            </div>
                        </div>
                    </div>


            	</div>

            	<h5 class="mt-4">Order Report</h5>

            	<div class="table-responsive">
            		<table class="table table-bordered table-hover">
            			<thead class="table-light">
            				<tr>
            					<th>S.No</th>
            					<th>Branch/ HO</th>
            					<th>Bill ID</th>
            					<th>Amount (In ₹)</th>
            					<th>Billed On</th>
            					<th>Billed By</th>
                                <th>Mode Of Payment</th>
            					<th>Customer</th>
            				</tr>
            			</thead> 
            			<tbody>
            				@foreach($orders as $order)
            				<tr>
            					<td>
            						{{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}
            					</td>
            					<td>
            						@if($order->branch_id != null)
            						{{$order->branch->user_name}}
            						@else
            						{{$order->shop->user_name}}
            						@endif
            					</td>
            					<td>
            						{{$order->bill_id}}
            					</td>
            					<td>
            						{{$order->bill_amount}}
            					</td>
            					<td>
            						{{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y') }}
            					</td>
            					<td>
            						{{ $order->billedBy->name }}
            					</td>
                                <td>
                                    @forelse($order->payments as $payment)
                                        <span class="badge bg-primary">
                                            {{ $payment->payment->name ?? 'N/A' }} 
                                            ₹ {{ number_format($payment->amount, 2) }}
                                        </span><br>
                                    @empty
                                        -
                                    @endforelse
                                </td>
            					<td>
            						{{ $order->customer->phone }} ({{ $order->customer->name }})
            					</td>
            				</tr>
            				@endforeach
            			</tbody>
            		</table>
            		@if($orders->isEmpty())
            		  @include('no-data')
            		@endif
            	</div>

                <h5 class="mt-4">Product In</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>S.No</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Amount (In ₹)</th>
                            </tr>
                        </thead> 
                        <tbody>
                            @foreach($productIn as $index => $product_in)
                                @php
                                    $price = $product_in->product->price ?? 0;
                                    $amount = $price * $product_in->quantity;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $product_in->product->name ?? '-' }}</td>
                                    <td>{{ $product_in->quantity }}</td>
                                    <td>₹ {{ number_format($amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($productIn->isEmpty())
                        @include('no-data')
                    @endif
                </div>

                <h5 class="mt-4">Product Out</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>S.No</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Amount (In ₹)</th>
                            </tr>
                        </thead> 
                        <tbody>
                            @foreach($productOut as $index => $product_out)
                                @php
                                    $price = $product_out->product->price ?? 0;
                                    $amount = $price * $product_out->quantity;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $product_out->product->name ?? '-' }}</td>
                                    <td>{{ $product_out->quantity }}</td>
                                    <td>₹ {{ number_format($amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($productOut->isEmpty())
                        @include('no-data')
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="salesModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Payment Mode Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Mode</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $index => $order)
                            @foreach($order->payments as $payment)
                                <tr>
                                    <td>{{ $loop->parent->iteration }}</td>
                                    <td>{{ $payment->payment->name ?? 'N/A' }}</td>
                                    <td>₹ {{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No Data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

@endsection
