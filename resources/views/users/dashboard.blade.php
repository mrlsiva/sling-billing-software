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
					<p class="card-title">Welcome {{Auth::user()->user_name}} - HO</p>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12 col-md-12">
		<div class="row">
			<div class="col-md-3 col-md-3">
				<div class="card">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-3 card-title">No.Of Branches </p>
								@foreach($branches as $branch)
								<h4 class="fw-bold d-flex align-items-center gap-2 mb-0"> {{ count($branches) }}</h4>
								@endforeach
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-md-3">
				<div class="card">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-3 card-title">No.Of Categories </p>

								<h4 class="fw-bold d-flex align-items-center gap-2 mb-0"> 2</h4>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-md-3">
				<div class="card">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-3 card-title">No. Of Sub Categories</p>
								<h4 class="fw-bold d-flex align-items-center gap-2 mb-0">
									5
								</h4>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-md-3">
				<div class="card">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-3 card-title">No.Of Products </p>
								<h4 class="fw-bold d-flex align-items-center gap-2 mb-0"> 23</h4>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
	<div class="col-xl-12 col-md-12">
		<div class="row">
			<div class="col-md-12 col-md-12 mb-3">
				Branch List
			</div>
			@foreach($branches as $branch)

			<div class="col-md-3 col-md-3">
				<div class="card">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<!-- {{$branch->name}} -  -->
								<p class="mb-3 card-title">{{$branch->user_name}}</p>
								<h4 class="fw-bold d-flex align-items-center gap-2 mb-0">0 Orders</h4>
							</div>
							<div>
								<a href="#!"> <i class="ri-arrow-right-circle-line fs-32 text-muted"></i></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			@endforeach
		</div>
	</div>
</div>

@endsection