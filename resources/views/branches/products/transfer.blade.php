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

				<form method="get" action="{{route('branch.stock_transfer.transfer', ['company' => request()->route('company')])}}">
				    <div class="row mb-3 p-3">
				    	<div class="col-md-6">
				    		<div class="input-group">
				    			<span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
				    			<input type="text" class="form-control" placeholder="Product Name" name="product" value="{{ request('product') }}" id="searchInput">
				    			<span class="input-group-text" id="clearFilter" style="display: {{ request('product') ? 'inline-flex' : 'none' }}"><a href="{{route('branch.stock_transfer.transfer', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
				    		</div>
				    	</div>
				    	<div class="col-md-5">
				    		@php
						        $user = App\Models\User::where('id', Auth::user()->parent_id)->first();
						    @endphp
				    		<select class="form-control" name="branch" id="branch">
				    			<option value=""> Select Branch </option>
				    			<option value="{{$user->id}}" {{ request('branch') == $user->id ? 'selected' : '' }}> {{$user->user_name}} </option>
				    			@foreach($branches as $branch)
				    			<option value="{{$branch->id}}" {{ request('branch') == $branch->id ? 'selected' : '' }}>{{$branch->user_name}}</option>
				    			@endforeach
				    		</select>
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
						            <label for="transfer_to" class="form-label text-muted">Transfer to</label>
						            <select class="form-control" id="transfer_to" name="transfer_to">
						                <option value="">Select</option>
						                <option value="1">Branch</option>
						                <option value="2">HO</option>
						            </select>
						        </div>
						    </div>
						</div>

						<div class="row" id="branch_field" style="display: none;">
						    <div class="col-md-12">
						        <div class="mb-3">
						            <label for="branch_select" class="form-label text-muted">To</label>
						            <select class="form-control" id="branch_select" name="branch">
						                <option value="">Select</option>
						                @foreach($branches as $branch)
						                    <option value="{{ $branch->id }}">{{ $branch->user_name }}</option>
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

	                <div class="row mt-3" id="imei_section" style="display:none;">
						    <div class="col-md-12">
						        <label class="form-label text-muted">Select IMEI Numbers</label>

						        <div id="imei_list"
						             class="border rounded p-2 d-flex flex-wrap gap-3"
						             style="max-height:250px; overflow-y:auto;">
						        </div>
						    </div>
						</div>

						<div id="variations_section"></div>
						
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