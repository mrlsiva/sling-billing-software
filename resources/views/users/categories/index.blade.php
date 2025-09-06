@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Category Create</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Category</p>
					</div>
					<a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#categoryAdd"><i class='bx bxs-folder-plus'></i> Create Category</a>
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

		        <form method="get" action="{{route('category.index', ['company' => request()->route('company')])}}">
				    <div class="row mb-3 p-3">
				    	<div class="col-md-11">
				    		<div class="input-group input-group-lg">
				    			<span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
				    			<input type="text" class="form-control" placeholder="Name" name="name" value="{{ request('name') }}">
				    			<span class="input-group-text"><a href="{{route('category.index', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
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
									<th>Image</th>
									<th>Category Name</th>
									<th>No.Of sub Category</th>
									<th>Active / In-Active</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
									@foreach($categories as $category)
									<tr>
										<td>
											{{ ($categories->currentPage() - 1) * $categories->perPage() + $loop->iteration }}
										</td>
										<td>
											@if($category->image != null)
												<img src="{{ asset('storage/' . $category->image) }}" class="logo-dark me-1" alt="category" height="30">
											@else
												<img src="{{ asset('assets/images/category.jpg') }}" class="logo-dark me-1" alt="category" height="30">
											@endif
										</td>
										<td>{{$category->name}}</td>
										<td>{{$category->sub_categories->count()}}</td>
										<td>
										    <form action="{{ route('category.status', ['company' => request()->route('company')]) }}" method="post">
										        @csrf
										        <input type="hidden" name="id" value="{{ $category->id }}">
										        <div class="form-check form-switch">
										            <input class="form-check-input" type="checkbox" name="is_active"
										                onchange="if(confirm('Are you sure you want to change the category status?')) { this.form.submit(); } else { this.checked = !this.checked; }"
										                {{ $category->is_active == 1 ? 'checked' : '' }}>
										        </div>
										    </form>
										</td>

										<td>
											@if($category->is_active == 1)
												<span class="badge bg-soft-success text-success">Active</span>
											@else
												<span class="badge bg-soft-danger text-danger">In-Active</span>
											@endif
										</td>
										<td>
											<div class="d-flex gap-3">
												<a href="#!" onclick="category_edit(this)" class="link-dark" data-system_id="{{$category->id}}"><i class="ri-edit-line align-middle fs-20"></i></a>
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
					{!! $categories->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>

    <div class="modal fade" id="categoryAdd" tabindex="-1" aria-labelledby="categoryAdd" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="row" action="{{route('category.store', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="name" class="form-label">Upload Category Image</label>
	                                <div class="input-group">
	                                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
	                                </div>
		                        </div>
		                    </div>
	                   	</div>
	                   	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Category Name</label>
		                            <input type="text" id="category" name="category" class="form-control" required="">
		                        </div>
		                    </div>
	                   	</div>
	                </div>
	                <div class="modal-footer">
	                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
	                    <button type="submit" class="btn btn-primary">Save</button>
	                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="categoryEdit" tabindex="-1" aria-labelledby="categoryEdit" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="row" action="{{route('category.update', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                	<div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="name" class="form-label">Upload Category Image</label>
	                                <div class="input-group">
	                                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
	                                </div>
		                        </div>
		                    </div>
	                   </div>

	                   <div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                            <label for="choices-single-groups" class="form-label text-muted">Category Name</label>
		                            <input type="text" id="category_name" name="category_name" class="form-control" required="">
		                            <input type="hidden" name="category_id" id="category_id">
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
<script src="{{asset('assets/js/users/category.js')}}"></script>
@endsection