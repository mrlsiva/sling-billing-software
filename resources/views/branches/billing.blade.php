@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Billing</title>
@endsection

@section('body')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<div class="row">
	<div class="col-md-8">
		<div class="card">
			<div class="card-header d-flex align-items-center justify-content-between border-0">
				<h4 class="card-title mb-0">Explore Our Best Menu</h4>
				<div>
					<a href="{{route('branch.billing.pos', ['company' => request()->route('company')])}}" class="btn btn-primary btn-sm"><i class="ri-eye-line"></i> View All</a>
				</div>
			</div>
		</div>
		<form id="filterForm" method="GET" action="{{ route('branch.billing.pos', ['company' => request()->route('company')]) }}">

			<div class="row">

				<div class="col-md-4">
					<div class="mb-3">
						<label for="choices-single-groups" class="form-label text-muted">Category</label>
						<select class="form-control" name="category" id="category">
							<option value=""> Select </option>
							@foreach($categories as $category)
							<option value="{{$category->id}}" {{ request('category') == $category->id ? 'selected' : '' }}>{{$category->name}}</option>
							@endforeach
						</select>
					</div>
				</div>

				<div class="col-md-4">
					<div class="mb-3">
						<label for="choices-single-groups" class="form-label text-muted">Sub Category</label>
						<select class="form-control" name="sub_category" id="sub_category">

							@if(request('sub_category'))
								@php
									$sub_categories = App\Models\SubCategory::where([['category_id',request('category')],['is_active',1]])->get();
								@endphp

								@foreach($sub_categories as $sub_category)
									<option value=""> Select </option>
									<option value="{{$sub_category->id}}" {{ request('sub_category') == $sub_category->id ? 'selected' : '' }}>{{$sub_category->name}}</option>
								@endforeach
							@else
								<option value=""> Select </option>
							@endif
						</select>
					</div>
				</div>
				
				<div class="col-md-4">
		    		<input type="hidden" name="filter" id="filterInput" value="{{ request('filter', 0) }}">
				    <div class="form-check mb-3">
				        <input type="checkbox" 
				               class="form-check-input" 
				               id="checkbox-veg" 
				               {{ request('filter') == 1 ? 'checked' : '' }}
				               onchange="document.getElementById('filterInput').value = this.checked ? 1 : 0; document.getElementById('filterForm').submit();">
				        <label class="form-check-label" for="checkbox-veg">
				            Show in stock products only
				        </label>
				    </div>
				</div>
			</div>

			<div class="row">
				<div class="d-flex justify-content-end">
					<button class="btn btn-primary btn-sm"><i class="ri-search-line"></i> Search</button>
				</div>
			</div>

		</form>
		<div class="row">
			@foreach($stocks as $stock)

				@if($stock->quantity === 0)
					<div class="col-xl-3 col-lg-3 col-md-4">
						<div class="card bg-soft-danger">
							<div class="card-body p-2">
								<div class="d-flex flex-column">
									<a href="#!" class="w-100 text-dark fs-12 fw-semibold text-truncate">{{$stock->product->category->name}} - {{$stock->product->sub_category->name}}</a>
									<a class="fs-10 text-dark fw-normal mb-0 w-100 text-truncate">{{$stock->product->name}}</a>
								</div>
								<div class="d-flex align-items-center justify-content-between mt-2">
									<div>
										<p class="text-dark fw-semibold fs-12 mb-0">Rs {{$stock->product->price}}</p>
									</div>
									<div class="d-flex align-content-center gap-1">
										<p class="mb-0 fs-12">{{$stock->quantity}}</p>
										<p class="badge bg-danger fs-10 mb-1 text-dark py-1 px-2"> Qty</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				@elseif($stock->quantity <= 5)
					<div class="col-xl-3 col-lg-3 col-md-4">
						<div class="card bg-soft-warning">
							<div class="card-body p-2">
								<div class="d-flex flex-column">
									<a href="#!" class="w-100 text-dark fs-12 fw-semibold text-truncate">{{$stock->product->category->name}} - {{$stock->product->sub_category->name}}</a>
									<a class="fs-10 text-dark fw-normal mb-0 w-100 text-truncate">{{$stock->product->name}}</a>
								</div>
								<div class="d-flex align-items-center justify-content-between mt-2">
									<div>
										<p class="text-dark fw-semibold fs-12 mb-0">Rs {{$stock->product->price}}</p>
									</div>
									<div class="d-flex align-content-center gap-1">
										<p class="mb-0 fs-12">{{$stock->quantity}}</p>
										<p class="badge bg-warning fs-10 mb-1 text-dark py-1 px-2"> Qty</p>
										<button type="button" class=" bg-light text-dark border-0 rounded fs-20 lh-1 h-100" onclick="add_to_cart(this)"data-system_id="{{$stock->product_id}}"> + </button>

									</div>
								</div>
							</div>
						</div>
					</div>
				@else
					<div class="col-xl-3 col-lg-3 col-md-4">
						<div class="card">
							<div class="card-body p-2">
								<div class="d-flex flex-column">
									<a href="#!" class="w-100 text-dark fs-12 fw-semibold text-truncate">{{$stock->product->category->name}} - {{$stock->product->sub_category->name}}</a>
									<a class="fs-10 text-dark fw-normal mb-0 w-100 text-truncate">{{$stock->product->name}}</a>
								</div>
								<div class="d-flex align-items-center justify-content-between mt-2">
									<div>
										<p class="text-dark fw-semibold fs-12 mb-0">Rs {{$stock->product->price}}</p>
									</div>
									<div class="d-flex align-content-center gap-1">
										<p class="mb-0 fs-12">{{$stock->quantity}}</p>
										<p class="badge bg-soft-success fs-10 mb-1 text-dark py-1 px-2"> Qty</p>
										<button type="button" class="bg-light text-dark border-0 rounded fs-20 lh-1 h-100" onclick="add_to_cart(this)"data-system_id="{{$stock->product_id}}"> + </button>

									</div>
								</div>
							</div>
						</div>
					</div>
				@endif
			@endforeach
		</div>
		<div class="mb-3">
			{!! $stocks->withQueryString()->links('pagination::bootstrap-5') !!}
		</div>
	</div>
	<div class="col-md-4">
		<div class="card"
			style="max-height: calc(100vh - 106px); height: calc(100vh - 106px);min-height: calc(100vh - 106px);">
			<!-- d-flex flex-column justify-content-between -->
			<div class="card-body pt-2 ">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a href="#homeTabsJustified" data-bs-toggle="tab" aria-expanded="false" class="nav-link active">
							<span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
							<span class="d-none d-sm-block"><i class="ri-shopping-cart-line"></i></span>
						</a>
					</li>
					<li class="nav-item">
						<a href="#messagesTabsJustified" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
							<span class="d-block d-sm-none"><i class="bx bx-envelope"></i></span>
							<span class="d-none d-sm-block"><i class="ri-id-card-line"></i></span>
						</a>
					</li>
					<li class="nav-item">
						<a href="#profileTabsJustified" data-bs-toggle="tab" aria-expanded="true" class="nav-link ">
							<span class="d-block d-sm-none"><i class="bx bx-user"></i></span>
							<span class="d-none d-sm-block"><i class="ri-bank-card-line"></i></span>
						</a>
					</li>

				</ul>
				<div class="tab-content pt-2 text-muted">

					<div class="tab-pane show active" id="homeTabsJustified">
						<h5 class="fw-semibold my-3">Order Summery</h5>
						<div class="" data-simplebar style="max-height: 200px;">
							<div id="cart_item">
								
							</div>
						</div>
						<div class="table-responsive">
							<table class="table table-bordered bg-light-subtle">
								<tbody>
									<tr>
										<td>
											<p class="d-flex mb-0 align-items-center gap-1">Items : </p>
										</td>
										<td class="text-end text-dark fw-medium" id="total_item">0 (Items)</td>
									</tr>
									<tr>
										<td>
											<p class="d-flex mb-0 align-items-center gap-1"> Subtotal : </p>
										</td>
										<td class="text-end text-dark fw-medium" id="sub_total">₹0.00</td>
									</tr>
									<tr>
										<td>
											<p class="d-flex mb-0 align-items-center gap-1"> Delivery Charge : </p>
										</td>
										<td class="text-end text-dark fw-medium">₹0.00</td>
									</tr>
									<tr>
										<td>
											<p class="d-flex mb-0 align-items-center gap-1"> Estimated Tax :
											</p>
										</td>
										<td class="text-end text-dark fw-medium" id="tax">₹0.00</td>
									</tr>
									<tr>
										<td>
											<p class="d-flex mb-0 align-items-center gap-1 fw-semibold text-danger">
												Payable Amount : </p>
										</td>
										<td class="text-end text-success fw-semibold" id="amount">$0.00</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class=" gap-1 hstack mt-3">
							<a href="#!" class="btn btn-danger w-100" id="clear_cart"><i class="ri-close-circle-line"></i> Clear</a>
							<a href="#!" id="next_tab_user_info" class="btn btn-primary w-100"><i class="ri-arrow-right-circle-line"></i>
								Next</a>
						</div>
					</div>
					<div class="tab-pane" id="messagesTabsJustified">
						<div class="mt-3">
							<div class="table-responsive">
								<table class="table table-bordered bg-light-subtle">
									<tbody>

										<tr>
											<td>
												<p class="d-flex mb-0 align-items-center gap-1 fw-semibold text-danger">
													Payable Amount : </p>
											</td>
											<td class="text-end text-success fw-semibold" id="amount_text">₹0.00</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

						<div class="d-flex justify-content-between align-items-center">
						    <h5 class="fw-semibold my-3 mb-0">Customer Info</h5>
						    <!-- <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#customerAdd">
						        <i class="ri-add-line"></i> Customer
						    </button> -->

						</div>

						<div class="row">

							<div class="col-md-12">
								<div class="mb-3">
									<label for="phone" class="form-label">Phone</label>
									<span class="text-danger">*</span>
									<input type="tel" name="phone" id="phone" class="form-control" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" placeholder="Phone">
								</div>
							</div>

							<input type="hidden" name="customer" id="customer">

							<div class="col-md-12">
								<div class="mb-3">
									<label for="alt_phone" class="form-label">Alternate Phone</label>
									<input type="tel" name="alt_phone" id="alt_phone" class="form-control" maxlength="10" pattern="[0-9]{10}" inputmode="numeric"  placeholder="Alternate Phone">
								</div>
							</div>

							<div class="col-md-12">
								<div class="mb-3">
									<label for="name" class="form-label">Name</label>
									<span class="text-danger">*</span>
		                            <input type="text" id="name" name="name" class="form-control"  placeholder="Name">
								</div>
							</div>

							<div class="col-md-12">
								<div class="mb-3">
									<label for="address" class="form-label">Address</label>
									<span class="text-danger">*</span>
									<input type="text" id="address" name="address" class="form-control" placeholder="Address" >
								</div>
							</div>

							<div class="col-md-12">
								<div class="mb-3">
									<label for="payment_method" class="form-label">Gender</label>
                                    <select class="form-control" name="gender" id="gender">
                                        <option value="">Select</option>
                                        @foreach($genders as $gender)
                                        	<option value="{{$gender->id}}">{{$gender->name}}</option>
                                        @endforeach
                                    </select>
								</div>
							</div>

							<div class="col-md-12">
								<div class="mb-3">
									<label for="dod" class="form-label">DOB</label>
									<input type="date" id="dob" name="dob" class="form-control" placeholder="DOB">
								</div>
							</div>

						</div>

						<div class=" gap-1 hstack mt-3">
							<a href="#!" class="btn btn-danger w-100" id="previous_tab_home_info"><i class="ri-close-circle-line"></i> Previous</a>
							<a href="#!" id="next_tab_payment_info" class="btn btn-primary w-100"><i class="ri-arrow-right-circle-line"></i>
								Next</a>
						</div>
						
					</div>
					<div class="tab-pane" id="profileTabsJustified">
						<h5 class="fw-semibold my-3">Payment Method</h5>
						<div class="row g-2">
							<div class="col-md-4">
								<div class="form-check form-checkbox-success ps-0">
									<label for="cash-payment" class="w-100">
										<div class="d-flex align-items-center p-3 rounded gap-2 border">
											<div class="d-flex align-items-center gap-2">
												<h5 class="mb-0"><i class="ri-cash-fill text-success"></i> Cash</h5>
											</div>
											<div class="ms-auto">
												<input class="form-check-input float-end" type="radio" name="shipping"
													id="cash-payment" checked>
											</div>
										</div>
									</label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-check form-checkbox-success ps-0">
									<label for="card-payment" class="w-100">
										<div class="d-flex align-items-center p-3 rounded gap-2 border">
											<div class="d-flex align-items-center gap-2">
												<h5 class="mb-0"><i class="ri-bank-card-fill text-success"></i> Card
												</h5>
											</div>
											<div class="ms-auto">
												<input class="form-check-input float-end" type="radio" name="shipping"
													id="card-payment">
											</div>
										</div>
									</label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-check form-checkbox-success ps-0">
									<label for="upi-payment" class="w-100">
										<div class="d-flex align-items-center p-3 rounded gap-2 border">
											<div class="d-flex align-items-center gap-2">
												<h5 class="mb-0"><i class="ri-bank-fill text-success"></i> UPI</h5>
											</div>
											<div class="ms-auto">
												<input class="form-check-input float-end" type="radio" name="shipping"
													id="upi-payment">
											</div>
										</div>
									</label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-check form-checkbox-success ps-0">
									<label for="exchange-payment" class="w-100">
										<div class="d-flex align-items-center p-3 rounded gap-2 border">
											<div class="d-flex align-items-center gap-2">
												<h5 class="mb-0"><i class="ri-exchange-funds-line text-success"></i> Exchange</h5>
											</div>
											<div class="ms-auto">
												<input class="form-check-input float-end" type="radio" name="shipping"
													id="exchange-payment">
											</div>
										</div>
									</label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-check form-checkbox-success ps-0">
									<label for="finanace-payment" class="w-100">
										<div class="d-flex align-items-center p-3 rounded gap-2 border">
											<div class="d-flex align-items-center gap-2">
												<h5 class="mb-0"><i class="ri-wallet-fill text-success"></i> Finanace</h5>
											</div>
											<div class="ms-auto">
												<input class="form-check-input float-end" type="radio" name="shipping"
													id="finanace-payment">
											</div>
										</div>
									</label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-check form-checkbox-success ps-0">
									<label for="credit-payment" class="w-100">
										<div class="d-flex align-items-center p-3 rounded gap-2 border">
											<div class="d-flex align-items-center gap-2">
												<h5 class="mb-0"><i class="ri-hand-coin-fill text-success"></i> Credit</h5>
											</div>
											<div class="ms-auto">
												<input class="form-check-input float-end" type="radio" name="shipping"
													id="credit-payment">
											</div>
										</div>
									</label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-check form-checkbox-success ps-0">
									<label for="cheque-payment" class="w-100">
										<div class="d-flex align-items-center p-3 rounded gap-2 border">
											<div class="d-flex align-items-center gap-2">
												<h5 class="mb-0"><i class="ri-cash-line text-success"></i> Cheque</h5>
											</div>
											<div class="ms-auto">
												<input class="form-check-input float-end" type="radio" name="shipping"
													id="cheque-payment">
											</div>
										</div>
									</label>
								</div>
							</div>
						</div>
						<h5 class="fw-semibold my-3">Payment info</h5>
						<div class="table-responsive">
							<table class="table table-bordered bg-light-subtle">
								<thead>
									<tr>
										<td class="fw-semibold">Method</td>
										<td class="fw-semibold">Amount</td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Cash</td>
										<td>₹0.00</td>
									</tr>
									<tr>
										<td>Card</td>
										<td>₹0.00</td>
									</tr>
									<tr>
										<td>UPI</td>
										<td>₹0.00</td>
									</tr>
								</tbody>
								<tfoot>
									<tr>
										<td>
											<p class="d-flex mb-0 align-items-center gap-1 fw-semibold text-success">Total Cash: </p>
										</td>
										<td>
											<p class="d-flex mb-0 align-items-center gap-1 fw-semibold text-success" id="amount_text1">Payable Amount: </p>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
						<div class=" gap-1 hstack mt-3">
							<a href="#!" class="btn btn-danger w-100" id="previous_tab_user_info"><i class="ri-close-circle-line"></i> Previous</a>
							<a href="#!" class="btn btn-primary w-100"><i class="ri-shopping-basket-2-line"></i>
								Proceed</a>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="customerAdd" tabindex="-1" aria-labelledby="customerAdd" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
		<div class="modal-content" >
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Add Customer</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form class="row" id="customer_add">
				@csrf
				<div class="modal-body">

					<div class="row">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="choices-single-groups" class="form-label text-muted">Name</label>
								<span class="text-danger">*</span>
								<input type="text" id="name" name="name" class="form-control" required="">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="choices-single-groups" class="form-label text-muted">Phone</label>
								<span class="text-danger">*</span>
								<input type="tel" name="phone" id="phone" class="form-control" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" required="">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="choices-single-groups" class="form-label text-muted">Alternate Phone</label>
								<input type="tel" id="alt_phone" name="alt_phone" class="form-control" maxlength="10" pattern="[0-9]{10}" inputmode="numeric">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="choices-single-groups" class="form-label text-muted">Address</label>
								<span class="text-danger">*</span>
								<input type="text" id="address" name="address" class="form-control" required="">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="choices-single-groups" class="form-label text-muted">Pincode</label>
								<input type="number" id="pincode" name="pincode" class="form-control" min="1">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="payment_method" class="form-label">Gender</label>
								<select class="form-control" data-choices name="gender" id="gender">
									<option value="">Select</option>
									@foreach($genders as $gender)
									<option value="{{$gender->id}}">{{$gender->name}}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="choices-single-groups" class="form-label text-muted">DOB</label>
								<input type="date" id="dob" name="dob" class="form-control" max="{{ date('Y-m-d') }}">
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="{{asset('assets/js/branches/billing.js')}}"></script>
@endsection