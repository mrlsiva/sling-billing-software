<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name')}} | GST Bill</title>
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
                    <div style="font-size:20px;" class="bold">GST BILL</div>
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
                <td colspan="2"><strong>Buyer Name:</strong> {{$gst_bill->customer_name}} </td>
                <td colspan="2"><strong>Mobile No:</strong> {{$gst_bill->customer_phone}} </td>
                <td colspan="2"><strong>Inv. No:</strong> {{$gst_bill->order_id}} </td>
                <td colspan="2">
                    <strong>Inv. Date:</strong> {{ \Carbon\Carbon::parse($gst_bill->transfer_on)->format('l, d F Y h:i A') }}
                </td>
            </tr>
            <tr>
                <td colspan="4"><strong>Address:</strong> {{$gst_bill->customer_address}}</td>
                
            </tr>
            <tr>
                <td colspan="4">
                    <strong>Sales Person:</strong> {{$gst_bill->sold_by}}
                </td>

                <td colspan="6"><strong>Total:</strong> ₹ {{ number_format($gst_bill_details->sum('gross'), 2) }}</td>
            </tr>
            <!-- Column Headings -->
            <tr>
                <th style="width:5%;">S.No</th>
                <th style="width:34%;">Description</th>
                <th style="width:6%;">Qty</th>
                <th style="width:9%;">Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach($gst_bill_details as $gst_bill_detail)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>

                    {{ $gst_bill_detail->product }}
                    <br>
                    <small class="text-muted">IMEI: {{ $gst_bill_detail->imie }}</small>
                </td>
                <td>{{$gst_bill_detail->quantity}}</td>
                <td>₹ {{ $gst_bill_detail->gross}}</td>
            </tr>
            @endforeach
        </tbody>

        <tfoot>

            @if($gst_bill->branch_id == null)
                @php
                    $user_detail = App\Models\UserDetail::where('user_id', $gst_bill->shop_id)->first();
                @endphp
            @else
                @php
                    $user_detail = App\Models\UserDetail::where('user_id', $gst_bill->branch_id)->first();
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
    <script>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500); // half a second delay
        }
    </script>
</body>

</html>