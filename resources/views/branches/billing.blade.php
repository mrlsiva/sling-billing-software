@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Billing</title>
@endsection

@section('body')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="row">
	<div class="col-md-8">
		<div class="card p-3">
			<!-- <div class="card-header d-flex align-items-center justify-content-between border-0">
					<h4 class="card-title mb-0">Explore Our Best Menu</h4>
					<div>
						<a href="{{route('branch.billing.pos', ['company' => request()->route('company')])}}" class="btn btn-primary btn-sm"><i class="ri-eye-line"></i> View All</a>
					</div>
				</div> -->

			<input type="text" id="scanner-input" autofocus style="opacity:0;position:absolute;left:-9999px;">

			<div class="row">
				<div class="col-md-12">
					<div class="mb-3">
						<label for="choices-single-groups" class="form-label text-muted">Billed By</label>
						<select class="form-control" data-choices name="billed_by" id="billed_by">
							<option value=""> Select </option>
							@foreach($staffs as $staff)
							<option value="{{$staff->id}}">{{$staff->name}}</option>
							@endforeach
						</select>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="choices-single-groups" class="form-label text-muted">Search by Product Name /
						Code</label>
					<div class="input-group">
						<!-- <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span> -->
						<input type="text" class="form-control" placeholder="Product Name / Code" name="product"
							id="product" value="{{ request('product') }}">
					</div>
				</div>
				<div class="col-md-4">
					<div class="">
						<label for="choices-single-groups" class="form-label text-muted">Category</label>
						<select class="form-control" data-choices name="category" id="category">
							<option value=""> Select </option>
							@foreach($categories as $category)
							<option value="{{$category->id}}"
								{{ request('category') == $category->id ? 'selected' : '' }}>{{$category->name}}
							</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="">
						<label for="choices-single-groups" class="form-label text-muted">Sub Category</label>
						<select class="form-control" name="sub_category" id="sub_category">
							@if(request('sub_category'))
							@php
							$sub_categories =
							App\Models\SubCategory::where([['category_id',request('category')],['is_active',1]])->get();
							@endphp

							@foreach($sub_categories as $sub_category)
							<option value=""> Select </option>
							<option value="{{$sub_category->id}}"
								{{ request('sub_category') == $sub_category->id ? 'selected' : '' }}>
								{{$sub_category->name}}</option>
							@endforeach
							@else
							<option value=""> Select </option>
							@endif
						</select>
					</div>
				</div>
			</div>

			<div class="row">

				<!-- <div class="col-md-4">
					<input type="hidden" name="filter" id="filterInput" value="{{ request('filter', 0) }}">
					<div class="form-check mb-3">
						<input type="checkbox" class="form-check-input" id="checkbox-veg" {{ request('filter') == 1 ? 'checked' : '' }}
						onchange="document.getElementById('filterInput').value = this.checked ? 1 : 0; ">
						<label class="form-check-label" for="checkbox-veg">
							Show in stock products only
						</label>
					</div>
				</div> -->

				<div class="col-md-4">
					<input type="hidden" name="filter" id="filterInput" value="1">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="checkbox-veg"
							{{ request('filter', 1) == 1 ? 'checked' : '' }}
							onchange="document.getElementById('filterInput').value = this.checked ? 1 : 0;">
						<label class="form-check-label" for="checkbox-veg">
							Show in stock products only
						</label>
					</div>
				</div>
			</div>
		</div>

		<div id="productContainer">
			<div class="row">
				@foreach($stocks as $stock)
				@php
				if ($stock->quantity === 0) {
				$cardClass = 'bg-soft-danger';
				$badgeClass = 'bg-danger';
				$showButton = false;
				} elseif ($stock->quantity <= 5) { $cardClass='bg-soft-warning' ; $badgeClass='bg-warning' ;
					$showButton=true; } else { $cardClass='' ; $badgeClass='bg-soft-success' ; $showButton=true; }
					@endphp <div class="col-md-4">
					<div class="card {{ $cardClass }}" onclick="add_to_cart(this)"
						data-system_id="{{ $stock->product_id }}" style="cursor:pointer;">
						<div class="card-body p-2">
							<div class="d-flex flex-column">
								<a href="#!" class="w-100 text-dark fs-12 fw-semibold text-truncate">
									{{ $stock->product->category->name }} - {{ $stock->product->sub_category->name }}
								</a>
								<a class="fs-10 text-dark fw-normal mb-0 w-100 text-truncate">
									{{ $stock->product->name }}
								</a>
							</div>
							<div class="d-flex align-items-center justify-content-between mt-2">
								<div>
									<p class="text-dark fw-semibold fs-12 mb-0">Rs {{ $stock->product->price }}</p>
								</div>
								<div class="d-flex align-content-center gap-1">
									<p class="mb-0 fs-12">{{ $stock->quantity }}</p>
									<p class="badge {{ $badgeClass }} fs-10 mb-1 text-dark py-1 px-2">Qty</p>
									<!-- @if($showButton)
					                            <button type="button"class="bg-light text-dark border-0 rounded fs-20 lh-1 h-100" onclick="add_to_cart(this)" data-system_id="{{ $stock->product_id }}">
					                                +
					                            </button>
					                        @endif -->
								</div>
							</div>
						</div>
					</div>
			</div>
			@endforeach

		</div>
	</div>

	<div class="d-flex justify-content-end" id="pagination" class="mt-3"></div>
