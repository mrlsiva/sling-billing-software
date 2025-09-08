@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Refund</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">Refund Product</p>
					</div>
					<a href="{{route('branch.order.index', ['company' => request()->route('company')])}}" class="btn btn-outline-primary btn-sm fw-semibold">⬅ Back</a>
				</div>

				<div class="">
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<tr>
								<td colspan="3"><strong>Buyer Name:</strong> {{$order->customer->name}} </td>
				                <td colspan="3"><strong>Mobile No:</strong> {{$order->customer->phone}} </td>
				                <td colspan="2"><strong>Inv. No:</strong> {{$order->bill_id}} </td>
				                <td colspan="2"><strong>Inv. Date:</strong> {{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y') }}</td>
				            </tr>
				            <tr>
				                <td colspan="10"><strong>Mode of Payment:</strong>@foreach($order_payment_details as $order_payment_detail) {{$order_payment_detail->payment->name}} @if($order_payment_detail->card != null)({{$order_payment_detail->card}})@endif @if($order_payment_detail->finance_id != null)({{$order_payment_detail->finance->name}})@endif, @endforeach</td>
				            </tr>
						</table>
					</div>
				</div>

				<div class="row p-3">
					<h5 class="text-dark fs-12 text-uppercase fw-bold">Products</h5>
				</div>

				<form class="row" action="{{ route('branch.order.refunded', ['company' => request()->route('company')]) }}" method="post" enctype="multipart/form-data" id="refund">
                	@csrf

                	<input type="hidden" name="order_id" value="{{$order->id}}">

					<div class="">
						<div class="table-responsive">
							<table class="table align-middle mb-0 table-secondary table-hover table-centered">
								<thead>
									<tr>
										<th>Select</th>
						                <th>S.No</th>
						                <th>Description</th>
						                <th>Qty</th>
						                <th>Rate</th>
						                <th>Taxable</th>
						                <th>Total</th>
						                <th>Refund Quantity</th>
						            </tr>
								</thead>
								<tbody>
						            @foreach($order_details as $order_detail)
						            <tr>
						            	<td>
						            		<input type="checkbox" class="form-check-input" id="product_select_{{$order_detail->id}}" value="{{$order_detail->id}}" name="orders_details[]">
						            	</td>
						                <td>{{ $loop->iteration }}</td>
						                <td>{{$order_detail->name}}</td>
						                <td>{{$order_detail->quantity}}</td>
						                <td>₹ {{ $order_detail->price - $order_detail->tax_amount }}</td>
						                <td>₹ {{$order_detail->tax_amount}}</td>
						                <td>₹ {{$order_detail->price * $order_detail->quantity}}</td>
						                <td>
						                	<input type="number" name="quantity[{{ $order_detail->id }}]" class="form-control refund-qty" placeholder="Enter refund quantity" max="{{ $order_detail->quantity }}" data-max="{{ $order_detail->quantity }}" data-price = "{{$order_detail->price}}" data-checkbox="product_select_{{$order_detail->id}}">
	        								<small class="text-danger error-msg d-none"></small>
						                </td>
						            </tr>
						            @endforeach
						        </tbody>
							</table>
						</div>
					</div>

					<div class="row g-2 p-3">
						<div class="col-md-6">
							<h6 class="fw-semibold my-3">Refund Reason</h6>
							<div class="mb-3">
								<input type="text" name="reason" id="reason" value="{{old('reason')}}" class="form-control" placeholder="Refund Reason">
							</div>
						</div>

						<div class="col-md-6">
							<h6 class="fw-semibold my-3">Return Amount*</h6>
							<div class="mb-3">
								<input type="text" name="amount" id="amount"  class="form-control" placeholder="Refund Amount" readonly="">
							</div>
						</div>
					</div>

					<div class="row g-2 p-3">
						<div class="col-md-6">
							<h6 class="fw-semibold my-3">Refund Mode*</h6>
							<div class="mb-3">
								<select class="form-control" data-choices name="payment" id="payment">
									@foreach($payments as $payment)
									<option value="{{$payment->id}}">{{$payment->name}}</option>
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-md-6">
							<h6 class="fw-semibold my-3">Payment info</h6>
							<div class="mb-3">
								<input type="text" name="detail" id="detail" value="{{old('detail')}}" class="form-control" placeholder="More Info">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-12 justify-content-center">
							<small id="form-error" class="text-danger d-none">Please select at least one product to refund.</small>
						</div>
						<div class="col-12 d-flex justify-content-center mt-2">
							<button type="submit" class="btn btn-primary">
								<i class="ri-save-line"></i> Refund
							</button>
						</div>
					</div>

				</form>

				
			</div>
		</div>
	</div>
@endsection

@section('script')
<script>
	document.addEventListener("DOMContentLoaded", function () {
	    document.querySelectorAll(".refund-qty").forEach(function(input) {
	        input.addEventListener("input", function () {
	            let max = parseInt(this.dataset.max);
	            let val = parseInt(this.value);

	            let errorMsg = this.nextElementSibling; // the <small> tag

	            if (val > max) {
	                this.value = max; // reset to max
	                alert(`Maximum allowed is ${max}`);
	                errorMsg.textContent = `Maximum allowed is ${max}`;
	                errorMsg.classList.remove("d-none");
	            } else if (val < 0) {
	                this.value = null;
	                alert("Quantity cannot be negative");
	                errorMsg.textContent = "Quantity cannot be negative";
	                errorMsg.classList.remove("d-none");
	            } else {
	                errorMsg.textContent = "";
	                errorMsg.classList.add("d-none");
	            }
	        });
	    });
	});
</script>

<script>
	document.addEventListener("DOMContentLoaded", function () {
	    const refundInputs = document.querySelectorAll(".refund-qty");
	    const amountInput = document.getElementById("amount");

	    function updateTotal() {
	        let total = 0;
	        refundInputs.forEach(function(input) {
	            let qty = parseInt(input.value) || 0;
	            let max = parseInt(input.dataset.max);
	            let price = parseFloat(input.dataset.price);
	            let checkboxId = input.dataset.checkbox;
	            let checkbox = document.getElementById(checkboxId);
	            let errorMsg = input.nextElementSibling;

	            // Validation
	            if (qty > max) {
	                qty = max;
	                input.value = max;
	                errorMsg.textContent = `Maximum allowed is ${max}`;
	                errorMsg.classList.remove("d-none");
	            } else if (qty < 0) {
	                qty = 0;
	                input.value = 0;
	                errorMsg.textContent = "Quantity cannot be negative";
	                errorMsg.classList.remove("d-none");
	            } else {
	                errorMsg.textContent = "";
	                errorMsg.classList.add("d-none");
	            }

	            // Auto-check/uncheck
	            if (qty > 0) {
	                checkbox.checked = true;
	            } else {
	                checkbox.checked = false;
	            }

	            // Add to total
	            total += qty * price;
	        });

	        amountInput.value = total.toFixed(2);
	    }

	    // When typing qty → update total
	    refundInputs.forEach(function(input) {
	        input.addEventListener("input", updateTotal);
	    });

	    // When checking/unchecking → reset qty if unchecked and update total
	    document.querySelectorAll(".form-check-input").forEach(function(checkbox) {
	        checkbox.addEventListener("change", function () {
	            if (!this.checked) {
	                // Find related input and reset it
	                let input = document.querySelector(`.refund-qty[data-checkbox="${this.id}"]`);
	                if (input) input.value = null;
	            }
	            updateTotal();
	        });
	    });
	});
</script>

<script>
	document.addEventListener("DOMContentLoaded", function () {
	    const form = document.getElementById("refund");
	    const checkboxes = document.querySelectorAll('input[name="orders_details[]"]');
	    const errorMsg = document.getElementById("form-error");

	    form.addEventListener("submit", function (e) {
	        let checked = false;
	        checkboxes.forEach(cb => {
	            if (cb.checked) {
	                checked = true;
	            }
	        });

	        if (!checked) {
	            e.preventDefault(); // stop form submit
	            errorMsg.classList.remove("d-none");
	        } else {
	            errorMsg.classList.add("d-none"); // hide if valid
	        }
	    });
	});
</script>


@endsection