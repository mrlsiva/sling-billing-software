<div class="table-responsive">
    <table class="table align-middle mb-0 table-hover table-centered">
        <thead class="bg-light-subtle">
            <tr>
                <th>S.No</th>
                <th>Category</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Tax</th>
                <th>Net</th>
                <th>Gross</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase_orders as $purchase_order)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $purchase_order->category->name ?? '' }}</td>
                <td>{{ $purchase_order->product->name ?? '' }}</td>
                <td>{{ $purchase_order->quantity }}</td>
                <td>₹ {{ number_format($purchase_order->price_per_unit,2) }}</td>
                <td>{{ $purchase_order->tax }}%</td>
                <td>₹ {{ number_format($purchase_order->net_cost,2) }}</td>
                <td>₹ {{ number_format($purchase_order->gross_cost,2) }}</td>
                <td>
                    @if($purchase_order->status == 0)
                        <span class="badge bg-danger">Unpaid</span>
                    @elseif($purchase_order->status == 1)
                        <span class="badge bg-success">Paid</span>
                    @else
                        <span class="badge bg-warning">Partial</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-3">
    {!! $purchase_orders->links('pagination::bootstrap-5') !!}
</div>