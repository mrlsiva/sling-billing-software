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

    <h2>Delivery Challan</h2>

    <h3>{{$transfer_detail->transfer_from->user_name}}</h3>
    <p style="text-align:center;">
        {{$transfer_detail->transfer_from->user_detail->address}},<br>
        Phone: {{$transfer_detail->transfer_from->phone}}<br>
        Email: {{$transfer_detail->transfer_from->email}}<br>
        GSTIN/UIN: {{ $transfer_detail->transfer_from->details->gst ?? '-' }}
    </p>

    <div class="row">
        <div class="box">
            <strong>Buyer</strong><br><br>
            {{$transfer_detail->transfer_to->user_name}}<br>
            {{$transfer_detail->transfer_to->user_detail->address}},<br>
            Phone: {{$transfer_detail->transfer_to->phone}}<br>
            Email: {{$transfer_detail->transfer_to->email}}<br>
            GSTIN/UIN: {{ $transfer_detail->transfer_to->details->gst ?? '-' }}
        </div>

        <div class="box">
            <table>
                <tr>
                    <td class="left">Invoice No</td>
                    <td>{{$transfer_detail->invoice}}</td>
                </tr>
                <tr>
                    <td class="left">Dated</td>
                    <td>{{ \Carbon\Carbon::parse($transfer_detail->transfer_on)->format('d-M-Y') }}</td>
                </tr>
                <tr>
                    <td class="left">Delivery Note</td>
                    <td></td>
                </tr>
                <tr>
                    <td class="left">Mode of Payment</td>
                    <td></td>
                </tr>
                <tr>
                    <td class="left" colspan="2">Terms of Delivery</td>
                </tr>
            </table>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sl. No</th>
                <th class="left">Description of Goods</th>
                <th>HSN/SAC</th>
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
                <td>{{$transfer_product->product->hsn_code ?? '-'}}</td>
                <td>{{$transfer_product->quantity}}</td>
                <td>{{number_format($transfer_product->product->price,2)}}</td>
                <td>{{$transfer_product->product->metric->name}}</td>
                <td class="right">{{number_format($transfer_product->product->price * $transfer_product->quantity,2)}}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="right">Total</th>
                <th>{{ $transfer_products->sum('quantity') }}</th>
                <th colspan="2"></th>
                <th class="right">
                    {{number_format($transfer_products->sum(function ($item) 
                        {
                            return $item->product->price * $item->quantity;
                        }), 2)
                    }}
                </th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>
            <strong>Declaration:</strong><br>
            We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
        </p>

        <p><em>This is a Computer Generated Invoice</em></p>
    </div>

    <div class="signature">
        <p>for <strong>{{$transfer_detail->transfer_from->user_name}}</strong></p>
        <br>
        <p><strong>Authorised Signatory</strong></p>
    </div>

</div>

<script>
    window.onload = function() {
        setTimeout(() => {
            window.print();
            }, 500); // half a second delay
    }
</script>
    
</body>
</html>
