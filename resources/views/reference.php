<td>
	@if($shop->user_detail->payment_method == 1)
		<span class="badge bg-soft-primary text-primary">Monthly</span>
	@elseif($shop->user_detail->payment_method == 2)
		<span class="badge bg-soft-primary text-primary">Quarterly</span>
	@elseif($shop->user_detail->payment_method == 3)
		<span class="badge bg-soft-primary text-primary">Semi-Yearly</span>
	@elseif($shop->user_detail->payment_method == 4)
		<span class="badge bg-soft-primary text-primary">Yearly</span>
	@else
		-
	@endif
</td>



@php

	$paymentDate = \Carbon\Carbon::parse($shop->user_detail->payment_date);
	$paymentMethod = $shop->user_detail->payment_method;

	switch ($paymentMethod) {
		case 1:
			$nextPaymentDate = $paymentDate->copy()->addMonth();
			break;
		case 2:
			$nextPaymentDate = $paymentDate->copy()->addMonths(3);
			break;
		case 3:
			$nextPaymentDate = $paymentDate->copy()->addMonths(6);
			break;
		case 4:
			$nextPaymentDate = $paymentDate->copy()->addYear();
			break;
		default:
			$nextPaymentDate = null;
	}
@endphp

<td>{{ $nextPaymentDate ? $nextPaymentDate->format('d M Y') : '-' }}</td>


<div class="col-md-4">
	<div class="mb-3">
		<label for="secondary_colour" class="form-label">Payment Method</label>
		<select class="form-control" data-choices name="payment_method" id="payment_method">
			<option value=""> Choose Payment</option>
			<option value="1">Monthly</option>
			<option value="2">Quarterly</option>
			<option value="3">Semi-Yearly</option>
			<option value="4">Yearly</option>
		</select>
	</div>
</div>

<div class="col-md-4">
	<div class="mb-3">
		<label for="secondary_colour" class="form-label">Payment Method</label>
		<select class="form-control" data-choices name="payment_method" id="payment_method">
			<option value=""> Choose Payment</option>
			<option value="1"  {{$user->user_detail->payment_method == 1 ? 'selected' : '' }}>Monthly</option>
			<option value="2" {{$user->user_detail->payment_method == 2 ? 'selected' : '' }}>Quarterly</option>
			<option value="3" {{$user->user_detail->payment_method == 3 ? 'selected' : '' }}>Semi-Yearly</option>
			<option value="4" {{$user->user_detail->payment_method == 4 ? 'selected' : '' }}>Yearly</option>
		</select>
	</div>
</div>

<div class="col-md-4">
	<div class="mb-3">
		<label for="payment_date" class="form-label">Payment Date</label>
		<input type="date" id="payment_date" name="payment_date" value="{{ $user->user_detail->payment_date }}" class="form-control" placeholder="Enter Payment Date">
	</div>
</div>



<div class="py-3 border-bottom">
	<h5 class="text-dark fs-12 text-uppercase fw-bold">Payment Method:</h5>
	@if($user->user_detail->payment_method == 1)
		<span class="badge bg-soft-primary text-primary">Monthly</span>
	@elseif($user->user_detail->payment_method == 2)
		<span class="badge bg-soft-primary text-primary">Quarterly</span>
	@elseif($user->user_detail->payment_method == 3)
		<span class="badge bg-soft-primary text-primary">Semi-Yearly</span>
	@elseif($user->user_detail->payment_method == 4)
		<span class="badge bg-soft-primary text-primary">Yearly</span>
	@else
		-
	@endif
</div>

<div class="py-3 border-bottom">
	<h5 class="text-dark fs-12 text-uppercase fw-bold">Payment Date:</h5>
	<p class="fw-medium mb-0">@if($user->user_detail->payment_date != null) {{ \Carbon\Carbon::parse($user->user_detail->payment_date)->format('d M Y') }} @else - @endif</p>
</div>

@php
use Carbon\Carbon;

$paymentDate = Carbon::parse($user->user_detail->payment_date);
$paymentMethod = $user->user_detail->payment_method;

switch ($paymentMethod) {
	case 1:
		$nextPaymentDate = $paymentDate->copy()->addMonth();
		break;
	case 2:
		$nextPaymentDate = $paymentDate->copy()->addMonths(3);
		break;
	case 3:
		$nextPaymentDate = $paymentDate->copy()->addMonths(6);
		break;
	case 4:
		$nextPaymentDate = $paymentDate->copy()->addYear();
		break;
	default:
		$nextPaymentDate = null;
}
@endphp

<div class="py-3 border-bottom">
	<h5 class="text-dark fs-12 text-uppercase fw-bold">Next Payment Date:</h5>
	<p class="fw-medium mb-0">{{ $nextPaymentDate ? $nextPaymentDate->format('d M Y') : '-' }}</p>
</div>