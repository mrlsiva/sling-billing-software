<div>
    <p><strong>Invoice No:</strong> {{ $purchase->invoice_no ?? '-' }}</p>
    <p><strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M Y') }}</p>
    <p><strong>Due Date:</strong> @if($purchase->due_date) {{ \Carbon\Carbon::parse($purchase->due_date)->format('d M Y') }} @else - @endif</p>
    <p><strong>Quantity:</strong> {{ $purchase->quantity }}  ({{ $purchase->metric->name }})</p>
    <p><strong>Price per Unit:</strong> ₹ {{number_format( $purchase->price_per_unit,2) }}</p>
    <p><strong>Tax:</strong> {{ $purchase->tax }}%</p>
    <p><strong>Discount:</strong>
        {{ $purchase->discount !== null ? '₹ ' . number_format($purchase->discount, 2) : '-' }}
    </p>
    <p><strong>Net Cost:</strong> ₹ {{number_format($purchase->net_cost,2) }}</p>
    <p><strong>Gross Cost:</strong> ₹ {{number_format( $purchase->gross_cost,2) }}</p>
    <p><strong>Status:</strong> 
    	@if($purchase->status == 0)
    	<span class="badge badge-soft-danger">Unpaid</span>
    	@elseif($purchase->status == 1)
    	<span class="badge badge-soft-success">Paid</span>

    	@elseif($purchase->status == 2)
    	<span class="badge badge-soft-warning">Partially Paid</span>
    	@endif
    </p>
</div>
