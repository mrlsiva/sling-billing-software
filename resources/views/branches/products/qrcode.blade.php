<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ config('app.name') }} | QR Code</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
	/* Container for all labels */
.container {
    display: flex;
    flex-wrap: wrap;      /* allow multiple rows */
    width: 435px;         /* enough for 3 labels of 145px */
    gap: 0;               /* no spacing merging them */
    margin: 0;
    padding: 0;
    background: gray;
}

/* Each label */
.label {
    width: 145px;         /* fixed label width */
    min-width: 145px;     /* prevent shrinking */
    max-width: 145px;     /* prevent growing */
    height: 83px;         /* fixed label height */
    background-color: #f6f6f6;
    margin: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    flex: 0 0 auto;       /* prevents flex-grow/shrink */
	padding: 4px 8px;
}

/* Shop Name & Product Code */
.shop-title {
    font-size: 10px;
    font-weight: bold;
    padding: 0px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
	text-align: center;
	min-height: 12px;
}

/* QR + Product container */
.shop {
    display: flex;
    width: 100%;
    padding: 2px;
    box-sizing: border-box;
}

/* QR code on left */
.qr {
    width: 35px;          /* slightly smaller to free space */
    text-align: center;
    flex-shrink: 0;
}

.qr img, .qr svg {
    width: 35px;
    height: 35px;
}

/* Product info on right */
.product {
    flex-grow: 1;         /* use available space inside label */
    padding-left: 8px;    /* reduce padding */
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.product-name {
    font-size: 10px;
    line-height: 12px;
    height: 24px;
    overflow: hidden;
}

.product-price {
    font-size: 12px;
    font-weight: bold;
    margin-top: 0px;
	 white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
	/* text-align: center; */
}

/* Print-specific styles */
@media print {
    * { transition: none !important; }

    @page {
        size: 4.13in 1.63in landscape; /* TE244 label size */
        margin: 6px 0 0 0;
    }

    body, html { margin: 0; padding: 0; }

    .container { gap: 0; }

    .label { 
        page-break-inside: avoid; 
        margin: 0; 
        flex: 0 0 auto;    /* prevent merging on print */
    }

    /* Tiny offset to fix first-row blank printing */
    .container::before { content: ""; display: block; height: 1px; }
}

</style>

</head>
<body>
<div class="container">
    @for ($i = 0; $i < 3; $i++)
        <div class="label">
            <div class="shop-title">{{ Auth::user()->name }}</div>
            <div class="shop-title">{{ $product->code }}</div>
            <div class="shop">
                <div class="qr">
                    {!! QrCode::size(42)->generate($product->id) !!}
                    @if($product->discount_type == 1)
                    <td class="right">{{$product->discount}}</td>
                @elseif($product->discount_type == 2)
                    <td class="right">{{$product->discount}}%</td>
                @else
                    <td class="right"> </td>
                @endif
                </div>
                <div class="product">
                    <div class="product-name">{{ $product->name }}</div>
                    <div class="product-price">Rs. {{ number_format($product->price,2) }}</div>
                </div>
            </div>
        </div>
    @endfor
</div>

<script>
window.onload = function() {
    setTimeout(() => { window.print(); }, 500);
};
</script>
</body>
</html>
