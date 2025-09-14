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
					<div class="row">
						<div class="col-4">
							<h5>Bill Type:</h5>

							@php
							$user_detail = App\Models\UserDetail::where('user_id',Auth::user()->id)->first();
							@endphp
							<div class="form-check">
								<input class="form-check-input" type="radio" name="bill_type" id="bill_type" value="1" {{ $user_detail && $user_detail->bill_type == 1 ? 'checked' : '' }}>
								<label class="form-check-label" for="bill_type">
									Regular Print
								</label>
							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="bill_type" id="bill_type" value="2" {{ $user_detail && $user_detail->bill_type == 2 ? 'checked' : '' }}>
								<label class="form-check-label" for="bill_type">
									Thermal Print
								</label>
							</div>
						</div>
					</div>
					<div class="d-flex justify-content-center">
						<button type="submit" class="btn btn-primary">Submit</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection