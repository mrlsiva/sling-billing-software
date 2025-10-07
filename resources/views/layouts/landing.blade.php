<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		@yield('title')
		@yield('style')

		@php
        	$user = App\Models\User::where('slug_name',request()->segment(1))->first();
    	@endphp

		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="description" content="A fully responsive premium Bootstrap admin dashboard template for modern web applications." />
		<meta name="author" content="FoxPixel" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta charset="utf-8">
		<!-- Bootstrap 5 CSS -->
		<link rel="icon" type="image/png" href="{{ asset('storage/' . $user->fav_icon) }}">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
		<!-- Bootstrap Icons -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
		<link rel="stylesheet" href="{{asset('assets/css/landing.css')}}">
		<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">

    </head>
	<body>
		@yield('body')
		@yield('modal')
		@yield('script')
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
		<script>
		// Set footer year
		document.getElementById('year').textContent = new Date().getFullYear();


		// Countdown target (edit this date as needed)
		const targetDate = new Date('2025-11-01T00:00:00');


		function updateCountdown(){
		const now = new Date();
		const diff = Math.max(0, targetDate - now);
		const secs = Math.floor(diff / 1000) % 60;
		const mins = Math.floor(diff / (1000 * 60)) % 60;
		const hrs = Math.floor(diff / (1000 * 60 * 60)) % 24;
		const days = Math.floor(diff / (1000 * 60 * 60 * 24));
		document.getElementById('days').textContent = String(days).padStart(2,'0');
		document.getElementById('hours').textContent = String(hrs).padStart(2,'0');
		document.getElementById('minutes').textContent = String(mins).padStart(2,'0');
		document.getElementById('seconds').textContent = String(secs).padStart(2,'0');
		}
		updateCountdown();
		setInterval(updateCountdown, 1000);


		// Simple subscribe form behaviour (replace with API integration)
		function subscribe(e){
		e.preventDefault();
		const email = document.getElementById('emailInput').value.trim();
		if(!email) return;
		// TODO: send `email` to your backend here (fetch / AJAX)
		document.getElementById('msg').style.display = 'block';
		document.getElementById('subscribeForm').reset();
		}
		</script>
		<!-- <script src="{{ asset('assets/js/app.js') }}"></script> -->
    </body>
</html>