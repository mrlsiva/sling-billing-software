<!DOCTYPE html>
<html>
    <head>

        <meta charset="utf-8">

        <title>Sales Report</title>

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

        <h2 style="text-align:center">Sales Report</h2>


        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date Time</th>
                    <th>Issued By</th>
                    <th>Sales By</th>
                    <th>Customer</th>
                    <th>Mobile</th>
                    <th>Address</th>
                    <th>Category</th>
                    <th>Subcategory</th>
                    <th>Item</th>
                    <th>Item Code</th>
                    <th>Qty</th>
                    <th>Gross (In ₹)</th>
                    <th>Tax (In ₹)</th>
                    <th>Net (In ₹)</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $totalGross = 0;
                    $totalTax   = 0;
                    $totalNet   = 0;
                @endphp

                @foreach($orders as $order)
                    @foreach($order->details as $detail)

                        @php
                            // total refunded qty for this product in this order
                            $refundedQty = 0;

                            if ($order->is_refunded) {
                                foreach ($order->refunds as $refund) {
                                    foreach ($refund->details as $rDetail) {
                                        if ($rDetail->product_id == $detail->product_id) {
                                            $refundedQty += $rDetail->quantity;
                                        }
                                    }
                                }
                            }

                            $finalQty = $detail->quantity - $refundedQty;

                            // avoid negative (safety)
                            if ($finalQty < 0) $finalQty = 0;

                            $gross = $detail->price * $finalQty;
                            $tax   = $detail->tax_amount * $finalQty;
                            $net   = ($detail->price - $detail->tax_amount) * $finalQty;
                        @endphp

                        <tr>
                            <td>{{ $order->bill_id }}</td>
                            <td>{{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y H:i') }}</td>
                            <td>User</td>
                            <td>{{ $order->billedBy->name ?? '' }}</td>
                            <td>{{ $order->customer->name ?? '' }}</td>
                            <td>{{ $order->customer->phone ?? '' }}</td>
                            <td>{{ $order->customer->address ?? '' }}</td>
                            <td>{{ $detail->product->category->name ?? '' }}</td>
                            <td>{{ $detail->product->sub_category->name ?? '' }}</td>
                            <td>{{ $detail->name }}</td>
                            <td>{{ $detail->product->code }}</td>
                            <td>
                                {{ $finalQty }}
                                @if($refundedQty > 0)
                                <small class="text-danger">(Refunded: {{ $refundedQty }})</small>
                                @endif
                            </td>

                            <td>{{ $gross }}</td>
                            <td>{{ $tax }}</td>
                            <td>{{ $net }}</td>
                        </tr>

                    @endforeach
                @endforeach
            </tbody>
        </table>
    </body>
</html>