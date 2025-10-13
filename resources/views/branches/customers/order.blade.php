@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Customer Order History</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">Customer - {{$customer->name}} ({{$customer->phone}}) Order History</p>
					</div>
					<div>
						<a class="btn btn-outline-primary btn-sm fw-semibold" href="{{route('branch.customer.index', ['company' => request()->route('company')])}}"><i class='bx bxs-arrow-to-left'></i> Back</a>
					</div>

				</div>

				<form method="get" action="{{route('branch.customer.order', ['company' => request()->route('company'),'id' => request()->route('id')])}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Customer Name/ Customer Phone/ Customer GST/ Bill No" name="order" value="{{ request('order') }}">
                                <span class="input-group-text"><a href="{{route('branch.customer.order', ['company' => request()->route('company'),'id' => request()->route('id')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-primary"> Search </button>
                        </div>
                    </div>
                </form>

				<div class="">
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Bill ID</th>
									<th>Amount (In ₹)</th>
									<th>Billed On</th>
									<th>Billed By</th>
									<th>Customer</th>
									<th>Customer GST</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								@foreach($orders as $order)
									<tr>
										<td>
											{{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}
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
										<td>
											@if($order->customer->gst != null)
												{{ $order->customer->gst }}
											@else
												-
											@endif
										</td>
										<td>

											<a href="{{ route('branch.billing.view_bill', ['company' => request()->route('company'),'id' => $order->id ]) }}" class="link-dark" target="_blank"><i class="ri-eye-line align-middle fs-20" title="View Bill"></i></a>
											
											<a href="{{ route('branch.billing.get_bill', ['company' => request()->route('company'),'id' => $order->id ]) }}" class="link-dark" target="_blank"><i class="ri-printer-line align-middle fs-20" title="Print Bill"></i></a>

											@if($order->is_refunded == 0)
												<a href="{{ route('branch.order.refund', ['company' => request()->route('company'),'id' => $order->id ]) }}" class="link-dark"><i class="ri-p2p-fill align-middle fs-20" title="Refund"></i></a>
											@endif
											
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					{!! $orders->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>
@endsection