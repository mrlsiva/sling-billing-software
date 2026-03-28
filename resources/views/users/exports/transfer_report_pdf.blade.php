<!DOCTYPE html>
<html>
    <head>

        <meta charset="utf-8">

        <title>Transfer Report</title>

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

        <h2 style="text-align:center">Transfer Report</h2>


        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Category</th>
                    <th>Sub</th>
                    <th>Item</th>
                    <th>Code</th>
                    <th>Qty</th>
                </tr>
            </thead>

            <tbody>
                @foreach($datas as $data)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($data->transfer_on)->format('d M Y H:i') }}</td>
                    <td>
                        @if($current_branch == 0)
                            {{ $data->to == Auth::user()->id ? 'Stock_In' : 'Stock_Out' }}
                        @else
                            {{ $data->to == $current_branch ? 'Stock_In' : 'Stock_Out' }}
                        @endif
                    </td>
                    <td>{{ $data->transfer_from->user_name ?? '' }}</td>
                    <td>{{ $data->transfer_to->user_name ?? '' }}</td>
                    <td>{{ $data->category->name ?? '' }}</td>
                    <td>{{ $data->sub_category->name ?? '' }}</td>
                    <td>{{ $data->product->name ?? '' }}</td>
                    <td>{{ $data->product->code ?? '' }}</td>
                    <td>{{ $data->quantity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>