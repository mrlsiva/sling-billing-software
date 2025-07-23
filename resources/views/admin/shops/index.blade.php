@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Shop</title>
@endsection

@section('body')
	
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
							<p class="card-title">All Shops</p>
					</div>
					<a href="{{route('admin.shop.create')}}" class="btn btn-outline-primary btn-sm fw-semibold"><i class='bx bxs-folder-plus'></i> Create Shop</a>
				</div>
				<div class="">
					<div class="table-responsive">
							<table class="table align-middle mb-0 table-hover table-centered">
								<thead class="bg-light-subtle">
									<tr>
										<th>Shop Name</th>
										<th>Mobile Number</th>
										<th>Address</th>
										<th>No.Of Branches</th>
										<th>Status</th>
										<th>Next Payment</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Vasantham</td>
										<td>9994090424</td>
										<td>Madurai</td>
										<td>4</td>
										<td><span class="badge badge-soft-warning">Overdue</span></td>
										<td>15-02-2024</td>
										<td>
												<div class="d-flex gap-3">
													<a href="#!" class="text-muted"><i class="ri-eye-line align-middle fs-20"></i></a>
													<a href="#!" class="link-dark"><i class="ri-edit-line align-middle fs-20"></i></a>
													<a href="#!" class="link-danger"><i class="ri-delete-bin-5-line align-middle fs-20"></i></a>
												</div>
										</td>
									</tr>
									<tr>
										<td>Saravana Store</td>
										<td>9994090424</td>
										<td>Madurai</td>
										<td>1</td>
										<td><span class="badge badge-soft-warning">Overdue</span></td>
										<td>15-02-2024</td>
										<td>
												<div class="d-flex gap-3">
													<a href="#!" class="text-muted"><i class="ri-eye-line align-middle fs-20"></i></a>
													<a href="#!" class="link-dark"><i class="ri-edit-line align-middle fs-20"></i></a>
													<a href="#!" class="link-danger"><i class="ri-delete-bin-5-line align-middle fs-20"></i></a>
												</div>
										</td>
									</tr>
									<tr>
										<td>JN Textiles</td>
										<td>9994090424</td>
										<td>Chennai</td>
										<td>4</td>
										<td><span class="badge badge-soft-success">Active</span></td>
										<td>15-02-2024</td>
										<td>
												<div class="d-flex gap-3">
													<a href="#!" class="text-muted"><i class="ri-eye-line align-middle fs-20"></i></a>
													<a href="#!" class="link-dark"><i class="ri-edit-line align-middle fs-20"></i></a>
													<a href="#!" class="link-danger"><i class="ri-delete-bin-5-line align-middle fs-20"></i></a>
												</div>
										</td>
									</tr>
									<tr>
										<td>Sathya</td>
										<td>9994090424</td>
										<td>Tuticorin</td>
										<td>3</td>
										<td><span class="badge badge-soft-danger">Inactive</span></td>
										<td>15-02-2026</td>
										<td>
												<div class="d-flex gap-3">
													<a href="#!" class="text-muted"><i class="ri-eye-line align-middle fs-20"></i></a>
													<a href="#!" class="link-dark"><i class="ri-edit-line align-middle fs-20"></i></a>
													<a href="#!" class="link-danger"><i class="ri-delete-bin-5-line align-middle fs-20"></i></a>
												</div>
										</td>
									</tr>
								</tbody>
							</table>
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					<nav aria-label="Page navigation example">
							<ul class="pagination justify-content-end mb-0">
								<li class="page-item"><a class="page-link" href="javascript:void(0);"><i class="ri-arrow-left-s-line"></i></a></li>
								<li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
								<li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
								<li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
								<li class="page-item"><a class="page-link" href="javascript:void(0);"><i class="ri-arrow-right-s-line"></i></a></li>
							</ul>
					</nav>
				</div>
			</div>
		</div>
	</div>
@endsection