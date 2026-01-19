@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Customers</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Customer</p>
					</div>

					@if(Auth::user()->user_detail->is_bill_enabled == 1)
					<div>
						<a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#customerAdd"><i class='bx bxs-folder-plus'></i> Create Customer</a>
						<a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkUpload"><i class='bx bxs-folder-plus'></i> Bulk Upload</a>
					</div>
					@endif
				</div>

				<form method="get" action="{{route('customer.index', ['company' => request()->route('company')])}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Customer Name/ Customer Phone" name="customer" value="{{ request('customer') }}" id="searchInput">
                                <span class="input-group-text" id="clearFilter" style="display: {{ request('customer') ? 'inline-flex' : 'none' }}"><a href="{{route('customer.index', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-primary"> Search </button>
                        </div>
                    </div>
                </form>

				<div class="">

					<div class="d-flex justify-content-end p-3">
						<form method="get" action="{{route('customer.download', ['company' => request()->route('company')])}}">
							<input type="hidden" class="form-control" name="customer" value="{{ request('customer') }}">
							<button class="btn btn-success"> Download </button>
						</form>
					</div>
					
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Name</th>
									<th>Phone</th>
									<th>Alternate Phone</th>
									<th>Address</th>
									<th>Gender</th>
									<th>DOB</th>
									<th>Order History</th>
								</tr>
							</thead>
							<tbody>
									@foreach($users as $user)
									<tr>
										<td>
											{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
										</td>
										<td>{{$user->name}}</td>
										<td>{{$user->phone}}</td>
										<td>
											@if($user->alt_phone != null)
												{{$user->alt_phone}}
											@else
												-
											@endif
										</td>
										<td>
											@if($user->address != null)
												{{$user->address}}
											@else
												-
											@endif
											<br>
											@if($user->pincode != null)
												{{$user->pincode}}
											@endif
										</td>
										<td>
											@if($user->gender_id != null)
												{{$user->gender->name}}
											@else
												-
											@endif
										</td>
										<td>
											@if($user->dob != null)
												{{ \Carbon\Carbon::parse($user->dob)->format('d M Y') }}
											@else
												-
											@endif
										</td>
										<td>
											<div class="d-flex gap-3">
												<a href="{{ route('customer.order', ['company' => request()->route('company'),'id' => $user->id ]) }}" class="text-muted" title="Order History"><i class="ri-eye-line align-middle fs-20"></i></a>
											</div>
										</td>
									</tr>
									@endforeach
							</tbody>
						</table>
						@if($users->isEmpty())
							@include('no-data')
                        @endif
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					{!! $users->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>

	<div class="modal fade" id="customerAdd" tabindex="-1" aria-labelledby="customerAdd" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="row" action="{{route('customer.store', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                   	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Name</label>
		                            <span class="text-danger">*</span>
		                            <input type="text" id="name" name="name" class="form-control" required="">
		                        </div>
		                    </div>
	                   	</div>

	                   	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Phone</label>
		                            <span class="text-danger">*</span>
		                            <input type="tel" name="phone" id="phone" class="form-control" maxlength="10" pattern="[0-9]{10}" inputmode="numeric">
		                        </div>
		                    </div>
	                   	</div>

	                   	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Alternate Phone</label>
		                            <input type="tel" id="alt_phone" name="alt_phone" class="form-control" maxlength="10" pattern="[0-9]{10}" inputmode="numeric">
		                        </div>
		                    </div>
	                   	</div>

	                   	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Address</label>
		                            <span class="text-danger">*</span>
		                            <input type="text" id="address" name="address" class="form-control" required="">
		                        </div>
		                    </div>
	                   	</div>

		                <div class="row">
		                	<div class="col-md-12">
		                		<div class="mb-3">
		                			<label for="choices-single-groups" class="form-label text-muted">Pincode</label>
		                			<input type="number" id="pincode" name="pincode" class="form-control" min="1">
		                		</div>
		                	</div>
		                </div>

		                <div class="row">
		                	<div class="col-md-12">
		                		<div class="mb-3">
		                			 <label for="payment_method" class="form-label">Gender</label>
                                    <select class="form-control" data-choices name="gender" id="gender">
                                        <option value="">Select</option>
                                        @foreach($genders as $gender)
                                        	<option value="{{$gender->id}}">{{$gender->name}}</option>
                                        @endforeach
                                    </select>
		                		</div>
		                	</div>
		                </div>

		                <div class="row">
		                	<div class="col-md-12">
		                		<div class="mb-3">
		                			<label for="choices-single-groups" class="form-label text-muted">DOB</label>
		                			<input type="date" id="dob" name="dob" class="form-control" max="{{ date('Y-m-d') }}">
		                		</div>
		                	</div>
		                </div>

		                <div class="row">
		                	<div class="col-md-12">
		                		<div class="mb-3">
		                			<label for="gst" class="form-label text-muted">GST</label>
		                			<input type="text" id="gst" name="gst" class="form-control" >
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

    <div class="modal fade" id="bulkUpload" tabindex="-1" aria-labelledby="bulkUpload" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Bulk Upload</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="row" action="{{route('branch.customer.bulk_upload', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                	<div class="row">
		                    <div class="col-md-12 d-flex justify-content-end">
		                    	<a href="{{ asset('assets/templates/customer.xlsx') }}" download="Customer_Template.xlsx">Download Template</a>
		                    </div>
		                </div>

	                	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="name" class="form-label">Upload File</label>
	                                <div class="input-group">
	                                    <input type="file" name="file" id="file" class="form-control" accept=".xlsx">
	                                </div>
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

@section('script')
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        let searchInput = document.getElementById("searchInput");
        let clearFilter = document.getElementById("clearFilter");

        function toggleClear() {
            if (searchInput.value.trim() !== "") {
                clearFilter.style.display = "inline-flex";
            } else {
                clearFilter.style.display = "none";
            }
        }

        // Run on load (for prefilled request values)
        toggleClear();

        // Run on typing
        searchInput.addEventListener("input", toggleClear);
    });
</script>
@endsection