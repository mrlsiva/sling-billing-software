@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Settings</title>
@endsection

@section('body')

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<p class="card-title">Settings - {{Auth::user()->user_name}}</p>
				</div>
			</div>
			<div class="card-body">
				<form method="get" action="{{route('branch.setting.store', ['company' => request()->route('company')])}}">
					
				</form>
			</div>
		</div>
	</div>
</div>
@endsection