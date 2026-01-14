@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Product Transfer</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">Product Transfer</p>
					</div>
					<div>
						<a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkTransfer" href="javascript:void(0);">
    						<i class="ri-file-excel-2-line me-2"></i> Bulk Transfer
						</a>

						<a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#productTransfer" href=""> <i class="ri-swap-box-fill me-2"></i>Product Transfer</a>
					</div>
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

				<form method="get" action="{{route('inventory.transfer', ['company' => request()->route('company')])}}">
				    <div class="row mb-3 p-3">
				    	<div class="col-md-6">
				    		<div class="input-group">
				    			<span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
				    			<input type="text" class="form-control" placeholder="Product Name" name="product" value="{{ request('product') }}" id="searchInput">
				    			<span class="input-group-text" id="clearFilter" style="display: {{ request('product') ? 'inline-flex' : 'none' }}"><a href="{{route('inventory.transfer', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
				    		</div>
				    	</div>
				    	<div class="col-md-5">
				    		<select class="form-control" name="branch" id="branch">
				    			<option value=""> Select Branch </option>
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

				<div class="">
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Invoice</th>
									<th>Branch</th>
                                    <th>Transfer On</th>
                                    <th>Bill</th>
								</tr>
							</thead>
							<tbody>
									@foreach($transfers as $transfer)
									<tr>
										<td>
											{{ ($transfers->currentPage() - 1) * $transfers->perPage() + $loop->iteration }}
										</td>

										<td>{{$transfer->invoice}}</td>

										<td>{{$transfer->transfer_to->user_name}}</td>

										<td>
											{{ \Carbon\Carbon::parse($transfer->transfer_on)->format('d M Y') }}
										</td>

										<td>
											<a href="{{ route('inventory.transfer.get_bill', ['company' => request()->route('company'),'id' => $transfer->id ]) }}" class="link-dark" target="_blank"><i class="ri-printer-line align-middle fs-20" title="Print Bill"></i></a>
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

	<div class="modal fade" id="productTransfer" tabindex="-1" aria-labelledby="productTransfer" aria-hidden="true">
	    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
	        <div class="modal-content">
	            <div class="modal-header">
	                <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Transfer Product</h5>
	                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	            </div>
	            <form class="row" action="{{route('inventory.transfer.store', ['company' => request()->route('company')])}}" method="post" id="transfer_submit">
	                @csrf
	                <div class="modal-body">

	                    <div class="row">
	                        <div class="col-md-12">
	                            <div class="mb-3">
	                                <label for="choices-single-groups" class="form-label text-muted">Select Branch</label>
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
	                                <input type="number" id="quantity" name="quantity" class="form-control" min="1" readonly="">
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

	                </div>
	                <div class="modal-footer">
	                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
	                    <button type="submit" id="transfer" class="btn btn-primary">Transfer</button>
	                </div>
	            </form>
	        </div>
	    </div>
	</div>

	<div class="modal fade" id="bulkTransfer" tabindex="-1" aria-labelledby="bulkTransferLabel" aria-hidden="true">
	    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
	        <div class="modal-content">
	            <div class="modal-header">
	                <h5 class="modal-title" id="bulkTransferLabel">Bulk Transfer</h5>
	                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	            </div>
	            <form class="row" action="{{ route('inventory.transfer.bulk', ['company' => request()->route('company')]) }}" method="post" enctype="multipart/form-data" id="transfer_submit">

	                @csrf
	                <div class="modal-body">

	                    <div class="row">
	                        <div class="col-md-12">
	                            <div class="mb-3">
	                                <label for="choices-single-groups" class="form-label text-muted">Select Branch</label>
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
		                    <div class="col-md-12 d-flex justify-content-end">
		                    	<a href="{{ asset('assets/templates/bulk_transfer.xlsx') }}" download="Bulk_Transfer.xlsx">Download Template</a>
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
	                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
	                    <button type="submit" id="transfer" class="btn btn-primary">Transfer</button>
	                </div>
	            </form>
	        </div>
	    </div>
	</div>
@endsection

@section('script')
<script src="{{asset('assets/js/users/transfer.js')}}"></script>
@endsection