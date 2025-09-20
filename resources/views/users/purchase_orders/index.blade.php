@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Purchase Order</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Purchase Order</p>
					</div>
					<a class="btn btn-outline-primary btn-sm fw-semibold" href="{{ route('vendor.purchase_order.create', ['company' => request()->route('company')]) }}"><i class='bx bxs-folder-plus'></i> Create Purchase Order</a>
				</div>

				<form method="get" action="{{route('vendor.purchase_order.index', ['company' => request()->route('company')])}}">
				    <div class="row mb-3 p-3">
				    	<div class="col-md-11">
				    		<div class="input-group">
				    			<span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
				    			<input type="text" class="form-control" placeholder="Vendor Name" name="vendor" value="{{ request('vendor') }}">
				    			<span class="input-group-text"><a href="{{route('vendor.purchase_order.index', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
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
									<th>Vendor</th>
									<th>Category</th>
									<th>Product</th>
									<th>Quantity</th>
									<th>Price (₹)</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
									@foreach($purchase_orders as $purchase_order)
									<tr>
										<td>
											{{ ($purchase_orders->currentPage() - 1) * $purchase_orders->perPage() + $loop->iteration }}
										</td>
										<td>{{$purchase_order->vendor->name}}</td>
										<td>{{$purchase_order->category->name}} - {{$purchase_order->sub_category->name}}</td>
										<td>{{$purchase_order->product->name}}</td>
										<td>{{$purchase_order->quantity}} ({{$purchase_order->metric->name}})</td>
										<td>{{$purchase_order->gross_cost}}</td>
										<td>
										    <a onclick="purchase_detail({{ $purchase_order->id }})" class="link-dark">
										        <i class="ri-eye-line align-middle fs-20" title="View Order"></i>
										    </a>
										</td>

									</tr>
									@endforeach
							</tbody>
						</table>
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					{!! $purchase_orders->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>

	<div class="modal fade" id="purchaseDetail" tabindex="-1" aria-labelledby="purchaseDetail" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Purchase Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

	            <div class="modal-body">
		        </div>

		        <div class="modal-footer">
		            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>	
		        </div>
                
            </div>
        </div>
    </div>

@endsection

@section('script')
<script src="{{asset('assets/js/users/purchase.js')}}"></script>
@endsection