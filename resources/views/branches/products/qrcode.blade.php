<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name')}} | QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Container for all labels */
        .qr-container {
            display: flex;
            flex-wrap: wrap;
			height:80px;
            width: 435px; /* total container width */
            gap: 0;       /* avoid extra spacing */
            margin: 0;
            padding: 0;
        }

        /* Each label */
        .qr-item {
            width: 132.28px;   /* label width */
            height: 83.15px;   /* label height */
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
            padding: 2px 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: calc(100% - 10px);
        }

        /* QR and Product Info container */
        .qr-body {
            display: flex;
            flex-direction: row;
            width: 100%;
            padding: 2px;
            box-sizing: border-box;
        }

        /* Left: QR code */
        .qr-left {
            width: 42px;
            text-align: center;
            flex-shrink: 0;
        }

        .qr-left img,
        .qr-left svg {
            width: 42px;
            height: 42px;
        }

        /* Right: Product name and price */
        .qr-right {
            flex-grow: 1;
            padding-left: 5px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .qr-right .name {
            font-size: 10px;
            line-height: 12px;
            height: 24px;
            overflow: hidden;
        }

        .qr-right .price {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }

        /* Print specific styles */
        @media print {
            * {
                transition: none !important;
            }

            @page {
                size: 4.13in 0.83in landscape; /* Adjust to your label dimensions */
                margin: 0;
            }

            body,
            html {
                margin: 0;
                padding: 0;
            }

            .qr-container {
                gap: 0; /* remove extra spacing */
            }

            .qr-item {
                page-break-inside: avoid;
                margin: 0;
            }

            /* Tiny offset to fix first row blank */
            .qr-container::before {
                content: "";
                display: block;
                height: 1px;
            }
        }
    </style>
</head>

<body class="bg-light">

    <div class="container my-4">
        <div class="qr-container">
            @for ($i = 0; $i < 3; $i++) {{-- Generate 3 labels --}}
                <div class="qr-item">
                    <div class="shop-title">{{ Auth::user()->name }}</div>
                    <div class="shop-title">{{ $product->code }}</div>
                    <div class="qr-body">
                        <div class="qr-left">
                            {!! QrCode::size(100)->generate($product->id) !!}
                        </div>
                        <div class="qr-right">
                            <div class="name">{{ $product->name }}</div>
                            <div class="price">Rs. {{ number_format($product->price, 2) }}</div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.onload = function () {
            setTimeout(() => {
                window.print();
            }, 1000); // increased delay to ensure rendering
        }
    </script>

</body>

</html>
