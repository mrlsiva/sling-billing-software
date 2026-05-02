@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Credits</title>
@endsection

@section('body')
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Credit</p>
					</div>
					<div>
						<a href="{{route('report.daily', ['company' => request()->route('company'),'branch' => 0])}}" class="btn btn-outline-primary btn-sm fw-semibold">Back</a>
					</div>
				</div>

				@if ($errors->any())
		            <div class="alert alert-danger">
		                <strong>Whoops!</strong> There were some problems with your input.<br><br>
		                <ul>
		                    @foreach ($errors->all() as $error)
		                        <li>{{ $error }}</li>
		                    @endforeach
		                </ul>
		            </div>
		        @endif

		        <form method="get" action="{{ route('credit', ['company' => request()->route('company'),'date' =>  request('date', now()->format('Y-m-d')) ]) }}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Invoice/Customer Name/ Customer Phone" name="customer" value="{{ request('customer') }}" id="searchInput">
                                <span class="input-group-text" id="clearFilter" style="display: {{ request('customer') ? 'inline-flex' : 'none' }}"><a href="{{ route('credit', ['company' => request()->route('company'),'date' =>  request('date', now()->format('Y-m-d')) ]) }}" class="link-dark"><i class="ri-close-large-line align-middle fs-20"></i></a></span>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-primary"> Search </button>
                        </div>
                    </div>
                </form>

                @if(session('error_alert'))
		        <div class="alert alert-danger">
		          <strong>Warning! </strong>{{ session('error_alert') }}<br>
		        </div>
		        @endif

				<div class="">

					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Invoice ID</th>
									<th>Name</th>
									<th>Phone</th>
									<th>Amount</th>
									<th>Remaining Amount</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
									@foreach($credits as $credit)
									<tr>
										<td>{{ ($credits->currentPage() - 1) * $credits->perPage() + $loop->iteration }}</td>
										<td>{{$credit->order_payment_detail->order->bill_id}}</td>
										<td>{{$credit->order_payment_detail->order->customer->name}}</td>
										<td>{{$credit->order_payment_detail->order->customer->phone }}</td>
										<td>{{$credit->amount }}</td>
										<td>
											@if($credit->remaining_amount > 0)
												{{$credit->remaining_amount }}
											@else
												-
											@endif
										</td>
										<td>
											@if($credit->status == 1)
										        <span class="badge bg-success">Paid</span>
										    @elseif($credit->status == 0)
										        <button type="button"  class="btn btn-warning btn-sm pay-credit" data-id="{{ $credit->id }}" data-name="{{ $credit->order_payment_detail->order->customer->name }}" data-invoice="{{ $credit->order_payment_detail->order->bill_id }}" data-remaining="{{ $credit->remaining_amount }}"> Pay </button>
										    @elseif($credit->status == 2)
										        <button type="button"  class="btn btn-warning btn-sm pay-credit" data-id="{{ $credit->id }}" data-name="{{ $credit->order_payment_detail->order->customer->name }}" data-invoice="{{ $credit->order_payment_detail->order->bill_id }}" data-remaining="{{ $credit->remaining_amount }}"> Pay </button>
										    @endif
										</td>
										<td>
											<a href="#" class="link-dark view-detail" data-id="{{ $credit->id }}">
											    <i class="ri-eye-line align-middle fs-20" title="View Credit Payments"></i>
											</a>
										</td>
									</tr>
									@endforeach
							</tbody>
						</table>
						@if($credits->isEmpty())
                        	@include('no-data')
                        @endif
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					{!! $credits->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>

	<div class="modal fade" id="creditPaymentsModal" tabindex="-1">
	    <div class="modal-dialog modal-lg">
	        <div class="modal-content">
	            <div class="modal-header">
	                <h5 class="modal-title">Credit Payments</h5>
	                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
	            </div>

	            <div class="modal-body">
	                <table class="table table-bordered">
	                    <thead>
	                        <tr>
	                            <th>Payment Method</th>
	                            <th>Amount</th>
	                            <th>Date</th>
	                        </tr>
	                    </thead>
	                    <tbody id="credit-payments-body">
	                        <!-- Filled dynamically -->
	                    </tbody>
	                </table>
	            </div>
	        </div>
	    </div>
	</div>

	<div class="modal fade" id="payCreditModal" tabindex="-1">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            
	            <div class="modal-header">
	                <h5 class="modal-title">Pay Credit</h5>
	                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
	            </div>

	            <form id="creditPaymentForm">
	                <div class="modal-body">

	                    <input type="hidden" name="credit_id" id="credit_id">

	                    <div class="mb-2">
	                        <label>Customer Name</label>
	                        <input type="text" id="customer_name" class="form-control" readonly>
	                    </div>

	                    <div class="mb-2">
	                        <label>Invoice</label>
	                        <input type="text" id="invoice" class="form-control" readonly>
	                    </div>

	                    <div class="mb-2">
	                        <label>Payment Method</label>
	                        <select name="payment_id" class="form-control" required>
	                            <option value="">Select</option>
	                            @foreach($payments as $payment)
	                                <option value="{{ $payment->id }}">{{ $payment->name }}</option>
	                            @endforeach
	                        </select>
	                    </div>

	                    <div class="mb-2">
	                        <label>Amount</label>
	                        <input type="number" name="amount" id="amount" class="form-control" required>
	                        <small class="text-danger d-none" id="amount-error">Amount exceeds remaining</small>
	                    </div>

	                </div>

	                <div class="modal-footer">
	                    <button type="submit" class="btn btn-success">Submit</button>
	                </div>

	            </form>

	        </div>
	    </div>
	</div>
@endsection

@section('script')
	<script>
		$(document).on('click', '.view-detail', function(e) {
		    e.preventDefault();

		    let creditId = $(this).data('id');

		    $.ajax({
		        url: 'payments/' + creditId,
		        type: 'GET',
		        success: function(response) {

		            let tbody = $('#credit-payments-body');
		            tbody.empty();

		            if(response.length === 0){
		                tbody.append('<tr><td colspan="3" class="text-center">No Payments Found</td></tr>');
		            } else {
		                $.each(response, function(index, item) {
		                    tbody.append(`
		                        <tr>
		                            <td>${item.payment ? item.payment.name : ''}</td>
		                            <td>${item.amount}</td>
		                            <td>${item.paid_on}</td>
		                        </tr>
		                    `);
		                });
		            }

		            $('#creditPaymentsModal').modal('show');
		        }
		    });
		});
	</script>

	<script>
		let maxAmount = 0;

		$(document).on('click', '.pay-credit', function() {

		    let creditId = $(this).data('id');
		    let name = $(this).data('name');
		    let invoice = $(this).data('invoice');
		    maxAmount = parseFloat($(this).data('remaining'));

		    $('#credit_id').val(creditId);
		    $('#customer_name').val(name);
		    $('#invoice').val(invoice);
		    $('#amount').val('');
		    $('#amount-error').addClass('d-none');

		    $('#payCreditModal').modal('show');
		});

		// Prevent exceeding amount
		$(document).on('input', '#amount', function() {
		    let value = parseFloat($(this).val());

		    if(value > maxAmount){
		        $('#amount-error').removeClass('d-none');
		        $(this).val('');
		    } else {
		        $('#amount-error').addClass('d-none');
		    }
		});
	</script>

	<script>
		$('#creditPaymentForm').submit(function(e){
		    e.preventDefault();

		    $.ajax({
		        url: 'payments/store',
		        method: 'POST',
		        data: $(this).serialize() + '&_token={{ csrf_token() }}',
		        success: function(){
		            alert('Payment added');
		            location.reload();
		        }
		    });
		});
	</script>

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

	        // Run on load (for prefilled request values)
	        toggleClear();

	        // Run on typing
	        searchInput.addEventListener("input", toggleClear);
	    });
	</script>
@endsection