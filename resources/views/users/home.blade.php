@extends('layouts.landing')

@section('title')
<title>{{ config('app.name')}} | Home</title>
@endsection

@section('body')

  <div class="main-card">
    <!-- Logo -->
    <img src="{{ asset('storage/' . $user->logo) }}" alt="{{ $user->name }} Logo" class="logo-top">

    <!-- Time -->
    <div class="time-box">
      <div class="time" id="time"></div>
      <div class="date" id="date"></div>
    </div>

    <div class="container text-center my-5">
      <h1>Welcome to</h1>
    <h2 class="display-4 fw-bold typing-loop" id="typingText">
        {{ $user->name }} 
    </h2>

     <p class="lead mt-3">
        @if(!empty($user->phone))
            <i class="bi bi-telephone text-danger me-2"></i>{{ $user->phone }}
        @endif

        @if(!empty($user->email))
            @if(!empty($user->phone)) &nbsp; | &nbsp; @endif
            <i class="bi bi-envelope text-primary me-2"></i>{{ $user->email }}
        @endif

        @if(!empty($user->user_detail->address))
            @if(!empty($user->phone) || !empty($user->email)) &nbsp; | &nbsp; @endif
            <i class="bi bi-geo-alt text-success me-2"></i>{{ $user->user_detail->address }}
        @endif
    </p>

    <a href="{{ route('login', ['company' => request()->route('company')]) }}" 
       class="">
        Login 
        <!-- <i class="bi bi-arrow-right ms-2 arrow-icon"></i> -->
    </a>
</div>
  </div>

  <!-- Footer -->
  <footer>
    <div>Developed by <strong>Sling Groups</strong></div>
    <div><img src="{{ asset('assets/images/sling-dark-logo.png') }}" alt="Sling Logo" height="35" /></div>
  </footer>
@endsection

@section('script')
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.js"></script>
  <script>
    function updateClock() {
      const now = new Date();
      const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      const date = now.toLocaleDateString([], { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });
      document.getElementById("time").textContent = time;
      document.getElementById("date").textContent = date;
    }
    setInterval(updateClock, 1000);
    updateClock();
  </script>
  <script>
// Looping typing effect using JS
document.addEventListener("DOMContentLoaded", function() {
  const textEl = document.getElementById("typingText");
  const fullText = textEl.textContent.trim();
  let i = 0;
  let isDeleting = false;

  function typeEffect() {
    const speed = isDeleting ? 60 : 120;

    textEl.textContent = fullText.substring(0, i) + (i % 2 === 0 ? "" : "");
    textEl.style.borderRight = "3px solid black";

    if (!isDeleting && i < fullText.length) {
      i++;
      setTimeout(typeEffect, speed);
    } else if (isDeleting && i > 0) {
      i--;
      setTimeout(typeEffect, speed);
    } else {
      isDeleting = !isDeleting;
      setTimeout(typeEffect, 800); // small pause before switching
    }
  }

  typeEffect();
});
</script>
@endsection