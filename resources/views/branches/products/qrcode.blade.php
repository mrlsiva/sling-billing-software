<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{{ config('app.name')}} | QR Code</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		.qr-container {
			display: flex;
			flex-wrap: wrap;
			gap: 20px;
			page-break-inside: avoid;
		}

		.qr-item {
			width: 260px;
			border: 1px solid #ccc;
			padding: 10px;
			text-align: center;
			page-break-inside: avoid;
			background: #fff;
		}

		.qr-body {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-top: 10px;
			text-align: left;
		}

		.qr-left {
			flex: 0 0 100px;
		}

		.qr-right {
			flex: 1;
			padding-left: 10px;
		}

		.qr-right p {
			margin: 2px 0;
			font-size: 14px;
		}

		@media print {
			body {
				background: #fff !important;
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
					<p><strong>{{ Auth::user()->name }}</strong></p>
					<p>{{ $product->code }}</p>

					<div class="qr-body">
						<div class="qr-left">
							{!! QrCode::size(100)->generate($product->code) !!}
						</div>
						<div class="qr-right">
							<p>{{ $product->name }}</p>
							<p>Rs. {{ number_format($product->price, 2) }}</p>
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
