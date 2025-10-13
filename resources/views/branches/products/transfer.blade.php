@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Stock Transfer</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">Stock Transfer</p>
					</div>
					<a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#stockTransfer" href=""> <i class="ri-swap-box-fill me-2"></i>Stock Transfer</a>
				</div>

		    	@if(session('error_alert'))
		        <div class="alert alert-danger">
		          <strong>Warning! </strong>{{ session('error_alert') }}<br>
		        </div>
		        @endif

				<div class="">
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>From</th>
									<th>To</th>
                                   	<th>Image</th>
                                    <th>Categoy</th>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Transfer On</th>
								</tr>
							</thead>
							<tbody>
									@foreach($transfers as $transfer)
									<tr>
										<td>
											{{ ($transfers->currentPage() - 1) * $transfers->perPage() + $loop->iteration }}
										</td>

										<td>{{$transfer->transfer_from->user_name}}</td>

										<td>{{$transfer->transfer_to->user_name}}</td>

										<td>
											@if($transfer->product->image != null)
												<img src="{{ asset('storage/' . $transfer->product->image) }}" class="logo-dark me-1" alt="Product" height="30">
											@else
												<img src="{{ asset('assets/images/category.jpg') }}" class="logo-dark me-1" alt="Product" height="30">
											@endif
											
										</td>
										<td>{{$transfer->category->name}} >> {{$transfer->sub_category->name}}</td>

										<td>{{$transfer->product->name}}</td>

										<td>{{$transfer->quantity}} ({{$transfer->product->metric->name}})</td>

										<td>
											{{ \Carbon\Carbon::parse($transfer->transfer_on)->format('d M Y') }}
										</td>
									</tr>
									@endforeach
							</tbody>
						</table>
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					{!! $transfers->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>

	<div class="modal fade" id="stockTransfer" tabindex="-1" aria-labelledby="stockTransfer" aria-hidden="true">
	    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
	        <div class="modal-content">
	            <div class="modal-header">
	                <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Transfer Product</h5>
	                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	            </div>
	            <form class="row" action="{{route('branch.stock_transfer.store', ['company' => request()->route('company')])}}" method="post" id="transfer_submit">
	                @csrf
	                <div class="modal-body">

	                    <div class="row">
	                        <div class="col-md-12">
	                            <div class="mb-3">
	                                <label for="choices-single-groups" class="form-label text-muted">To</label>
	                                <select class="form-control" data-choices name="branch" id="branch">
	                                    <option value=""> Select </option>
	                                    @foreach($branches as $branch)
	                                    <option value="{{$branch->id}}">{{$branch->user_name}}</option>
	                                    @endforeach
	                                </select>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="row">
	                        <div class="col-md-12">
	                            <div class="mb-3">
	                                <label for="choices-single-groups" class="form-label text-muted">Select Category</label>
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
	                                <label for="choices-single-groups" class="form-label text-muted">Select Sub Category</label>
	                                <select class="form-control" name="sub_category" id="sub_category">
	                                    <option value=""> Select </option>
	                                </select>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="row">
	                        <div class="col-md-12">
	                            <div class="mb-3">
	                                <label for="choices-single-groups" class="form-label text-muted">Select Product</label>
	                                <select class="form-control" name="product" id="product">
	                                    <option value=""> Select </option>
	                                </select>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="row">
	                        <div class="col-md-12">
	                            <div class="mb-3">
	                                <label for="choices-single-groups" class="form-label text-muted">Matrics</label>
	                                <input type="text" id="unit" name="unit" class="form-control" disabled="">
	                            </div>
	                        </div>
	                    </div>
	                    <div class="row">
	                        <div class="col-md-12">
	                            <div class="mb-3">
	                                <label for="choices-single-groups" class="form-label text-muted">Available</label>
	                                <input disabled="" type="text" id="available" name="available" class="form-control" placeholder="0">
	                            </div>
	                        </div>
	                    </div>
	                    <div class="row">
	                        <div class="col-md-12">
	                            <div class="mb-3">
	                                <label for="choices-single-groups" class="form-label text-muted">Enter Quantity</label>
	                                <input type="number" id="quantity" name="quantity" class="form-control" min="1">
	                            </div>
	                        </div>
	                    </div>
	                </div>
	                <div class="modal-footer">
	                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
	                    <button type="submit" id="transfer" class="btn btn-primary">Transfer</button>
	                </div>
	            </form>
	        </div>
	    </div>
	</div>

@endsection

@section('script')
<script src="{{asset('assets/js/branches/transfer.js')}}"></script>
@endsection