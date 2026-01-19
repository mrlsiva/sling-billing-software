@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Products</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Product</p>
					</div>
					<div>
						<a class="btn btn-outline-primary btn-sm fw-semibold" href="{{ route('product.create', ['company' => request()->route('company')]) }}"><i class='bx bxs-folder-plus'></i> Create Product</a>
						<a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkUpload"><i class='bx bxs-folder-plus'></i> Bulk Upload</a>
					</div>
				</div>

				<form method="get" action="{{route('product.index', ['company' => request()->route('company')])}}">
				    <div class="row mb-3 p-3">
				    	<div class="col-md-11">
				    		<div class="input-group">
				    			<span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
				    			<input type="text" class="form-control" placeholder="Product Name / Code / HSN Code" name="product" value="{{ request('product') }}" id="searchInput">
				    			<span class="input-group-text" id="clearFilter" style="display: {{ request('product') ? 'inline-flex' : 'none' }}"><a href="{{route('product.index', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
				    		</div>
				    	</div>

					    <div class="col-md-1">
					    	<button class="btn btn-primary"> Search </button>
					    </div>
				    </div>
		    	</form>

		    	@if(session('error_alert'))
		        <div class="alert alert-danger">
		          <strong>Warning! </strong>{{ session('error_alert') }}<br>
		        </div>
		        @endif

				<div class="">

					<div class="d-flex justify-content-end p-3">
						<form method="get" action="{{route('product.download', ['company' => request()->route('company')])}}">
							<input type="hidden" class="form-control" name="product" value="{{ request('product') }}">
							<button class="btn btn-success"> Download </button>
						</form>
					</div>
					
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Item Code</th>
									<th>Image</th>
									<th>Category >> Subcategory</th>
									<th>Name</th>
									<th>Price (₹)</th>
									<th>Tax</th>
									<th>Stock</th>
									<th>Active / In-Active</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
									@foreach($products as $product)
									<tr>
										<td>
											{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}
										</td>
										<td>{{$product->code}}</td>

										<td>
											@if($product->image != null)
												<img src="{{ asset('storage/' . $product->image) }}" class="logo-dark me-1" alt="Product" height="30">
											@else
												<img src="{{ asset('assets/images/category.jpg') }}" class="logo-dark me-1" alt="Product" height="30">
											@endif
											
										</td>
										<td>{{$product->category->name}} >> {{$product->sub_category->name}}</td>

										<td>{{$product->name}}</td>
										<!-- <td>{{$product->hsn_code}}</td> -->
										<td>{{$product->price}}</td>
										<td>{{ $product->tax->name }}%</td>

										<td>{{$product->quantity}}({{$product->metric->name}})</td>

										<td>
										    <form action="{{ route('product.status', ['company' => request()->route('company')]) }}" method="post" onsubmit="return confirm('Are you sure you want to change the product status?')">
										        @csrf
										        <input type="hidden" name="id" value="{{ $product->id }}">
										        <div class="form-check form-switch">
										            <input class="form-check-input" type="checkbox" name="is_active"
										                onchange="if(confirm('Are you sure you want to change the product status?')) { this.form.submit(); } else { this.checked = !this.checked; }"
										                {{ $product->is_active == 1 ? 'checked' : '' }}>
										        </div>
										    </form>
										</td>

										<!-- <td>
											@if($product->is_active == 1)
												<span class="badge bg-soft-success text-success">Active</span>
											@else
												<span class="badge bg-soft-danger text-danger">In-Active</span>
											@endif
										</td> -->
										<td>
											<div class="d-flex gap-3">
												<a href="{{ route('product.edit', ['company' => request()->route('company'),'id' => $product->id ]) }}"  class="link-dark"><i class="ri-edit-line align-middle fs-20"></i></a>
											</div>
										</td>
									</tr>
									@endforeach
							</tbody>
						</table>
						@if($products->isEmpty())
                            @include('no-data')
                        @endif
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					{!! $products->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
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
                <form class="row" action="{{route('product.bulk_upload', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                	<div class="row">
		                    <div class="col-md-12 d-flex justify-content-end">
		                    	<a href="{{ asset('assets/templates/product.xlsx') }}" download="Product_Template.xlsx">Download Template</a>
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
<script src="{{asset('assets/js/users/category.js')}}"></script>
@endsection