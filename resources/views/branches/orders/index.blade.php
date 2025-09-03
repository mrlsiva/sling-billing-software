@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Orders</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Order</p>
					</div>

				</div>

				<div class="">
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Bill ID</th>
									<th>Amount</th>
									<th>Billed On</th>
									<th>Billed By</th>
									<th>Customer</th>
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
											{{ $order->customer->name }}
										</td>
										<td>
											<i class="ri-eye-line align-middle fs-20" title="View More"></i>
											<i class="ri-printer-line align-middle fs-20" title="Print"></i>
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