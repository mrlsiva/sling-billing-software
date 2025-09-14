<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name')}} | Invoice</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
        }

        table {
            width: 98%;
            margin: 0 auto;
            border-collapse: collapse;
            font-size: 14px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f0f0f0;
        }

        .no-border {
            border: none !important;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .logo {
            width: 220px;
            display: block;
            margin: 0 auto 6px auto;
        }

        .bold {
            font-weight: bold;
        }

        .foot-box {
            border: 1px solid #000;
            padding: 8px;
            min-height: 80px;
        }

        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>

    <table>
        <thead>

            <!-- Logo -->
            <tr>
                <td colspan="10" class="no-border center">
                    <img src="{{ asset('storage/' . $user->logo) }}" class="logo" alt="Logo">
                    <!-- <img src="{{ public_path('storage/' . $user->logo) }}" class="logo" alt="Logo"> -->
                    <div style="font-size:20px;" class="bold">TAX INVOICE</div>
                    <div>
                        <strong>{{$user->name}}</strong><br>
                        {{$user->user_detail->address}}<br>
                        CELL: {{$user->phone}}@if ($user->alt_phone != null), 9994333605 @endif @if ($user->email != null) | Email: {{$user->email}} @endif<br>
                        @if ($user->user_detail->gst != null)GSTIN: {{$user->user_detail->gst}} @endif
                    </div>
                </td>
            </tr>
            <!-- Buyer Info -->
            <tr>
                <td colspan="3"><strong>Buyer Name:</strong> {{$order->customer->name}} </td>
                <td colspan="3"><strong>Mobile No:</strong> {{$order->customer->phone}} </td>
                <td colspan="2"><strong>Inv. No:</strong> {{$order->bill_id}} </td>
                <td colspan="2">
                    <strong>Inv. Date:</strong> {{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y') }}
                </td>
            </tr>
            <tr>
                <td colspan="4"><strong>Address:</strong> {{$order->customer->address}} @if($order->customer->pincode != null) - {{$order->customer->pincode}}@endif</td>
                <td colspan="2"><strong>Terms of Delivery:</strong> -</td>
                <td colspan="4"><strong>Mode of Payment:</strong>@foreach($order_payment_details as $order_payment_detail) {{$order_payment_detail->payment->name}} @if($order_payment_detail->card != null)({{$order_payment_detail->card}})@endif @if($order_payment_detail->finance_id != null)({{$order_payment_detail->finance->name}})@endif, @endforeach</td>
            </tr>
            <tr>
                <td colspan="2">
                	<strong>Sales Person:</strong> {{$order->billedBy->name}}
                </td>
                <td colspan="6">
                	@foreach($order_payment_details as $order_payment_detail) 
                		<strong>{{$order_payment_detail->payment->name}}@if($order_payment_detail->card != null)({{$order_payment_detail->card}})@endif @if($order_payment_detail->finance_id != null)({{$order_payment_detail->finance->name}})@endif:</strong> ₹ {{$order_payment_detail->amount}}, 
                	@endforeach
                </td>
                @if($order->is_refunded == 0)
                    <td colspan="2" class="right"><strong>Total:</strong> ₹ {{number_format($order->bill_amount,2)}}</td>
                @else
                    @php
                        $refundAmount = App\Models\Refund::where('order_id', $order->id)->sum('refund_amount');
                        $refundIds = App\Models\Refund::where('order_id',$order->id)->pluck('payment_id')->toArray();
                        $refund_details = App\Models\RefundDetail::whereIn('refund_id',$refundIds)->get();
                    @endphp
                    <td colspan="2"><strong>Order Amount:</strong> ₹ {{number_format($order->bill_amount,2)}}<strong>Refunded Amount:</strong> ₹ {{ number_format($refundAmount, 2) }}<strong>Total:</strong> ₹ {{ number_format($order->bill_amount - $refundAmount, 2) }}</td>
                @endif
            </tr>
            <!-- Column Headings -->
            <tr>
                <th style="width:5%;">S.No</th>
                <th style="width:34%;">Description</th>
                <th style="width:6%;">Qty</th>
                <th style="width:9%;">Rate</th>
                <th style="width:9%;">Taxable</th>
                <th style="width:7%;">CGST%</th>
                <th style="width:7%;">CGST Val</th>
                <th style="width:7%;">SGST%</th>
                <th style="width:7%;">SGST Val</th>
                <th style="width:9%;">Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach($order_details as $order_detail)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{$order_detail->name}}</td>
                <td>{{$order_detail->quantity}}</td>
                <td>₹ {{ $order_detail->price - $order_detail->tax_amount }}</td>
                <td>₹ {{$order_detail->tax_amount}}</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>₹ {{$order_detail->price * $order_detail->quantity}}</td>
            </tr>
            @endforeach
            @if($order->is_refunded == 1)

                @php
                    $refundIds = App\Models\Refund::where('order_id',$order->id)->pluck('payment_id')->toArray();
                    $refund_details = App\Models\RefundDetail::whereIn('refund_id',$refundIds)->get();
                @endphp

                @foreach($refund_details as $refund_detail)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{$refund_detail->name}} (Refunded - {{ \Carbon\Carbon::parse($refund_detail->created_at)->format('d M Y') }})</td>
                        <td>{{$refund_detail->quantity}}</td>
                        <td>₹ {{ $refund_detail->price - $refund_detail->tax_amount }}</td>
                        <td>₹ {{$refund_detail->tax_amount}}</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>₹ {{$refund_detail->price * $refund_detail->quantity}}</td>
                    </tr>
                @endforeach

            @endif
            <!-- Add many more rows here (5…25…100). The header/footer will repeat per page automatically. -->
        </tbody>

        <tfoot>
            <tr>
                <td colspan="5"><strong>Total CGST:</strong></td>
                <td colspan="5">-</td>
            </tr>
            <tr>
                <td colspan="5"><strong>Total SGST:</strong></td>
                <td colspan="5">-</td>
            </tr>

            <tr>
                <td colspan="5"><strong>Total Tax:</strong></td>
                <td colspan="5">
					₹ {{ number_format($order_details->sum(fn($d) => (float)$d->tax_amount * (int)$d->quantity),2)}}
				</td>
            </tr>
            @if($order->is_refunded == 1)
                @php
                    $refundAmount = App\Models\Refund::where('order_id', $order->id)->sum('refund_amount');
                    $refundIds = App\Models\Refund::where('order_id',$order->id)->pluck('payment_id')->toArray();
                    $refund_details = App\Models\RefundDetail::whereIn('refund_id',$refundIds)->get();
                @endphp

                <tr>
                    <td colspan="5"><strong>REFUNDED TAX:</strong></td>
                    <td colspan="5">
                        ₹ {{ number_format($refund_details->sum(fn($d) => (float)$d->tax_amount * (int)$d->quantity),2) }}
                    </td>
                </tr>

                <tr>
                    <td colspan="5"><strong>ORDER AMOUNT:</strong></td>
                    <td colspan="5">₹ {{number_format($order->bill_amount,2)}}</td>
                </tr>

                <tr>
                    <td colspan="5"><strong>REFUNDED AMOUNT:</strong></td>
                    <td colspan="5">₹ {{number_format($refundAmount,2)}}</td>
                </tr>

                <tr>
                    <td colspan="5"><strong>NET TOTAL:</strong></td>
                    <td colspan="5">₹ {{ number_format($order->bill_amount - $refundAmount, 2) }}</td>
                </tr>

            @else
            <tr>
                <td colspan="5"><strong>NET TOTAL:</strong></td>
                <td colspan="5">₹ {{number_format($order->bill_amount,2)}}</td>
            </tr>
            @endif
            <tr>
                <td colspan="5" class="no-border">
                    <div class="foot-box">
                        <strong>Bank Details</strong><br>
                        Name: @if($user->bank_detail->name != null) {{$user->bank_detail->name}} @else - @endif<br>
                        Holder Name: @if($user->bank_detail->holder_name != null) {{$user->bank_detail->holder_name}} @else - @endif<br>
                        Branch: @if($user->bank_detail->branch != null) {{$user->bank_detail->branch}} @else - @endif<br>
                        A/C No: @if($user->bank_detail->account_no != null) {{$user->bank_detail->account_no}} @else - @endif<br>
                        IFSC Code: @if($user->bank_detail->ifsc_code != null) {{$user->bank_detail->ifsc_code}} @else - @endif
                    </div>
                </td>
                <td colspan="5" class="no-border right">
                    <div class="foot-box">
                        <strong>Authorised Signature</strong><br><br><br>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
	<script>
	    window.onload = function() {
	        setTimeout(() => {
	            window.print();
	        }, 500); // half a second delay
	    }
	</script>
</body>

</html>