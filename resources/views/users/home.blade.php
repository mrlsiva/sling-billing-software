<h2>{{request()->route('company')}}</h2>
<br>
<a href="{{ route('login', ['company' => request()->route('company')]) }}" >
<button type="button" class="btn btn-primary w-100"><i class="ri-save-line"></i> Login</button></a>