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

				</div>

				<form method="get" action="{{route('branch.product.index', ['company' => request()->route('company')])}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Product/ Categoy/ Sub Category Name" name="product" value="{{ request('product') }}">
                                <span class="input-group-text"><a href="{{route('branch.product.index', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
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
									<th>Categoy</th>
									<th>Product</th>
									<th>Unit</th>
									<th>Price (₹)</th>
									<th>Stock</th>
									<th>Total Price (₹)</th>
								</tr>
							</thead>
							<tbody>
								@foreach($stocks as $stock)
									<tr>
										<td>
											{{ ($stocks->currentPage() - 1) * $stocks->perPage() + $loop->iteration }}
										</td>
										<td>
											@if($stock->product->image != null)
											<img src="{{ asset('storage/' . $stock->product->image) }}" class="logo-dark me-1" alt="Product" height="30">
											@else
											<img src="{{ asset('assets/images/product.jpg') }}" class="logo-dark me-1" alt="Product" height="30">
											@endif
										</td>
										<td>{{$stock->category->name}} - {{$stock->sub_category->name}}</td>
										<td>{{$stock->product->name}}</td>
										<td>{{$stock->product->metric->name}}</td>
										<td>{{$stock->product->price}}</td>
										<td>{{$stock->quantity}}</td>
										<td>{{ number_format($stock->product->price * $stock->quantity, 2) }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					{!! $stocks->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>
@endsection