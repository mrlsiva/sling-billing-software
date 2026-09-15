@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Credit Payment History</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">Credit Payment History</p>
					</div>
					<div>
						<a href="{{route('branch.credit.index', ['company' => request()->route('company')])}}" class="btn btn-outline-primary btn-sm fw-semibold">Back</a>
					</div>
				</div>

				<form method="get" action="{{route('branch.credit.history', ['company' => request()->route('company')])}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Invoice/Customer Name/ Customer Phone" name="customer" value="{{ request('customer') }}" id="searchInput">
                                <span class="input-group-text" id="clearFilter" style="display: {{ request('customer') ? 'inline-flex' : 'none' }}">
                                    <a href="{{ route('branch.credit.history', ['company' => request()->route('company')]) }}" class="link-dark"><i class="ri-close-large-line align-middle fs-20"></i></a>
                                </span>
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
									<th>Date/Time</th>
									<th>Invoice</th>
									<th>Customer</th>
									<th>Phone</th>
									<th>Payment Mode</th>
									<th>Amount</th>
								</tr>
							</thead>
							<tbody>
								@foreach($payments as $payment)
								<tr>
									<td>{{ ($payments->currentPage() - 1) * $payments->perPage() + $loop->iteration }}</td>
									<td>{{ \Carbon\Carbon::parse($payment->paid_on)->format('d-m-Y h:i A') }}</td>
									<td>{{ $payment->credit->order_payment_detail->order->bill_id ?? '-' }}</td>
									<td>{{ $payment->credit->order_payment_detail->order->customer->name ?? '-' }}</td>
									<td>{{ $payment->credit->order_payment_detail->order->customer->phone ?? '-' }}</td>
									<td>{{ $payment->payment->name ?? 'N/A' }}</td>
									<td>₹ {{ number_format($payment->amount, 2) }}</td>
								</tr>
								@endforeach
							</tbody>
						</table>
						@if($payments->isEmpty())
                        	@include('no-data')
                        @endif
					</div>
				</div>
				<div class="card-footer border-0">
					{!! $payments->links('pagination::bootstrap-5') !!}
				</div>

			</div>
		</div>
	</div>
@endsection

@section('script')
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        let searchInput = document.getElementById("searchInput");
        let clearFilter = document.getElementById("clearFilter");

        function toggleClear() {
            if (searchInput.value.trim() !== "") {
                clearFilter.style.display = "inline-flex";
            } else {
                clearFilter.style.display = "none";
            }
        }

        toggleClear();
        searchInput.addEventListener("input", toggleClear);
    });
</script>
@endsection
