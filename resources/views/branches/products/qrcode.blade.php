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
    flex-wrap: wrap;
    width: 435px; /* total width for 3 labels per row */
    background: gray;
    gap: 0;
    margin: 0;
    padding: 0;
}

/* Each label */
.label {
    width: 132px;      /* physical label width */
    height: 83px;      /* label height matching TE244 60x40mm */
    background-color: #f6f6f6;
    margin: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
}

/* Shop Name & Product Code */
.shop-title {
    font-size: 10px;
    font-weight: bold;
    padding: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
    width: 35px;        /* slightly smaller to free more space for text */
    text-align: center;
    flex-shrink: 0;
}

.qr img, .qr svg {
    width: 35px;
    height: 35px;
}

/* Product info on right */
.product {
    flex-grow: 2;       /* take more horizontal space */
    padding-left: 2px;  /* reduce left padding */
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
    font-size: 14px;
    font-weight: bold;
    margin-top: 5px;
}

/* Print-specific styles */
@media print {
    * { transition: none !important; }

    @page {
        size: 4.13in 1.63in landscape; /* TE244 size */
        margin: 0;
    }

    body, html { margin: 0; padding: 0; }

    .container { gap: 0; }

    .label { page-break-inside: avoid; margin: 0; }

    /* Tiny offset to fix first-row blank */
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
