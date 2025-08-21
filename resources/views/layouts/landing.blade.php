<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		@yield('title')
		@yield('style')
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="description" content="A fully responsive premium Bootstrap admin dashboard template for modern web applications." />
		<meta name="author" content="FoxPixel" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<!-- <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
		<link rel="stylesheet" href="{{asset('assets/css/landing.css')}}"> -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
<style>
body {
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
background-color: #f8f9fa;
}
.hero {
background: linear-gradient(135deg, #19AD9F, #1B1E2C);
color: #fff;
padding: 100px 20px;
text-align: center;
}
.feature-icon {
font-size: 40px;
color: #19AD9F;
}
.pricing-card {
border-radius: 20px;
transition: transform 0.3s;
}
.pricing-card:hover {
transform: translateY(-10px);
}
</style>
    </head>
	<body>
		@yield('body')
		@yield('modal')
		@yield('script')
		<!-- <script src="{{ asset('assets/js/app.js') }}"></script> -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>