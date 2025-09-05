@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Product Create</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Product</p>
					</div>
					<a class="btn btn-outline-primary btn-sm fw-semibold" href="{{ route('product.create', ['company' => request()->route('company')]) }}"><i class='bx bxs-folder-plus'></i> Create Product</a>
				</div>

				<form method="get" action="{{route('product.index', ['company' => request()->route('company')])}}">
				    <div class="row mb-3 p-3">
				    	<div class="col-md-11">
				    		<div class="input-group input-group-lg">
				    			<span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
				    			<input type="text" class="form-control" placeholder="Product Name / Code / HSN Code" name="product" value="{{ request('product') }}">
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
									<th>Name</th>
									<th>Product Code</th>
									<th>HSN Code</th>
									<th>Price (₹)</th>
									<th>Active / In-Active</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
									@foreach($products as $product)
									<tr>
										<td>
											{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}
										</td>
										<td>
											@if($product->image != null)
												<img src="{{ asset('storage/' . $product->image) }}" class="logo-dark me-1" alt="Product" height="30">
											@else
												<img src="{{ asset('assets/images/product.jpg') }}" class="logo-dark me-1" alt="Product" height="30">
											@endif
										</td>
										<td>{{$product->name}}</td>
										<td>{{$product->code}}</td>
										<td>{{$product->hsn_code}}</td>
										<td>{{$product->price}}</td>
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

										<td>
											@if($product->is_active == 1)
												<span class="badge bg-soft-success text-success">Active</span>
											@else
												<span class="badge bg-soft-danger text-danger">In-Active</span>
											@endif
										</td>
										<td>
											<div class="d-flex gap-3">
												<a href="{{ route('product.edit', ['company' => request()->route('company'),'id' => $product->id ]) }}"  class="link-dark"><i class="ri-edit-line align-middle fs-20"></i></a>
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
					{!! $products->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>
@endsection

@section('script')
<script src="{{asset('assets/js/users/category.js')}}"></script>
@endsection