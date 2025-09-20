{{$user}}

<br><br>

{{$user->name}}

<br><br>

{{$user->user_detail->address}}

<br><br>

{{$user->bank_detail->name}}

<br><br>

<img src="{{ asset('storage/' . $user->logo) }}" alt="user-image" class="img-fluid user-avtar">