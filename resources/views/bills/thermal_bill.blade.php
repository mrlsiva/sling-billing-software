<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Invoice</title>
    <style>
        body {
            font-family: monospace;
            width: calc(100% - 10mm);
            /* Typical thermal printer width */
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

        .items td {
            padding: 2px 0;
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
    </style>
</head>

<body>
    <div class="center bold mb-10 print-logo">
        <img src="{{ asset('storage/' . $user->logo) }}" />
    </div>
    <div class="center bold mb-10">
        {{$user->name}}<br>
        {{$user->user_detail->address}}<br>
        Phone: {{$user->phone}}
    </div>

    <hr>

    <table width="100%">
        <tr>
            <td>Invoice #: {{$order->bill_id}}</td>
            <td class="right">Date: {{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y') }}</td>
        </tr>
    </table>

    <hr>

    <table class="items" width="100%">
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Size-Colour</th>
                <th class="right">IMEI</th>
                <th class="right">Price</th>
                <th class="right">Tax</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
        	@foreach($order_details as $order_detail)
            <tr>
                <td>{{$order_detail->name}}</td>
                <td class="right">{{$order_detail->quantity}}</td>
                <td class="right">
                    @php
                        $sizeName   = $order_detail->size_id   ? $order_detail->size->name   : null;
                        $colorName  = $order_detail->colour_id ? $order_detail->colour->name : null;
                    @endphp

                    {{ $sizeName && $colorName ? "$sizeName - $colorName" : ($sizeName ?: ($colorName ?: '-')) }}
                </td>
                <td class="right">
                    @if($order_detail->imei != null)
                        {{$order_detail->imei}}
                    @else
                        -
                    @endif
                </td>
                <td class="right">₹ {{ $order_detail->price - $order_detail->tax_amount }}</td>
                <td class="right">₹ {{$order_detail->tax_amount}}</td>
                <td class="right">₹ {{$order_detail->price * $order_detail->quantity}}</td>
            </tr>
            @endforeach
            @if($order->is_refunded == 1)

                @php
                    $refundIds = App\Models\Refund::where('order_id',$order->id)->pluck('payment_id')->toArray();
                    $refund_details = App\Models\RefundDetail::whereIn('refund_id',$refundIds)->get();
                @endphp

                @foreach($refund_details as $refund_detail)
                    <tr>
                        <td>{{$refund_detail->name}} (Refunded - {{ \Carbon\Carbon::parse($refund_detail->created_at)->format('d M Y') }})</td>
                        <td class="right">{{$refund_detail->quantity}}</td>
                        <td class="right">₹ {{ $refund_detail->price - $refund_detail->tax_amount }}</td>
                        <td class="right">₹ {{$refund_detail->tax_amount}}</td>
                        <td class="right">₹ {{$refund_detail->price * $refund_detail->quantity}}</td>
                    </tr>
                @endforeach

            @endif
        </tbody>
    </table>

    <hr>

    <table width="100%">
    	@if($order->is_refunded == 1)
          	@php
                $refundAmount = App\Models\Refund::where('order_id', $order->id)->sum('refund_amount');
                $refundIds = App\Models\Refund::where('order_id',$order->id)->pluck('payment_id')->toArray();
                $refund_details = App\Models\RefundDetail::whereIn('refund_id',$refundIds)->get();
            @endphp
            <tr>
	            <td class="bold">Total Tax</td>
	            <td class="right">₹ {{ number_format($order_details->sum(fn($d) => (float)$d->tax_amount * (int)$d->quantity),2)}}</td>
	        </tr>
	        <tr>
	            <td class="bold">Refunded Tax</td>
	            <td class="right">₹ {{ number_format($refund_details->sum(fn($d) => (float)$d->tax_amount * (int)$d->quantity),2) }}</td>
	        </tr>
	        <tr>
	            <td class="bold">ORDER AMOUNT</td>
	            <td class="right">₹ {{number_format($order->bill_amount,2)}}</td>
	        </tr>
	        <tr>
	            <td class="bold">REFUNDED AMOUNT</td>
	            <td class="right">₹ {{number_format($refundAmount,2)}}</td>
	        </tr>
	        <tr>
	            <td class="bold">TOTAL</td>
	            <td class="right">₹ {{ number_format($order->bill_amount - $refundAmount, 2)}}</td>
	        </tr>
        @else
	        <tr>
	            <td class="bold">Total Tax</td>
	            <td class="right">₹ {{ number_format($order_details->sum(fn($d) => (float)$d->tax_amount * (int)$d->quantity),2)}}</td>
	        </tr>
	        <tr>
	            <td class="bold">Total</td>
	            <td class="right">₹ {{number_format($order->bill_amount,2)}}</td>
	        </tr>
	    @endif
    </table>

    <hr>

    <div class="center">
        Thank you for your purchase!<br>
        Visit Again!
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