@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Staffs</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Staff</p>
					</div>
					<a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#staffAdd"><i class='bx bxs-folder-plus'></i> Add Staff</a>

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

				<form method="get" action="{{route('branch.staff.index', ['company' => request()->route('company')])}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Name" name="name" value="{{ request('name') }}">
                                <span class="input-group-text"><a href="{{route('branch.staff.index', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
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
									<th>Active / In-Active</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								@foreach($staffs as $staff)
									<tr>
										<td>
											{{ ($staffs->currentPage() - 1) * $staffs->perPage() + $loop->iteration }}
										</td>
										<td>{{$staff->name}}</td>
										<td>{{$staff->phone}}</td>
										<td>
										    <form action="{{ route('branch.staff.status', ['company' => request()->route('company')]) }}" method="post">
										        @csrf
										        <input type="hidden" name="id" value="{{ $staff->id }}">
										        <div class="form-check form-switch">
										            <input class="form-check-input" type="checkbox" name="is_active"
										                onchange="if(confirm('Are you sure you want to change the staff status?')) { this.form.submit(); } else { this.checked = !this.checked; }"
										                {{ $staff->is_active == 1 ? 'checked' : '' }}>
										        </div>
										    </form>
										</td>

										<td>
											@if($staff->is_active == 1)
												<span class="badge bg-soft-success text-success">Active</span>
											@else
												<span class="badge bg-soft-danger text-danger">In-Active</span>
											@endif
										</td>
										<td>
											<div class="d-flex gap-3">
												<a href="#!" data-bs-toggle="modal" data-bs-target="#staffEdit" class="link-dark" data-id="{{$staff->id}}" data-name="{{$staff->name}}" data-phone="{{$staff->phone}}" data-role="{{$staff->role}}"><i class="ri-edit-line align-middle fs-20"></i></a>
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
					{!! $staffs->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>

	<div class="modal fade" id="staffAdd" tabindex="-1" aria-labelledby="staffAdd" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Add New Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="row" action="{{route('branch.staff.store', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                   	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Name</label>
		                            <input type="text" id="name" name="name" class="form-control" required="" placeholder="Enter Name">
		                        </div>
		                    </div>
	                   	</div>

	                   	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Phone</label>
		                            <input type="number" id="phone" name="phone" class="form-control" placeholder="Enter Phone">
		                        </div>
		                    </div>
	                   	</div>

	                   	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Role</label>
		                            <input type="text" id="role" name="role" class="form-control" placeholder="Enter Role">
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

    <div class="modal fade" id="staffEdit" tabindex="-1" aria-labelledby="staffEdit" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Edit Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="row" action="{{route('branch.staff.update', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                	<input type="hidden" name="staff_id" id="staff_id">

	                   	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Name</label>
		                            <input type="text" id="staff_name" name="staff_name" class="form-control" required="" placeholder="Enter Name">
		                        </div>
		                    </div>
	                   	</div>

	                   	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Phone</label>
		                            <input type="number" id="staff_phone" name="staff_phone" class="form-control" placeholder="Enter Phone">
		                        </div>
		                    </div>
	                   	</div>

	                   	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Role</label>
		                            <input type="text" id="staff_role" name="staff_role" class="form-control" placeholder="Enter Role">
		                        </div>
		                    </div>
	                   	</div>

	                </div>
	                <div class="modal-footer">
	                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
	                    <button type="submit" class="btn btn-primary">Save Changes</button>
	                </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var staffEditModal = document.getElementById('staffEdit');

    staffEditModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button/link that triggered the modal

        // Extract info from data-* attributes
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');
        var phone = button.getAttribute('data-phone');
        var role = button.getAttribute('data-role');

        // Update the modal's form fields
        staffEditModal.querySelector('#staff_name').value = name;
        staffEditModal.querySelector('#staff_phone').value = phone;
        staffEditModal.querySelector('#staff_role').value = role;
        staffEditModal.querySelector('#staff_id').value = id;

    });
});
</script>
@endsection