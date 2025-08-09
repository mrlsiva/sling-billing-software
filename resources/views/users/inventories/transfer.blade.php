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
                    <p class="card-title">Product Transfer</p>
                </div>
                <a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal"
                    data-bs-target="#productTransfer" href=""> <i class="ri-swap-box-fill me-2"></i>Product Transfer</a>
            </div>
            <div class="card-body pt-2 ">
                <ul class="nav nav-tabs nav-justified">

                    <li class="nav-item">
                        <a href="{{route('inventory.transfer', ['company' => request()->route('company'),'shop' => Auth::user()->id,'branch' => 0])}}" class="nav-link {{ request()->route('branch') == 0 ? 'active' : '' }}" id="{{Auth::user()->id}}">
                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
                            <span class="d-none d-sm-block"><i class="ri-shopping-basket-line me-2"></i>{{Auth::user()->user_name}}</span>
                        </a>
                    </li>

                    @foreach($branches as $branch)
                    	<li class="nav-item">
	                        <a href="{{route('inventory.transfer', ['company' => request()->route('company'),'shop' => Auth::user()->id,'branch' => $branch->id])}}" class="nav-link {{ request()->route('branch') == $branch->id ? 'active' : '' }}" id="{{$branch->id}}">
	                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
	                            <span class="d-none d-sm-block"><i class="ri-shopping-basket-line me-2"></i></i>{{$branch->user_name}}</span>
	                        </a>
                    	</li>
                    @endforeach
                    

                </ul>
                <div class="tab-content pt-2 text-muted">
                    <div class="tab-pane show active" id="homeTabsJustified">
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
                                        <th>Stock at</th>
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
                    </div>
                </div>
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
            <form class="row" action="{{route('inventory.transfered', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">

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
                                <label for="choices-single-groups" class="form-label text-muted">Unit</label>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Clear</button>
                    <button type="submit" class="btn btn-primary">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{asset('assets/js/users/transfer.js')}}"></script>
@endsection