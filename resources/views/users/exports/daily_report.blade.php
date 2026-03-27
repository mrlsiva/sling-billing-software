<table>
    <tr>
        <th colspan="2"><b>Daily Report Summary</b></th>
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

    @if(request()->route('branch') == 0)
    <tr>
        <td>Today Purchase</td>
        <td>{{ number_format($totalPurchase,2) }}</td>
    </tr>

    <tr>
        <td>Today Vendor Payment</td>
        <td>{{ number_format($totalVendorPaid,2) }}</td>
    </tr>

    <tr>
        <td>Today Vendor Refund Amount</td>
        <td>{{ number_format($totalRefund,2) }}</td>
    </tr>
    @endif

</table>


<br><br>

@if(request()->route('branch') == 0)
<table>
    <thead>
        <tr>
            <th colspan="7"><b>Purchase Report</b></th>
        </tr>

        <tr>
            <th>S.No</th>
            <th>Vendor</th>
            <th>Invoice No</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Amount</th>
            <th>Date</th>
        </tr>
    </thead>

    <tbody>
        @foreach($purchases as $purchase)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $purchase->vendor->name }}</td>
            <td>{{ $purchase->invoice_no }}</td>
            <td>{{ $purchase->product->name }}</td>
            <td>{{ $purchase->quantity }}</td>
            <td>{{ $purchase->gross_cost }}</td>
            <td>{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>


<br><br>


<table>
    <thead>
        <tr>
            <th colspan="6"><b>Vendor Payment Report</b></th>
        </tr>

        <tr>
            <th>S.No</th>
            <th>Vendor</th>
            <th>Purchase Invoice</th>
            <th>Amount</th>
            <th>Paid On</th>
            <th>Comment</th>
        </tr>
    </thead>

    <tbody>
        @foreach($payments as $payment)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $payment->purchaseOrder->vendor->name ?? '-' }}</td>
            <td>{{ $payment->purchaseOrder->invoice_no ?? '-' }}</td>
            <td>{{ number_format($payment->amount,2) }}</td>
            <td>{{ \Carbon\Carbon::parse($payment->paid_on)->format('d M Y') }}</td>
            <td>{{ $payment->comment ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>


<br><br>


<table>
    <thead>
        <tr>
            <th colspan="8"><b>Purchase Refund Report</b></th>
        </tr>

        <tr>
            <th>S.No</th>
            <th>Vendor</th>
            <th>Invoice</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Refund Amount</th>
            <th>Refund On</th>
            <th>Refunded By</th>
        </tr>
    </thead>

    <tbody>
        @foreach($refunds as $refund)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $refund->vendor->name }}</td>
            <td>{{ $refund->purchase_order->invoice_no }}</td>
            <td>{{ $refund->purchase_order->product->name }}</td>
            <td>{{ $refund->quantity }}</td>
            <td>{{ number_format($refund->refund_amount,2) }}</td>
            <td>{{ \Carbon\Carbon::parse($refund->refund_on)->format('d M Y') }}</td>
            <td>{{ $refund->refundedBy->name }}</td>
        </tr>
        @endforeach
    </tbody>
</table>


<br><br>

@endif

<h5>Product IN</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
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
    </tbody>
</table>

<h5>Product OUT</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
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
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th colspan="7"><b>Order Report</b></th>
        </tr>

        <tr>
            <th>S.No</th>
            <th>Branch / HO</th>
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
            <td>{{ $order->bill_amount }}</td>
            <td>{{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y') }}</td>
            <td>{{ $order->billedBy->name }}</td>
            <td>
                @if($order->payments->count())
                    @foreach($order->payments as $pay)
                        {{ $pay->payment->name ?? '-' }} 
                        (₹{{ $pay->amount }})<br>
                    @endforeach
                @else
                    -
                @endif
            </td>
            <td>{{ $order->customer->phone }} ({{ $order->customer->name }})</td>
        </tr>
        @endforeach
    </tbody>
</table>