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
		<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
		<link rel="stylesheet" href="{{asset('assets/css/landing.css')}}">
    </head>
	<body>
		@yield('body')
		@yield('modal')
		@yield('script')
		<script src="{{ asset('assets/js/app.js') }}"></script>
    </body>
</html>