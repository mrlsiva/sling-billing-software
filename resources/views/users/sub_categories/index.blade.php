@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Sub Category Create</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Sub Category</p>
					</div>
					<a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#subCategoryAdd"><i class='bx bxs-folder-plus'></i> Create Sub Category</a>
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

				<div class="">
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Image</th>
									<th>Category</th>
									<th>Sub Category</th>
									<th>Active / In-Active</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
									@foreach($sub_categories as $sub_category)
									<tr>
										<td>
											{{ ($sub_categories->currentPage() - 1) * $sub_categories->perPage() + $loop->iteration }}
										</td>
										<td>
											@if($sub_category->image != null)
												<img src="{{ asset('storage/' . $sub_category->image) }}" class="logo-dark me-1" alt="sub_category" height="30">
											@else
												<img src="{{ asset('assets/images/category.jpg') }}" class="logo-dark me-1" alt="sub_category" height="30">
											@endif
										</td>
										<td>{{$sub_category->category->name}}</td>

										<td>{{$sub_category->name}}</td>
										<td>
											<form action="{{ route('sub_category.status', ['company' => request()->route('company')]) }}" method="post">
												@csrf
												<input type="hidden" name="id" value="{{$sub_category->id}}">
											    <div class="form-check form-switch">
											        <input class="form-check-input" type="checkbox" name="is_active" onchange="this.form.submit()" {{ $sub_category->is_active == 1 ? 'checked' : '' }}>
											    </div>
											</form>

										</td>
										<td>
											@if($sub_category->is_active == 1)
												<span class="badge bg-soft-success text-success">Active</span>
											@else
												<span class="badge bg-soft-danger text-danger">In-Active</span>
											@endif
										</td>
										<td>
											<div class="d-flex gap-3">
												<a href="#!" onclick="sub_category_edit(this)" class="link-dark" data-system_id="{{$sub_category->id}}"><i class="ri-edit-line align-middle fs-20"></i></a>
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
					{!! $sub_categories->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>

    <div class="modal fade" id="subCategoryAdd" tabindex="-1" aria-labelledby="subCategoryAdd" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Add Sub Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="row" action="{{route('sub_category.store', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="name" class="form-label">Upload Sub Category Image</label>
	                                <div class="input-group">
	                                    <input type="file" name="image" id="image" class="form-control">
	                                </div>
		                        </div>
		                    </div>
	                   	</div>

	                	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Category</label>
		                            <select class="form-control" data-choices name="category" id="category">
                                        <option value=""> Select </option>
                                        @foreach($categories as $category)
                                        	<option value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                    </select>
		                        </div>
		                    </div>
	                   	</div>
	                	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Category</label>
		                            <input type="text" id="sub_category" name="sub_category" class="form-control">
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

    <div class="modal fade" id="subCategoryEdit" tabindex="-1" aria-labelledby="subCategoryEdit" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Edit Sub Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="row" action="{{route('sub_category.update', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="name" class="form-label">Upload Sub Category Image</label>
	                                <div class="input-group">
	                                    <input type="file" name="image" id="image" class="form-control">
	                                </div>
		                        </div>
		                    </div>
	                   	</div>
	                   	
	                	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Sub Category</label>
		                            <select class="form-control" name="category_id" id="category_id">
                                        <option value=""> Select </option>
                                        @foreach($categories as $category)
                                        	<option value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                    </select>
		                        </div>
		                    </div>
	                   	</div>
	                   <div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Sub Category Name</label>
		                            <input type="text" id="sub_category_name" name="sub_category_name" class="form-control">
		                            <input type="hidden" name="sub_category_id" id="sub_category_id">
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
<script src="{{asset('assets/js/users/sub_category.js')}}"></script>
@endsection