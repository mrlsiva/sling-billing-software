<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	@yield('title')

	@yield('style')
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