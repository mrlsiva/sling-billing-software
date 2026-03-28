<!DOCTYPE html>
<html>
    <head>

        <meta charset="utf-8">

        <title>Purchase Report</title>

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

        <h2 style="text-align:center">Purchase Report</h2>


        <table>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Date</th>
                    <th>Invoice</th>
                    <th>Vendor</th>
                    <th>Category</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Value (In ₹)</th>
                    <th>GST (In ₹)</th>
                    <th>Base (In ₹)</th>
                    <th>NLC (In ₹)</th>
                </tr>
            </thead>

            <tbody>
                @php $i=1; @endphp

                @foreach($datas as $data)
                    @php
                        $qty = $data->quantity;
                        $value = $data->gross_cost;
                        $gst = $data->gross_cost - $data->net_cost;
                        $base = $data->net_cost;
                        $nlc = $qty > 0 ? ($value / $qty) : 0;
                    @endphp

                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d M Y') }}</td>
                        <td>{{ $data->invoice_no }}</td>
                        <td>{{ $data->vendor->name ?? '-' }}</td>
                        <td>{{ $data->category->name ?? '-' }}</td>
                        <td>{{ $data->product->name ?? '-' }}</td>
                        <td class="text-end">{{ $qty }}</td>
                        <td class="text-end">{{ number_format($value,2) }}</td>
                        <td class="text-end">{{ number_format($gst,2) }}</td>
                        <td class="text-end">{{ number_format($base,2) }}</td>
                        <td class="text-end">{{ number_format($nlc,2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>