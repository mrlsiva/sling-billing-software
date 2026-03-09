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

                <a href="{{route('report', ['company' => request()->route('company')])}}" class="btn btn-sm btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </a>

            </div>
            <div class="card-body pt-2 ">
                <ul class="nav nav-tabs nav-justified">

                	 <li class="nav-item">
                        <a href="{{route('report.daily', ['company' => request()->route('company'),'branch' => 0])}}" class="nav-link {{ request()->route('branch') == 0 ? 'active' : '' }}" id="{{Auth::user()->id}}">
                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
                            <span class="d-none d-sm-block"><i class="ri-store-2-line me-2"></i>{{Auth::user()->user_name}}</span>
                        </a>
                    </li>

                   
                    @foreach($branches as $branch)
                    	<li class="nav-item">
	                        <a href="{{route('report.daily', ['company' => request()->route('company'),'branch' => $branch->id])}}" class="nav-link {{ request()->route('branch') == $branch->id ? 'active' : '' }}" id="{{$branch->id}}">
	                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
	                            <span class="d-none d-sm-block"><i class="ri-store-2-line me-2"></i></i>{{$branch->user_name}}</span>
	                        </a>
                    	</li>
                    @endforeach

                </ul>

                <div class="tab-content pt-2 text-muted">
                    <div class="tab-pane show active" id="homeTabsJustified">

                        <form method="get" action="{{route('report.daily', ['company' => request()->route('company'),'branch' => request('branch')])}}">
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
						    <form method="get" action="{{route('report.daily.download_excel', ['company' => request()->route('company'),'branch' => request('branch')])}}">
						        <input type="hidden" name="date" value="{{ request('date') }}">
						        <button class="btn btn-success">
						            <i class="ri-file-excel-2-line"></i> Excel
						        </button>
						    </form>

						    <form method="get" action="{{route('report.daily.download_pdf', ['company' => request()->route('company'),'branch' => request('branch')])}}">
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
						                <h6 class="text-muted">Total Sales</h6>
						                <h4 class="text-success">₹ {{ number_format($totalSales,2) }}</h4>
						            </div>
						        </div>
						    </div>

						    <div class="col-md-3">
						        <div class="card border shadow-sm">
						            <div class="card-body text-center">
						                <h6 class="text-muted">Total Purchase</h6>
						                <h4 class="text-primary">₹ {{ number_format($totalPurchase,2) }}</h4>
						            </div>
						        </div>
						    </div>

						    <div class="col-md-3">
						        <div class="card border shadow-sm">
						            <div class="card-body text-center">
						                <h6 class="text-muted">Vendor Paid</h6>
						                <h4 class="text-warning">₹ {{ number_format($totalVendorPaid,2) }}</h4>
						            </div>
						        </div>
						    </div>

						    <div class="col-md-3">
							    <div class="card border shadow-sm">
							        <div class="card-body text-center">
							            <h6 class="text-muted">Refund Amount</h6>
							            <h4 class="text-danger">
							                ₹ {{ number_format($totalRefund,2) }}
							            </h4>
							        </div>
							    </div>
							</div>

						    <div class="col-md-3">
						        <div class="card border shadow-sm">
						            <div class="card-body text-center">
						                <h6 class="text-muted">Profit</h6>
						                <h4 class="{{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
						                    ₹ {{ number_format($profit,2) }}
						                </h4>
						            </div>
						        </div>
						    </div>


						</div>

                        <h5 class="mt-4">Purchase Report</h5>

						<table class="table table-bordered">

							<thead class="table-light">
								<tr>
								<th>S.No</th>
								<th>Vendor</th>
								<th>Invoice No</th>
								<th>Product</th>
								<th>Quantity</th>
								<th>Amount</th>
								<th>Date</th>
								</tr>
							</thead>

							<tbody>

								@foreach($purchases as $purchase)

									<tr>
										<td>{{ $loop->iteration }}</td>

										<td>{{ $purchase->vendor->name }}</td>

										<td>{{ $purchase->invoice_no }}</td>

										<td>{{ $purchase->product->name }}</td>

										<td>{{ $purchase->quantity }}</td>

										<td>{{ $purchase->gross_cost }}</td>

										<td>{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M Y') }}</td>

									</tr>

								@endforeach

							</tbody>
						</table>
						@if($purchases->isEmpty())
                            @include('no-data')
                        @endif

						<h5 class="mt-4">Vendor Payment Report</h5>

						<div class="table-responsive">
							<table class="table table-bordered table-hover">

								<thead class="table-light">
									<tr>
										<th>S.No</th>
										<th>Vendor</th>
										<th>Purchase Invoice</th>
										<th>Amount</th>
										<th>Paid On</th>
										<th>Comment</th>
									</tr>
								</thead>

								<tbody>

									@foreach($payments as $payment)

										<tr>

											<td>{{ $loop->iteration }}</td>

											<td>
											{{ $payment->purchaseOrder->vendor->name ?? '-' }}
											</td>

											<td>
											{{ $payment->purchaseOrder->invoice_no ?? '-' }}
											</td>

											<td>
											₹ {{ number_format($payment->amount,2) }}
											</td>

											<td>
											{{ \Carbon\Carbon::parse($payment->paid_on)->format('d M Y') }}
											</td>

											<td>
											{{ $payment->comment ?? '-' }}
											</td>

										</tr>

									@endforeach

								</tbody>

							</table>
							@if($payments->isEmpty())
                                @include('no-data')
                            @endif
						</div>

						<h5 class="mt-4">Purchase Refund Report</h5>

						<div class="table-responsive">
							<table class="table table-bordered table-hover">

								<thead class="table-light">
									<tr>
										<th>S.No</th>
										<th>Vendor</th>
										<th>Invoice</th>
										<th>Product</th>
										<th>Quantity</th>
										<th>Refund Amount</th>
										<th>Refund On</th>
										<th>Refunded By</th>
									</tr>
								</thead>

								<tbody>

									@foreach($refunds as $refund)

									<tr>

										<td>{{ $loop->iteration }}</td>

										<td>{{ $refund->vendor->name }}</td>

										<td>{{ $refund->purchase_order->invoice_no }}</td>

										<td>{{ $refund->purchase_order->product->name }}</td>

										<td>{{ $refund->quantity }}</td>

										<td>₹ {{ number_format($refund->refund_amount,2) }}</td>

										<td>{{ \Carbon\Carbon::parse($refund->refund_on)->format('d M Y') }}</td>

										<td>{{ $refund->refundedBy->name }}</td>

									</tr>

									@endforeach

								</tbody>

							</table>
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

                    </div>
                </div>
            </div>
            <div class="card-footer border-0">
				{!! $orders->withQueryString()->links('pagination::bootstrap-5') !!}
			</div>
        </div>
    </div>
</div>

@endsection
