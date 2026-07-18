<table>
    <tr>
        <th colspan="2"><b>Daily Report Summary - {{$date}}</b></th>
    </tr>

    <tr>
        <td>Today Sales</td>
        <td>{{ number_format($totalSales,2) }}</td>
    </tr>

    <tr>
        <td>Product In</td>
        <td>{{ number_format($productInAmount,2) }}</td>
    </tr>

    <tr>
        <td>Product Out</td>
        <td>{{ number_format($productOutAmount,2) }}</td>
    </tr>

    <tr>
        <td>Credit Amount</td>
        <td>{{ number_format($credit_amount, 2) }}</td>
    </tr>

    <tr>
        <td>Discount Amount</td>
        <td>{{ number_format($orders->sum('order_discount'), 2) }}</td>
    </tr>

</table>


<br><br>

@if(!$orders->isEmpty())
<table>
    <thead>
        <tr>
            <th colspan="7"><b>Order Report</b></th>
        </tr>

        <tr>
            <th>S.No</th>
            <th>Branch</th>
            <th>Bill ID</th>
            <th>Amount</th>
            <th>Billed On</th>
            <th>Billed By</th>
            <th>Payment Mode</th>
            <th>Customer</th>
        </tr>
    </thead>

    <tbody>
        @foreach($orders as $order)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
                @if($order->branch_id)
                {{ $order->branch->user_name }}
                @else
                {{ $order->shop->user_name }}
                @endif
            </td>
            <td>{{ $order->bill_id }}</td>
            <td>{{ $order->bill_amount - ($order->is_refunded ? ($order->total_refund ?? 0) : 0) }}</td>
            <td>{{ \Carbon\Carbon::parse($order->billed_on)->format('d-m-Y') }}</td>
            <td>{{ $order->billedBy->name }}</td>
            <td>
                @if($order->payments->count())
                    @foreach($order->payments as $pay)
                        {{ $pay->payment->name ?? '-' }} (₹{{ $pay->amount }})<br>
                    @endforeach
                    @if($order->order_discount != 0)
                        <span class="badge bg-primary">
                            Discount 
                            (₹ {{ number_format($order->order_discount, 2) }})
                        </span><br>
                    @endif
                    @if($order->is_refunded)
                        <span class="badge bg-primary">
                            Refund 
                            ₹ {{ number_format($order->total_refund, 2) }}
                            <br>
                            {{ \Carbon\Carbon::parse($order->refunds->last()->refund_on)->format('d-m-Y') }}
                        </span>
                    @endif
                @else
                    -
                @endif
            </td>
            <td>{{ $order->customer->phone }} ({{ $order->customer->name }})</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if(!$productIn->isEmpty())
<h5>Product IN</h5>
<table>
    <tr>
        <th>Product</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Amount</th>
    </tr>

    @foreach($productIn as $item)
    @php
        $price = $item->product->price ?? 0;
        $amount = $price * $item->quantity;
    @endphp
    <tr>
        <td>{{ $item->product->name ?? '-' }}</td>
        <td>{{ $item->quantity }}</td>
        <td>{{ $price }}</td>
        <td>{{ $amount }}</td>
    </tr>
    @endforeach

    <tr>
        <td colspan="3"><b>Total</b></td>
        <td><b>{{ $productInAmount }}</b></td>
    </tr>
</table>
@endif

@if(!$productOut->isEmpty())
<h5>Product OUT</h5>
<table>
    <tr>
        <th>Product</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Amount</th>
    </tr>

    @foreach($productOut as $item)
    @php
        $price = $item->product->price ?? 0;
        $amount = $price * $item->quantity;
    @endphp
    <tr>
        <td>{{ $item->product->name ?? '-' }}</td>
        <td>{{ $item->quantity }}</td>
        <td>{{ $price }}</td>
        <td>{{ $amount }}</td>
    </tr>
    @endforeach

    <tr>
        <td colspan="3"><b>Total</b></td>
        <td><b>{{ $productOutAmount }}</b></td>
    </tr>
</table>
@endif