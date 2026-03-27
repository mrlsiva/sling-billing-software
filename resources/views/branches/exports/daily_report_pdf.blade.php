<!DOCTYPE html>
<html>
    <head>

        <meta charset="utf-8">

        <title>Daily Report</title>

        <style>

            body{
                font-family: DejaVu Sans;
                font-size:12px;
            }

            table{
                width:100%;
                border-collapse:collapse;
                margin-bottom:20px;
            }

            th,td{
                border:1px solid #ddd;
                padding:6px;
                text-align:center;
            }

            th{
                background:#f2f2f2;
            }

            .section{
                background:#e9ecef;
                font-weight:bold;
            }

            .summary{
                margin-bottom:20px;
            }

        </style>

    </head>

    <body>

        <h2 style="text-align:center">Daily Report</h2>


        <table class="summary">

            <tr>
                <td>Today Sales</td>
                <td>₹ {{ number_format($totalSales,2) }}</td>
            </tr>

            <tr>
                <td>Product In</td>
                <td>₹ {{ number_format($productInAmount,2) }}</td>
            </tr>

            <tr>
                <td>Product Out</td>
                <td>₹ {{ number_format($productOutAmount,2) }}</td>
            </tr>

        </table>


        <table>

            <tr class="section">
                <th colspan="8">Order Report</th>
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
                            {{ $pay->payment->name ?? '-' }} (₹{{ number_format($pay->amount,2) }})<br>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
                <td>{{ $order->customer->phone }} ({{ $order->customer->name }})</td>
            </tr>
            @endforeach

        </table>

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

    </body>
</html>