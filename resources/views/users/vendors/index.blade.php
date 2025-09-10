@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Vendors</title>
@endsection

@section('body')

	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Vendor</p>
					</div>
					<a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#vendorAdd"><i class='bx bxs-folder-plus'></i> Add Vendor</a>
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

				<form method="get" action="{{route('vendor.index', ['company' => request()->route('company')])}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Name/ Phone" name="vendor" value="{{ request('vendor') }}">
                                <span class="input-group-text"><a href="{{route('vendor.index', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
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
									<th>Phone</th>
									<th>Email</th>
									<th>Address</th>
									<th>City/State</th>
									<th>GST</th>
									<th>Active / In-Active</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
									@foreach($vendors as $vendor)
									<tr>
										<td>
											{{ ($vendors->currentPage() - 1) * $vendors->perPage() + $loop->iteration }}
										</td>
										<td>{{$vendor->name}}</td>
										<td>{{$vendor->phone}}</td>
										<td>
											@if($vendor->email != null)
												{{$vendor->email}}
											@else
												-
											@endif
										</td>
										<td>
											@if($vendor->address != null)
												{{$vendor->address}}
											@else
												-
											@endif
										</td>
										<td>
											@if($vendor->city != null)
												{{$vendor->city}}
											@else
												-
											@endif
											@if($vendor->state != null)
												{{$vendor->state}}
											@else
												-
											@endif
										</td>
										<td>
											@if($vendor->gst != null)
												{{$vendor->gst}}
											@else
												-
											@endif
										</td>
										<td>
										    <form action="{{ route('vendor.status', ['company' => request()->route('company')]) }}" method="post">
										        @csrf
										        <input type="hidden" name="id" value="{{ $vendor->id }}">
										        <div class="form-check form-switch">
										            <input class="form-check-input" type="checkbox" name="is_active"
										                onchange="if(confirm('Are you sure you want to change the vendor status?')) { this.form.submit(); } else { this.checked = !this.checked; }"
										                {{ $vendor->is_active == 1 ? 'checked' : '' }}>
										        </div>
										    </form>
										</td>
										<td>
											@if($vendor->is_active == 1)
												<span class="badge bg-soft-success text-success">Active</span>
											@else
												<span class="badge bg-soft-danger text-danger">In-Active</span>
											@endif
										</td>
										<td>
											<div class="d-flex gap-3">
												<a href="#!" data-bs-toggle="modal" data-bs-target="#vendorEdit" class="link-dark" data-id="{{$vendor->id}}" data-name="{{$vendor->name}}" data-phone="{{$vendor->phone}}" data-email="{{$vendor->email}}" data-address="{{$vendor->address}}" data-address1="{{$vendor->address1}}" data-city="{{$vendor->city}}" data-state="{{$vendor->state}}" data-gst="{{$vendor->gst}}"><i class="ri-edit-line align-middle fs-20" title="Edit"></i></a>

												<a href=""  class="link-dark"><i class="ri-history-line align-middle fs-20" title="History"></i></a>

												<a href="{{ route('vendor.ledger.index', ['company' => request()->route('company'),'id' => $vendor->id ]) }}"  class="link-dark"><i class="ri-file-list-3-line align-middle fs-20" title="Ledger"></i></a>
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
					{!! $vendors->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>

	<div class="modal fade" id="vendorAdd" tabindex="-1" aria-labelledby="vendorAdd" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Add New Vendor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="row" action="{{route('vendor.store', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                   	<div class="row">
		                    <div class="col-md-6">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Name</label>
		                            <input type="text" id="name" name="name" class="form-control" required="" placeholder="Enter Name">
		                        </div>
		                    </div>
		                    <div class="col-md-6">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Phone</label>
		                            <input type="number" id="phone" name="phone" class="form-control" required="" placeholder="Enter Phone">
		                        </div>
		                    </div>
	                   	</div>

	                   	<div class="row">
		                    <div class="col-md-6">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Email</label>
		                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter Email">
		                        </div>
		                    </div>
		                    <div class="col-md-6">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">GST</label>
		                            <input type="text" id="gst" name="gst" class="form-control" placeholder="Enter GST number">
		                        </div>
		                    </div>
	                   	</div>

	                   	<div class="row">
		                    <div class="col-md-6">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Address</label>
		                            <input type="text" id="address" name="address" class="form-control" placeholder="Enter Address">
		                        </div>
		                    </div>
		                    <div class="col-md-6">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Address1</label>
		                            <input type="text" id="address1" name="address1" class="form-control" placeholder="Enter Alternate Address">
		                        </div>
		                    </div>
	                   	</div>

	                   	<div class="row">
		                    <div class="col-md-6">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">City</label>
		                            <input type="text" id="city" name="city" class="form-control" placeholder="Enter City">
		                        </div>
		                    </div>
		                    <div class="col-md-6">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">State</label>
		                            <input type="text" id="state" name="state" class="form-control" placeholder="Enter State">
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
@endsection