</div>

<div class="col-md-4">
	<div class="card"
		style="max-height: calc(100vh - 106px); height: calc(100vh - 106px);min-height: calc(100vh - 106px);">
		<!-- d-flex flex-column justify-content-between -->
		<div class="card-body pt-2 ">
			<ul class="nav nav-tabs nav-justified">
				<li class="nav-item">
					<a href="#homeTabsJustified" data-bs-toggle="tab" aria-expanded="false" class="nav-link active"
						id="cart_tab">
						<span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
						<span class="d-none d-sm-block"><i class="ri-shopping-cart-line"></i></span>
					</a>
				</li>
				<li class="nav-item">
					<a href="#messagesTabsJustified" data-bs-toggle="tab" aria-expanded="false" class="nav-link"
						id="customer_tab">
						<span class="d-block d-sm-none"><i class="bx bx-envelope"></i></span>
						<span class="d-none d-sm-block"><i class="ri-id-card-line"></i></span>
					</a>
				</li>
				<li class="nav-item">

					<a data-bs-toggle="tab" aria-expanded="false" class="nav-link disabled" id="payment_tab"
						title="Cart is empty">
						<span class="d-block d-sm-none"><i class="bx bx-user"></i></span>
						<span class="d-none d-sm-block"><i class="ri-money-rupee-circle-line"></i></span>
					</a>
				</li>

			</ul>
			<div class="tab-content pt-2 text-muted">

				<div class="tab-pane show active" id="homeTabsJustified">
					<div id="order_detail" class="secret">
						<div class="d-flex justify-content-between align-items-center mb-3">
						<h5 class="fw-semibold my-3">Order Summery</h5>
						<button type="button" class="btn btn-sm fw-semibold d-flex align-items-center gap-2">
							<i class="ri-focus-3-line"></i> Focus
						</button>
						
					</div>
						<div class="" data-simplebar
							style="max-height: calc(100vh - 466px); min-height: calc(100vh - 466px);">
							<div id="cart_item">

							</div>
						</div>
						<div class="table-responsive">
							<table class="table table-bordered bg-light-subtle billing-table-pos">
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
											<p class="d-flex mb-0 align-items-center gap-1"> Tax :
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
							<a href="#!" class="btn btn-danger w-100" id="clear_cart"><i
									class="ri-close-circle-line"></i> Clear</a>
							<a href="#!" id="next_tab_user_info" class="btn btn-primary w-100"><i
									class="ri-arrow-right-circle-line"></i>
								Next</a>
						</div>
					</div>
					<div id="empty_order_detail" class="">
						<div class="row">
							<div class="col-md-12 text-center d-flex flex-column justify-content-center align-items-center gap-3"
								style="height: calc(100vh - 160px);">
								<h2 class="mb-0"><i class="ri-shopping-cart-line"></i></h2>
								<p class="mb-0">Cart is Empty</p>
								<button type="button" class="btn btn-outline-primary btn-sm fw-semibold d-flex align-items-center gap-2" id="focus_cart">
									<i class="ri-focus-3-line"></i> Focus
								</button>
							</div>
						</div>
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

					<div class="d-flex justify-content-between align-items-center mb-3">
						<h5 class="fw-semibold my-3 mb-0">Customer Info</h5>
						<!-- <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#customerAdd">
							        <i class="ri-add-line"></i> Customer
							    </button> -->

					</div>

					<div class="row"
						style="max-height: calc(100vh - 336px); height: calc(100vh - 336px);min-height: calc(100vh - 336px); overflow-y: auto;">

						<div class="col-md-12">
							<div class="mb-3">
								<label for="phone" class="form-label">Phone</label>
								<span class="text-danger">*</span>
								<input type="tel" name="phone" id="phone" class="form-control" maxlength="10"
									pattern="[0-9]{10}" inputmode="numeric" placeholder="Phone">
							</div>
						</div>

						<input type="hidden" name="customer" id="customer">

						<div class="col-md-12">
							<div class="mb-3">
								<label for="alt_phone" class="form-label">Alternate Phone</label>
								<input type="tel" name="alt_phone" id="alt_phone" class="form-control" maxlength="10"
									pattern="[0-9]{10}" inputmode="numeric" placeholder="Alternate Phone">
							</div>
						</div>

						<div class="col-md-12">
							<div class="mb-3">
								<label for="name" class="form-label">Name</label>
								<span class="text-danger">*</span>
								<input type="text" id="name" name="name" class="form-control" placeholder="Name">
							</div>
						</div>

						<div class="col-md-12">
							<div class="mb-3">
								<label for="address" class="form-label">Address</label>
								<span class="text-danger">*</span>
								<input type="text" id="address" name="address" class="form-control"
									placeholder="Address">
							</div>
						</div>

						<div class="col-md-12">
							<div class="mb-3">
								<label for="pincode" class="form-label">Pincode</label>
								<input type="number" id="pincode" name="pincode" class="form-control" min="1"
									placeholder="Pincode">
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
						<a href="#!" class="btn btn-danger w-100" id="previous_tab_home_info"><i
								class="ri-close-circle-line"></i> Previous</a>
						<a href="#!" id="next_tab_payment_info" class="btn btn-primary w-100"><i
								class="ri-arrow-right-circle-line"></i>
							Next</a>
					</div>

				</div>
				<div class="tab-pane" id="profileTabsJustified">
					<h5 class="fw-semibold my-3">Payment Method</h5>
					<div class="row g-2">
						<div class="col-md-12">
							<div class="mb-3">
								<select class="form-control" data-choices name="payment" id="payment">
									<option value="">Select</option>
									@foreach($payments as $payment)
									<option value="{{$payment->id}}">{{$payment->name}}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>

					<div class="row g-2 secret" id="cash">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="cash" class="form-label">Cash</label>
								<span class="text-danger">*</span>
								<input type="number" name="cash_amount" id="cash_amount" value="{{old('cash_amount')}}"
									class="form-control" placeholder="Amount">
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-check mb-3">
								<input type="checkbox" class="form-check-input" id="amount_fill" name="amount_fill">
								<label class="form-check-label" for="amount_fill">
									Full Amount
								</label>
							</div>
						</div>

						<div class=" d-flex justify-content-center">
							<button type="btn" class="btn btn-primary" id="cash_add" onclick="cash_add()"><i
									class="ri-bank-line"></i> Add</button>
						</div>
					</div>

					<div class="row g-2 secret" id="card">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="card_number" class="form-label">Card Number</label>
								<span class="text-danger">*</span>
								<input type="number" name="card_number" id="card_number" value="{{old('card_number')}}"
									class="form-control" placeholder="Card Number">
							</div>
						</div>
						<div class="col-md-12">
							<div class="mb-3">
								<label for="card_name" class="form-label">Card Name</label>
								<span class="text-danger">*</span>
								<input type="text" name="card_name" id="card_name" value="{{old('card_name')}}"
									class="form-control" placeholder="Card Name">
							</div>
						</div>
						<div class="col-md-12">
							<div class="mb-3">
								<label for="card_amount" class="form-label">Amount</label>
								<span class="text-danger">*</span>
								<input type="number" name="card_amount" id="card_amount" value="{{old('card_amount')}}"
									class="form-control" placeholder="Amount">
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-check mb-3">
								<input type="checkbox" class="form-check-input" id="card_fill" name="card_fill">
								<label class="form-check-label" for="card_fill">
									Full Amount
								</label>
							</div>
						</div>

						<div class=" d-flex justify-content-center">
							<button type="btn" class="btn btn-primary" id="card_add" onclick="card_add()"><i
									class="ri-bank-line"></i> Add</button>
						</div>
					</div>

					<div class="row g-2 secret" id="finance">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="finance_card" class="form-label">Finance Card Number</label>
								<span class="text-danger">*</span>
								<input type="number" name="finance_card" id="finance_card"
									value="{{old('finance_card')}}" class="form-control"
									placeholder="Finance Card Number">
							</div>
						</div>
						<div class="col-md-12">
							<div class="mb-3">
								<label for="finance_type" class="form-label">Finance</label>
								<span class="text-danger">*</span>
								<select class="form-control" data-choices name="finance_type" id="finance_type">
									<option value="">Select</option>
									@foreach($finances as $finance)
									<option value="{{$finance->id}}">{{$finance->name}}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-12">
							<div class="mb-3">
								<label for="finance_amount" class="form-label">Amount</label>
								<span class="text-danger">*</span>
								<input type="number" name="finance_amount" id="finance_amount"
									value="{{old('finance_amount')}}" class="form-control" placeholder="Amount">
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-check mb-3">
								<input type="checkbox" class="form-check-input" id="finance_fill" name="finance_fill">
								<label class="form-check-label" for="finance_fill">
									Full Amount
								</label>
							</div>
						</div>
						<div class=" d-flex justify-content-center">
							<button type="btn" class="btn btn-primary" id="finance_add" onclick="finance_add()"><i
									class="ri-bank-line"></i> Add</button>
						</div>
					</div>

					<div class="row g-2 secret" id="exchange">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="exchange_amount" class="form-label">Exchange</label>
								<span class="text-danger">*</span>
								<input type="number" name="exchange_amount" id="exchange_amount"
									value="{{old('exchange_amount')}}" class="form-control"
									placeholder="Exchange Amount">
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-check mb-3">
								<input type="checkbox" class="form-check-input" id="exchange_fill" name="exchange_fill">
								<label class="form-check-label" for="exchange_fill">
									Full Amount
								</label>
							</div>
						</div>
						<div class=" d-flex justify-content-center">
							<button type="btn" class="btn btn-primary" id="exchange_add" onclick="exchange_add()"><i
									class="ri-bank-line"></i> Add</button>
						</div>
					</div>

					<div class="row g-2 secret" id="credit">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="credit_amount" class="form-label">Credit</label>
								<span class="text-danger">*</span>
								<input type="number" name="credit_amount" id="credit_amount"
									value="{{old('credit_amount')}}" class="form-control" placeholder="Credit">
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-check mb-3">
								<input type="checkbox" class="form-check-input" id="credit_fill" name="credit_fill">
								<label class="form-check-label" for="credit_fill">
									Full Amount
								</label>
							</div>
						</div>
						<div class=" d-flex justify-content-center">
							<button type="btn" class="btn btn-primary" id="credit_add" onclick="credit_add()"><i
									class="ri-bank-line"></i> Add</button>
						</div>
					</div>

					<div class="row g-2 secret" id="cheque">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="cheque_number" class="form-label">Cheque No</label>
								<span class="text-danger">*</span>
								<input type="number" name="cheque_number" id="cheque_number"
									value="{{old('cheque_number')}}" class="form-control" placeholder="Cheque No">
							</div>
						</div>
						<div class="col-md-12">
							<div class="mb-3">
								<label for="cheque_amount" class="form-label">Amount</label>
								<span class="text-danger">*</span>
								<input type="number" name="cheque_amount" id="cheque_amount"
									value="{{old('cheque_amount')}}" class="form-control" placeholder="Amount">
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-check mb-3">
								<input type="checkbox" class="form-check-input" id="cheque_fill" name="cheque_fill">
								<label class="form-check-label" for="cheque_fill">
									Full Amount
								</label>
							</div>
						</div>
						<div class=" d-flex justify-content-center">
							<button type="btn" class="btn btn-primary" id="cheque_add" onclick="cheque_add()"><i
									class="ri-bank-line"></i> Add</button>
						</div>
					</div>

					<div class="row g-2 secret" id="upi">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="upi_amount" class="form-label">UPI Amount</label>
								<span class="text-danger">*</span>
								<input type="number" name="upi_amount" id="upi_amount" value="{{old('upi_amount')}}"
									class="form-control" placeholder="UPI Amount">
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-check mb-3">
								<input type="checkbox" class="form-check-input" id="upi_fill" name="upi_fill">
								<label class="form-check-label" for="upi_fill">
									Full Amount
								</label>
							</div>
						</div>
						<div class=" d-flex justify-content-center">
							<button type="btn" class="btn btn-primary" id="upi_add" onclick="upi_add()"><i
									class="ri-bank-line"></i> Add</button>
						</div>
					</div>

					<h5 class="fw-semibold my-3">Payment info</h5>
					<div class="table-responsive">
						<table class="table table-bordered bg-light-subtle">
							<thead>
								<tr>
									<td class="fw-semibold">Method</td>
									<td class="fw-semibold">Amount</td>
									<td class="fw-semibold">Action</td>
								</tr>
							</thead>
							<tbody id="payment-info-body">
								<!-- Rows will be appended dynamically -->
							</tbody>
							<tfoot>
								<tr>
									<td>
										<p class="d-flex mb-0 align-items-center gap-1 fw-semibold text-success"
											id="received_cash">Total Cash: </p>
									</td>
									<td colspan="2">
										<p class="d-flex mb-0 align-items-center gap-1 fw-semibold text-success"
											id="amount_text1">Payable Amount: </p>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class=" gap-1 hstack mt-3">
						<a href="#!" class="btn btn-danger w-100" id="previous_tab_user_info"><i
								class="ri-close-circle-line"></i> Previous</a>
						<a href="#!" onclick="submit()" class="btn btn-primary w-100"><i
								class="ri-shopping-basket-2-line"></i>
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
		<div class="modal-content">
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
								<input type="tel" name="phone" id="phone" class="form-control" maxlength="10"
									pattern="[0-9]{10}" inputmode="numeric" required="">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="mb-3">
								<label for="choices-single-groups" class="form-label text-muted">Alternate Phone</label>
								<input type="tel" id="alt_phone" name="alt_phone" class="form-control" maxlength="10"
									pattern="[0-9]{10}" inputmode="numeric">
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