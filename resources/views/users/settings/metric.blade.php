@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Metric</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Metric</p>
					</div>
					<a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#metricAdd"><i class='bx bxs-folder-plus'></i> Create Metric</a>
				</div>


				@if ($errors->any())
		            <div class="alert alert-danger">
		                <strong>Whoops!</strong> There were some problems with your input.<br><br>
		                <ul>
		                    @foreach ($errors->all() as $error)
		                        <li>{{ $error }}</li>
		                    @endforeach
		                </ul>
		            </div>
		        @endif

				<form method="get" action="{{route('setting.metric.index', ['company' => request()->route('company')])}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Name" name="metric" value="{{ request('metric') }}" id="searchInput">
                                <span class="input-group-text" id="clearFilter" style="display: {{ request('metric') ? 'inline-flex' : 'none' }}"><a href="{{route('setting.metric.index', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-primary"> Search </button>
                        </div>
                    </div>
                </form>

				<div class="">
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Name</th>
									<th>Active / In-Active</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
									@foreach($metrics as $metric)
									<tr>
										<td>
											{{ ($metrics->currentPage() - 1) * $metrics->perPage() + $loop->iteration }}
										</td>
										<td>{{$metric->name}}</td>
										<td>
										    <form action="{{ route('setting.metric.status', ['company' => request()->route('company')]) }}" method="post">
										        @csrf
										        <input type="hidden" name="id" value="{{ $metric->id }}">
										        <div class="form-check form-switch">
										            <input class="form-check-input" type="checkbox" name="is_active" onchange="if(confirm('Are you sure you want to change the metric status?')) { this.form.submit(); } else { this.checked = !this.checked; }"
										                {{ $metric->is_active == 1 ? 'checked' : '' }}>
										        </div>
										    </form>
										</td>

										<td>
											@if($metric->is_active == 1)
												<span class="badge bg-soft-success text-success">Active</span>
											@else
												<span class="badge bg-soft-danger text-danger">In-Active</span>
											@endif
										</td>
										<td>
											<div class="d-flex gap-3">
												<a href="#!" onclick="metric_edit(this)" class="link-dark" data-system_id="{{$metric->id}}"><i class="ri-edit-line align-middle fs-20"></i></a>
											</div>
										</td>
									</tr>
									@endforeach
							</tbody>
						</table>
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					{!! $metrics->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>



	<div class="modal fade" id="metricAdd" tabindex="-1" aria-labelledby="metricAdd" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Add New</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="row" action="{{route('setting.metric.store', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                   	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Metric</label>
		                            <input type="text" id="name" name="name" class="form-control" required="">
		                        </div>
		                    </div>
	                   	</div>
	                </div>
	                <div class="modal-footer">
	                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
	                    <button type="submit" class="btn btn-primary">Submit</button>
	                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="metricEdit" tabindex="-1" aria-labelledby="metricEdit" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Edit Metric</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="row" action="{{route('setting.metric.update', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                   <div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Metric</label>
		                            <input type="text" id="metric" name="metric" class="form-control" required="">
		                            <input type="hidden" name="metric_id" id="metric_id">
		                        </div>
		                    </div>
	                   </div>
	                   
	                </div>
	                <div class="modal-footer">
	                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
	                    <button type="submit" class="btn btn-primary">Save changes</button>
	                </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script src="{{asset('assets/js/users/metric.js')}}"></script>
@endsection