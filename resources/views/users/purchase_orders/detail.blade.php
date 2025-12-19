<div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover table-centered">
            <thead class="bg-light-subtle">
                <tr>
                    <th>S.No</th>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price Per Unit</th>
                    <th>Tax</th>
                    <th>Discount</th>
                    <th>Net Cost</th>
                    <th>Gross Cost</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase_orders as $purchase_order)
                <tr>
                    <td>
                        {{ $loop->iteration }}
                    </td>
                    <td>{{$purchase_order->category->name}} - {{$purchase_order->sub_category->name}}</td>
                    <td>{{$purchase_order->product->name}}</td>
                    <td>{{$purchase_order->quantity}} ({{$purchase_order->metric->name}})</td>
                    <td>₹ {{number_format( $purchase_order->price_per_unit,2) }}</td>
                    <td>{{ $purchase_order->tax }}%</td>
                    <td>{{ $purchase_order->discount !== null ? '₹ ' . number_format($purchase_order->discount, 2) : '-' }}</td>
                    <td>₹ {{number_format($purchase_order->net_cost,2) }}</td>
                    <td>₹ {{number_format( $purchase_order->gross_cost,2) }}</td>
                    <td>
                        @if($purchase_order->status == 0)
                        <span class="badge badge-soft-danger">Unpaid</span>
                        @elseif($purchase_order->status == 1)
                        <span class="badge badge-soft-success">Paid</span>

                        @elseif($purchase_order->status == 2)
                        <span class="badge badge-soft-warning">Partially Paid</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
