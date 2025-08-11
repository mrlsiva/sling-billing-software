@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Dashboard</title>
@endsection

@section('body')

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<p class="card-title">Welcome {{Auth::user()->user_name}} - Branch</p>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection