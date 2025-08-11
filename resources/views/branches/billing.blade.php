@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Billing</title>
@endsection

@section('body')
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
							<div class="border border-light  p-2 rounded">
								<div class="d-flex flex-wrap align-items-center gap-3">
									<div>
										<a href="#!" class="text-dark fs-12 fw-bold">Italian Burata Pizza</a>
										<p class="fs-10 my-1">12-Inch</p>
									</div>
									<div class="ms-lg-auto">
										<div
											class="input-step border bg-body-secondary p-1 mt-1 rounded d-inline-flex overflow-visible">
											<button type="button"
												class="minus bg-light text-dark border-0 rounded fs-20 lh-1 h-100">-</button>
											<input type="number"
												class="text-dark text-center border-0 bg-body-secondary rounded h-100"
												value="1" min="0" max="100" readonly="">
											<button type="button"
												class="plus bg-light text-dark border-0 rounded fs-20 lh-1 h-100">+</button>
										</div>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between px-1">
									<div>
										<p class="text-dark fw-semibold fs-16 mb-0">$12.00 </p>
									</div>
									<div class="d-flex align-content-center gap-1">
										<a href="#!"
											class="btn btn-soft-danger avatar-xs rounded d-flex align-items-center justify-content-center"><i
												class="ri-delete-bin-5-line align-middle fs-12"></i></a>
									</div>
								</div>
							</div>
							<div class="border border-light mt-3 p-2 rounded">
								<div class="d-flex flex-wrap align-items-center gap-3">
									<div>
										<a href="#!" class="text-dark fs-12 fw-bold">Italian Burata Pizza</a>
										<p class="fs-10 my-1">12-Inch</p>
									</div>
									<div class="ms-lg-auto">
										<div
											class="input-step border bg-body-secondary p-1 mt-1 rounded d-inline-flex overflow-visible">
											<button type="button"
												class="minus bg-light text-dark border-0 rounded fs-20 lh-1 h-100">-</button>
											<input type="number"
												class="text-dark text-center border-0 bg-body-secondary rounded h-100"
												value="1" min="0" max="100" readonly="">
											<button type="button"
												class="plus bg-light text-dark border-0 rounded fs-20 lh-1 h-100">+</button>
										</div>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between px-1">
									<div>
										<p class="text-dark fw-semibold fs-16 mb-0">$12.00 </p>
									</div>
									<div class="d-flex align-content-center gap-1">
										<a href="#!"
											class="btn btn-soft-danger avatar-xs rounded d-flex align-items-center justify-content-center"><i
												class="ri-delete-bin-5-line align-middle fs-12"></i></a>
									</div>
								</div>
							</div>
							<div class="border border-light mt-3 p-2 rounded">
								<div class="d-flex flex-wrap align-items-center gap-3">
									<div>
										<a href="#!" class="text-dark fs-12 fw-bold">Italian Burata Pizza</a>
										<p class="fs-10 my-1">12-Inch</p>
									</div>
									<div class="ms-lg-auto">
										<div
											class="input-step border bg-body-secondary p-1 mt-1 rounded d-inline-flex overflow-visible">
											<button type="button"
												class="minus bg-light text-dark border-0 rounded fs-20 lh-1 h-100">-</button>
											<input type="number"
												class="text-dark text-center border-0 bg-body-secondary rounded h-100"
												value="1" min="0" max="100" readonly="">
											<button type="button"
												class="plus bg-light text-dark border-0 rounded fs-20 lh-1 h-100">+</button>
										</div>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between px-1">
									<div>
										<p class="text-dark fw-semibold fs-16 mb-0">$12.00 </p>
									</div>
									<div class="d-flex align-content-center gap-1">
										<a href="#!"
											class="btn btn-soft-danger avatar-xs rounded d-flex align-items-center justify-content-center"><i
												class="ri-delete-bin-5-line align-middle fs-12"></i></a>
									</div>
								</div>
							</div>
							<div class="border border-light mt-3 p-2 rounded">
								<div class="d-flex flex-wrap align-items-center gap-3">
									<div>
										<a href="#!" class="text-dark fs-12 fw-bold">Italian Burata Pizza</a>
										<p class="fs-10 my-1">12-Inch</p>
									</div>
									<div class="ms-lg-auto">
										<div
											class="input-step border bg-body-secondary p-1 mt-1 rounded d-inline-flex overflow-visible">
											<button type="button"
												class="minus bg-light text-dark border-0 rounded fs-20 lh-1 h-100">-</button>
											<input type="number"
												class="text-dark text-center border-0 bg-body-secondary rounded h-100"
												value="1" min="0" max="100" readonly="">
											<button type="button"
												class="plus bg-light text-dark border-0 rounded fs-20 lh-1 h-100">+</button>
										</div>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between px-1">
									<div>
										<p class="text-dark fw-semibold fs-16 mb-0">$12.00 </p>
									</div>
									<div class="d-flex align-content-center gap-1">
										<a href="#!"
											class="btn btn-soft-danger avatar-xs rounded d-flex align-items-center justify-content-center"><i
												class="ri-delete-bin-5-line align-middle fs-12"></i></a>
									</div>
								</div>
							</div>
						</div>
						<div class="table-responsive">
							<table class="table table-bordered bg-light-subtle">
								<tbody>
									<tr>
										<td>
											<p class="d-flex mb-0 align-items-center gap-1">Items : </p>
										</td>
										<td class="text-end text-dark fw-medium">5 (Items)</td>
									</tr>
									<tr>
										<td>
											<p class="d-flex mb-0 align-items-center gap-1"> Subtotal : </p>
										</td>
										<td class="text-end text-dark fw-medium">$80.00</td>
									</tr>
									<tr>
										<td>
											<p class="d-flex mb-0 align-items-center gap-1"> Delivery Charge : </p>
										</td>
										<td class="text-end text-dark fw-medium">$00.00</td>
									</tr>
									<tr>
										<td>
											<p class="d-flex mb-0 align-items-center gap-1"> Estimated Tax (12.5%) :
											</p>
										</td>
										<td class="text-end text-dark fw-medium">$9.00</td>
									</tr>
									<tr>
										<td>
											<p class="d-flex mb-0 align-items-center gap-1 fw-semibold text-danger">
												Payable Amount : </p>
										</td>
										<td class="text-end text-success fw-semibold">$89.00</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class=" gap-1 hstack mt-3">
							<a href="#!" class="btn btn-danger w-100"><i class="ri-close-circle-line"></i> Clear</a>
							<a href="#!" class="btn btn-primary w-100"><i class="ri-shopping-basket-2-line"></i>
								Proceed</a>
						</div>
					</div>
					<div class="tab-pane" id="messagesTabsJustified">
						<h5 class="fw-semibold my-3">Customer Info </h5>
						<div class="row">

							<div class="col-md-12">

								<div class="mb-3">
									<label for="account_number" class="form-label">Mobile No</label>
									<input type="text" id="account_number" name="account_number" class="form-control"
										placeholder="Enter Customer Mobile Number" inputmode="numeric" pattern="[0-9]*"
										maxlength="16" value="{{old('account_number')}}">
								</div>

							</div>
							<div class="col-md-12">

								<div class="mb-3">
									<label for="category-name" class="form-label">Alternate Mobile No </label>
									<input type="text" id="confirm_account_number" name="confirm_account_number"
										class="form-control" placeholder="Alternate Mobile No" inputmode="numeric"
										pattern="[0-9]*" maxlength="16" value="{{old('confirm_account_number')}}">
								</div>

							</div>
							<div class="col-md-12">

								<div class="mb-3">
									<label for="bank" class="form-label">Customer Name</label>
									<input type="text" id="bank" name="bank" class="form-control"
										placeholder="Enter Customer Name" value="{{old('bank')}}">
								</div>

							</div>
							<div class="col-md-12">

								<div class="mb-3">
									<label for="branch" class="form-label">Address</label>
									<input type="text" id="branch" name="branch" class="form-control"
										placeholder="Enter Address" value="{{old('branch')}}">
								</div>

							</div>

						</div>
						<div class=" gap-1 hstack mt-3">
							<!-- <a href="#!" class="btn btn-danger w-100"><i class="ri-close-circle-line"></i> Clear</a> -->
							<a href="#!" class="btn btn-primary w-100"><i class="ri-shopping-basket-2-line"></i>
								Proceed</a>
						</div>
					</div>
					<div class="tab-pane" id="profileTabsJustified">
						<div class="mt-3">
							<div class="table-responsive">
								<table class="table table-bordered bg-light-subtle">
									<tbody>

										<tr>
											<td>
												<p class="d-flex mb-0 align-items-center gap-1 fw-semibold text-danger">
													Payable Amount : </p>
											</td>
											<td class="text-end text-success fw-semibold">$89.00</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<!-- <h5 class="fw-semibold my-3">Payment Method</h5> -->
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
						</div>
						<h5 class="fw-semibold my-3">Payment info</h5>
						<div class="row">
							<div class="col-md-12">

								<div class="mb-3">
									<label for="bank" class="form-label">Bank Name</label>
									<input type="text" id="bank" name="bank" class="form-control"
										placeholder="Enter Bank Name" value="{{old('bank')}}">
								</div>

							</div>
							<div class="col-md-12">

								<div class="mb-3">
									<label for="account_number" class="form-label">Enter A/C No</label>
									<input type="text" id="account_number" name="account_number" class="form-control"
										placeholder="Enter Account Number" inputmode="numeric" pattern="[0-9]*"
										maxlength="16" value="{{old('account_number')}}">
								</div>

							</div>
							<div class="col-md-12">

								<div class="mb-3">
									<label for="category-name" class="form-label">Confirm A/C No </label>
									<input type="text" id="confirm_account_number" name="confirm_account_number"
										class="form-control" placeholder="Confirm Account Number" inputmode="numeric"
										pattern="[0-9]*" maxlength="16" value="{{old('confirm_account_number')}}">
								</div>

							</div>
							<div class="col-md-12">

								<div class="mb-3">
									<label for="branch" class="form-label">Branch</label>
									<input type="text" id="branch" name="branch" class="form-control"
										placeholder="Enter Branch" value="{{old('branch')}}">
								</div>

							</div>

						</div>
						<div class=" gap-1 hstack mt-3">
							<!-- <a href="#!" class="btn btn-danger w-100"><i class="ri-close-circle-line"></i> Clear</a> -->
							<a href="#!" class="btn btn-primary w-100"><i class="ri-shopping-basket-2-line"></i>
								Proceed</a>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('script')
<script src="{{asset('assets/js/branches/billing.js')}}"></script>
@endsection