<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Transfer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #000;
        }
        .container {
            width: 800px;
            margin: auto;
            border: 1px solid #000;
            padding: 15px;
        }
        h2, h3 {
            text-align: center;
            margin: 5px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .box {
            width: 48%;
            border: 1px solid #000;
            padding: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 6px;
            text-align: center;
        }
        .left {
            text-align: left;
        }
        .right {
            text-align: right;
        }
        .footer {
            margin-top: 20px;
        }
        .signature {
            text-align: right;
            margin-top: 40px;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Stock Transfer Details</h2>

    <h3>{{$transfer_detail->From->user_name}}</h3>
    <p style="text-align:center;">
        {{$transfer_detail->From->user_detail->address}},<br>
        Phone: {{$transfer_detail->From->phone}}<br>
        Email: {{$transfer_detail->From->email}}<br>
        GSTIN/UIN: {{ $transfer_detail->From->details->gst ?? '-' }}
    </p>

    <div class="row">
        <div class="box">
            <strong>Buyer</strong><br><br>
            {{$transfer_detail->To->user_name}}<br>
            {{$transfer_detail->To->user_detail->address}},<br>
            Phone: {{$transfer_detail->To->phone}}<br>
            Email: {{$transfer_detail->To->email}}<br>
            GSTIN/UIN: {{ $transfer_detail->To->details->gst ?? '-' }}
        </div>

        <div class="box">
            <table>
                <tr>
                    <td class="left">Initiated On</td>
                    <td>{{ \Carbon\Carbon::parse($transfer_detail->initiated_on)->format('d-M-Y') }}</td>
                </tr>
                <tr>
                    <td class="left">Initiated By</td>
                    <td>{{ $transfer_detail->initiatedBy->user_name  }}</td>
                </tr>
                <tr>
                    <td class="left">Updated On</td>
                    <td>{{ $transfer_detail->updated_on ? \Carbon\Carbon::parse($transfer_detail->updated_on)->format('d M Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="left" >Updated By</td>
                    <td>{{ optional($transfer_detail->updatedBy)->user_name ?? '-' }}</td>
                </tr>
            </table>
        </div>

    </div>

    <table>
        <thead>
            <tr>
                <th>Sl. No</th>
                <th class="left">Description of Goods</th>
                <th>Quantity</th>
                <th>Rate</th>
                <th>Per</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfer_products as $transfer_product)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td class="left">{{$transfer_product->product->name}}</td>
                <td>{{$transfer_product->quantity}}</td>

                @if($transfer_product->price != null)
                    <td>{{number_format($transfer_product->price,2)}}</td>
                @else
                    <td>{{number_format($transfer_product->product->price,2)}}</td>
                @endif

                <td>{{$transfer_product->product->metric->name}}</td>

                 @if($transfer_product->price != null)
                    <td class="right">{{number_format($transfer_product->price * $transfer_product->quantity,2)}}</td>
                @else
                    <td class="right">{{number_format($transfer_product->product->price * $transfer_product->quantity,2)}}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="right">Total</th>
                <th>{{ $transfer_products->sum('quantity') }}</th>
                <th colspan="2"></th>
                <th class="right">
                    @if($transfer_product->price == null)
                    {{number_format($transfer_products->sum(function ($item) 
                        {
                            return $item->product->price * $item->quantity;
                        }), 2)
                    }}
                    @else
                    {{number_format($transfer_products->sum(function ($item) 
                        {
                            return $item->price * $item->quantity;
                        }), 2)
                    }}
                    @endif
                </th>
            </tr>
        </tfoot>
    </table>
</div>
    
</body>
</html>
