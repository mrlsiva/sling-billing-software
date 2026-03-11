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
                <th>Total Sales</th>
                @if(request()->route('branch') == 0)
                <th>Total Purchase</th>
                <th>Vendor Paid</th>
                <th>Total Refund</th>
                <th>Profit</th>
                @endif
            </tr>

            <tr>
                <td>₹ {{ number_format($totalSales,2) }}</td>
                @if(request()->route('branch') == 0)
                <td>₹ {{ number_format($totalPurchase,2) }}</td>
                <td>₹ {{ number_format($totalVendorPaid,2) }}</td>
                <td>₹ {{ number_format($totalRefund,2) }}</td>
                <td><b>₹ {{ number_format($profit,2) }}</b></td>
                @endif
            </tr>

        </table>

         @if(request()->route('branch') == 0)
        <table>

            <tr class="section">
                <th colspan="7">Purchase Report</th>
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

        </table>


        <table>

            <tr class="section">
                <th colspan="6">Vendor Payment Report</th>
            </tr>

            <tr>
                <th>S.No</th>
                <th>Vendor</th>
                <th>Purchase Invoice</th>
                <th>Amount</th>
                <th>Paid On</th>
                <th>Comment</th>
            </tr>

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

        </table>


        <table>

            <tr class="section">
                <th colspan="8">Purchase Refund Report</th>
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

        </table>
        @endif


        <table>

            <tr class="section">
                <th colspan="7">Order Report</th>
            </tr>

            <tr>
                <th>S.No</th>
                <th>Branch / HO</th>
                <th>Bill ID</th>
                <th>Amount</th>
                <th>Billed On</th>
                <th>Billed By</th>
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
                <td>{{ $order->customer->phone }} ({{ $order->customer->name }})</td>
            </tr>
            @endforeach

        </table>


    </body>
</html>