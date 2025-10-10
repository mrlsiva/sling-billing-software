<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Invoice</title>
    <style>
        body {
            font-family: monospace;
            width: calc(100% - 10mm);
            padding: 5mm;
            margin: 0;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .items {
        border-collapse: collapse;
        width: 100%;
    }

    .items th,
    .items td {
        border: 1px solid #000; /* Black border for separation */
        padding: 4px 6px;
    }

        th {
            text-align: left;
        }

        hr {
            border: 1px dashed #000;
            margin: 5px 0;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .print-logo img {
            max-width: 280px;
            max-height: 90px;
        }
        .print-logo {
            margin-right: 8px;
            max-width: 40%;
        }
        /* ✅ Flex container for header */
        .print-header {
            display: flex;
            /* justify-content: space-between; */
            align-items: flex-start; /* top align */
            width: 100%;
        }

        .company-info {
            text-align: left;
            line-height: 1.4;
        }
        .d-flex {
            display: flex;
        }
        .flex-column {
            flex-direction: column;
        }
        .flex-row{
            flex-direction: row;
        }
        .mr-2 {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="print-header mb-10">
        <!-- Left: Logo -->
        <div class="print-logo">
            <img src="{{ asset('storage/' . $user->logo) }}" alt="Logo">
        </div>

        <!-- Right: Company Info -->
        <div class="company-info">
            <strong>{{ $user->name }}</strong><br>
            {{ $user->user_detail->address }}<br>
            <div class="d-flex flex-row">
                <div class="mr-2"><strong>Mobile: </strong></div>
                <div class="d-flex flex-column">
                    <div>{{ $user->phone }}</div>
                    <div>{{ $user->alt_phone }}</div>
                </div>
            </div>
            @if($user->user_detail->gst)
            <div class="d-flex flex-row">
                <div class="mr-2"><strong>GST: </strong></div>
                <div class="d-flex flex-column">
                    <div>{{ $user->user_detail->gst }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <table width="100%">
        <tr>
            <td>Invoice: #{{$order->bill_id}}</td>
            <td class="right">Date: {{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y h:i A') }}</td>
        </tr>
    </table>

    <table class="items" width="100%">
        <thead>
            <tr>
                
                <th>Items</th>
                <th class="right">Rate</th>
                <th class="right">Qty</th>
                <th class="right">Discount</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order_details as $order_detail)
            <tr>
                <td>
                    <div class="d-flex flex-row">
                        <div>{{ $loop->iteration }}.</div>
                        <div>{{$order_detail->name}}</div>
                    </div>
                </td>
                <td class="right"> {{ number_format($order_detail->price,2) }}</td>
                <td class="right">{{$order_detail->quantity}}</td>
                @if($order_detail->discount_type == 1)
                    <td class="right">{{$order_detail->discount}}</td>
                @elseif($order_detail->discount_type == 2)
                    <td class="right">{{$order_detail->discount}}%</td>
                @else
                    <td class="right">-</td>
                @endif
                <td class="right"> {{number_format($order_detail->price * $order_detail->quantity,2)}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table width="100%">
        <tr>
            <td class="bold">Total Items</td>
            <td class="bold">=</td>
            <td class="">{{ number_format($order_details->sum(fn($d) => (int)$d->quantity))}}</td>
        </tr>
        <tr>
            <td class="bold">Total Amount (Inclusive of all tax)</td>
            <td class="bold">=</td>

            <td class="right">₹ {{ number_format($order_details->sum(fn($d) => $d->price * $d->quantity), 2) }}</td>
        </tr>
        <tr>
            <td class="bold">Discount Amount</td>
            <td class="bold">=</td>
            <td class="right">₹ {{number_format($order->total_product_discount,2)}}</td>
        </tr>
    </table>

    <hr>

    <div class="center">
        No Exchange<br>
        வாங்கிய பொருட்கள் வாபஸ் வாங்கப்படாது
    </div>

    <script>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
