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
    <link rel="shortcut icon" href="assets/images/favicon.ico">
	 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
	<link rel="stylesheet" href="{{asset('assets/css/login.css')}}">
</head>
<body>
	@yield('body')

	@yield('modal')

	@yield('script')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>