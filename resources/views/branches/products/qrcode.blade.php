<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{{ config('app.name')}} | QR Code</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		
.qr-container {
			background: gray;
			display: flex ;
			flex-wrap: wrap;
			width: 435px;
		}

		.qr-item {
			margin: 1px;
			width: 132.28346457px;
			height: 83.149606299px;
			overflow: hidden;
			background-color: #f6f6f6;
		}
		.qr-item p{
			width: calc(100% - 10px);
			font-size: 10px;
			padding: 5px;
			font-weight: bold;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.shop-title {
			width: calc(100% - 10px);
			font-size: 10px;
			padding: 5px;
			font-weight: bold;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}
		.qr-body {
			width: 100%;
			padding: 2px;
		}
		.qr-left {
			width: 42px;
			margin-right: 2px;
			float: left;
			text-align: center;
		}
		.qr-left img, .qr-left svg {
			width: 42px;
			height: 42px;
		}
		.qr-right {
			width: calc(100% - 49px);
			display: inline-block;
			padding-left: 5px;
		}

		.qr-right .name {
			width: 100%;
			height: 24px;
			min-height: 24px;
			max-height: 24px;
			overflow: hidden;
			font-size: 10px;
			line-height: 12px;
		}
		.qr-right .price {
			font-size: 14px;
			width: 100%;
			font-weight: bold;
			margin-top: 5px;
		}
		@media print {
			* {
                transition: none !important;
            }

            @page {
                size: 4.13in;
            }
			body {
				background: #fff !important;
				height: 83.149606299px;
			}
			.qr-container {
				gap: 10px;
			}
			.qr-item {
				page-break-inside: avoid;
			}
			
		}
	</style>
</head>

<body class="bg-light">

	<div class="container my-4">
		<div class="qr-container">
			@for ($i = 0; $i < 3; $i++) {{-- Generate 3 labels --}}
				<div class="qr-item">
					<div class="shop-title"> {{ Auth::user()->name }} </div>
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
		window.onload = function() {
			setTimeout(() => {
				window.print();
			}, 500);
		}
	</script>

</body>
</html>
