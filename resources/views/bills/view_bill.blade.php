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
                <td colspan="2"><strong>Buyer Name:</strong> {{$order->customer->name}} </td>
                <td colspan="2"><strong>GST:</strong> @if($order->customer->gst != null) {{$order->customer->gst}} @else - @endif </td>
                <td colspan="2"><strong>Mobile No:</strong> {{$order->customer->phone}} </td>
                <td colspan="2"><strong>Inv. No:</strong> {{$order->bill_id}} </td>
                <td colspan="2">
                    <strong>Inv. Date:</strong> {{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y') }}
                </td>
            </tr>
            <tr>
                <td colspan="4"><strong>Address:</strong> {{$order->customer->address}} @if($order->customer->pincode != null) - {{$order->customer->pincode}}@endif</td>
                <td colspan="6"><strong>Mode of Payment:</strong>
                    @foreach($order_payment_details as $order_payment_detail)
                        <strong>
                            {{ $order_payment_detail->payment->name }}
                            @if($order_payment_detail->card)
                                ({{ $order_payment_detail->card }})
                            @endif
                            @if($order_payment_detail->finance_id)
                                ({{ $order_payment_detail->finance->name }})
                            @endif:
                        </strong>
                        ₹ {{ $order_payment_detail->amount }}
                        @if(! $loop->last), @endif
                    @endforeach

                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <strong>Sales Person:</strong> {{$order->billedBy->name}}
                </td>

                <td colspan="6"><strong>Total:</strong> ₹ {{number_format($order->bill_amount,2)}}</td>
            </tr>
            <!-- Column Headings -->
            <tr>
                <th style="width:5%;">S.No</th>
                <th style="width:34%;">Description</th>
                <th style="width:6%;">Qty</th>
                <th style="width:6%;">IMEI</th>
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
                <td>
                     @php
                        $sizeName   = $order_detail->size_id   ? $order_detail->size->name   : null;
                        $colorName  = $order_detail->colour_id ? $order_detail->colour->name : null;
                    @endphp

                    {{ $order_detail->name }}
                    {{ ($sizeName || $colorName) ? '(' . trim(($sizeName ?? '') . ' - ' . ($colorName ?? ''), ' - ') . ')' : '' }}

                </td>
                <td>{{$order_detail->quantity}}</td>
                <td>
                    @if($order_detail->imei != null)
                        {{$order_detail->imei}}
                    @else
                        -
                    @endif
                </td>
                <td>₹ {{ $order_detail->price - $order_detail->tax_amount }}</td>
                <td>₹ {{$order_detail->tax_amount}}</td>
                <td>₹ {{ number_format((float)($order_detail->tax_percent / 2), 2) }}</td>
                <td>₹ {{ number_format((float)($order_detail->tax_amount / 2), 2) }}</td>
                <td>₹ {{ number_format((float)($order_detail->tax_percent / 2), 2) }}</td>
                <td>₹ {{ number_format((float)($order_detail->tax_amount / 2), 2) }}</td>
                <td>₹ {{$order_detail->price * $order_detail->quantity}}</td>
            </tr>
            @endforeach
        </tbody>

        <tfoot>

            @php
                $taxGroups = App\Models\OrderDetail::select('tax_percent', \DB::raw('SUM(CAST(tax_amount AS DECIMAL(10,2))) as total_tax'))->where('order_id', $order->id)->groupBy('tax_percent')->get();
            @endphp

            @foreach($taxGroups as $tax)
                <tr>
                    <td colspan="5"><strong>Total {{ $tax->tax_percent }}% Tax:</strong></td>
                    <td colspan="5">₹ {{ number_format($tax->total_tax, 2) }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="5"><strong>Total Tax:</strong></td>
                <td colspan="5">
                    ₹ {{ number_format($order_details->sum(fn($d) => (float)$d->tax_amount * (int)$d->quantity),2)}}
                </td>
            </tr>
            <tr>
                <td colspan="5"><strong>NET TOTAL:</strong></td>
                <td colspan="5">₹ {{number_format($order->bill_amount,2)}}</td>
            </tr>

            @if($order->branch_id == null)
                @php
                    $user_detail = App\Models\UserDetail::where('user_id', $order->shop_id)->first();
                @endphp
            @else
                @php
                    $user_detail = App\Models\UserDetail::where('user_id', $order->branch_id)->first();
                @endphp
            @endif

            @if($user_detail->show_bank_detail == 1)

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
            </tr>

            @endif
        </tfoot>
    </table>
</body>

</html>