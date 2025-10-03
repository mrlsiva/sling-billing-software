@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Order Report</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card p-3">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">Order Report</p>
					</div>

				</div>

				<form method="get" action="{{route('branch.report.order', ['company' => request()->route('company')])}}">
                    <div class="row mb-2">
                    	<div class="col-md-5">
                    		<div class="mb-2">
                    			<label for="from" class="form-label">From Date</label>
                    			<input type="date" id="from" name="from" value="{{ request('from') }}" class="form-control">
                    		</div>
                    	</div>

                        <div class="col-md-5">
                        	<div class="mb-2">
                        		<label for="to" class="form-label">To Date</label>
                        		<input type="date" id="to" name="to" value="{{ request('to') }}" class="form-control">
                        	</div>
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-primary"> Search </button>
                        </div>
                    </div>
                </form>

				<div class="">

					<div class="d-flex justify-content-end p-3">
						<form method="get" action="{{route('branch.report.order.download_excel', ['company' => request()->route('company')])}}">
							<input type="hidden" class="form-control" name="from" value="{{ request('from') }}">
							<input type="hidden" class="form-control" name="to" value="{{ request('to') }}">
							<button class="btn btn-success"> <i class="ri-file-excel-2-line"></i> Excel </button>
						</form>
						<form method="get" action="{{route('branch.report.order.download_pdf', ['company' => request()->route('company')])}}">
							<input type="hidden" class="form-control" name="from" value="{{ request('from') }}">
							<input type="hidden" class="form-control" name="to" value="{{ request('to') }}">
							<button class="btn btn-success"> <i class="ri-file-pdf-2-line"></i> PDF </button>
						</form>
					</div>

					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Bill ID</th>
									<th>Amount (in ₹)</th>
									<th>Billed on</th>
									<th>Billed by</th>
									<th>Mode of payment</th>
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
										@php
										    $payment_ids = App\Models\OrderPaymentDetail::where('order_id', $order->id)->pluck('payment_id');

										    $payments = App\Models\Payment::whereIn('id', $payment_ids)->pluck('name')->toArray();
										@endphp

										<td>{{ implode(', ', $payments) }}</td>
										<td>
											{{ $order->customer->phone }} ({{ $order->customer->name }})
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

@section('script')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const fromInput = document.getElementById("from");
            const toInput = document.getElementById("to");

            fromInput.addEventListener("change", function () {
                toInput.min = fromInput.value; // set min date
                if (toInput.value && toInput.value < fromInput.value) {
                    toInput.value = fromInput.value; // auto-correct if invalid
                }
            });

            // If "from" already has a value on load, set "to" min accordingly
            if (fromInput.value) {
                toInput.min = fromInput.value;
            }
        });
    </script>
@endsection