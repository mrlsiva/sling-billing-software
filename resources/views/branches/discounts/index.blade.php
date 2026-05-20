@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Order Discount</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card p-3">
				<div class="card-header d-flex justify-content-between align-items-center">
					
					<p class="card-title mb-0">Order Discount</p>

				</div>

				<form class="mt-3" method="get" action="{{route('branch.discount.index', ['company' => request()->route('company')])}}">
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

                        <div class="col-md-2 mt-4">
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
									<th>Discount (in ₹)</th>
									<th>Billed on</th>
									<th>Billed by</th>
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
											{{ $order->order_discount}}
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