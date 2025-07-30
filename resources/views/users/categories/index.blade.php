@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Shop Create</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
							<p class="card-title">All Category</p>
					</div>
					<a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#exampleModalCenteredScrollable"><i class='bx bxs-folder-plus'></i> Create Category</a>
				</div>
				<div class="">
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>Category ID</th>
									<th>Category Name</th>
									<th>No.Of sub Category</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
									<tr>
										<td>1</td>
										<td>-</td>
										<td>10</td>
										<td>
											<span class="badge bg-soft-success text-success">Active</span>
										</td>
										<td>
											<div class="d-flex gap-3">
												<a href="" class="text-muted"><i class="ri-eye-line align-middle fs-20"></i></a>
												<a href="" class="link-dark"><i class="ri-edit-line align-middle fs-20"></i></a>
												<a href="" class="link-danger"><i class="ri-delete-bin-5-line align-middle fs-20"></i></a>
											</div>
										</td>
									</tr>
							</tbody>
						</table>
					</div>
					<!-- end table-responsive -->
				</div>
				
			</div>
		</div>
	</div>
    <div class="modal fade" id="exampleModalCenteredScrollable" tabindex="-1" aria-labelledby="exampleModalCenteredScrollableTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                   <div class="row">
                    <div class="col-md-12">
                        
                        <div class="mb-3">
                            <label for="choices-single-groups" class="form-label text-muted">Category Name</label>
                            <input type="text" id="simpleinput" class="form-control">
                        </div>
                    </div>
                    
                   </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection