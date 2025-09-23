@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Stock</title>
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <p class="card-title">Stock</p>
                </div>
            </div>
            <div class="card-body pt-2 ">
                <ul class="nav nav-tabs nav-justified">

                    <li class="nav-item">
                        <a href="{{route('inventory.stock', ['company' => request()->route('company'),'shop' => Auth::user()->id,'branch' => 0])}}" class="nav-link {{ request()->route('branch') == 0 ? 'active' : '' }}" id="{{Auth::user()->id}}">
                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
                            <span class="d-none d-sm-block"><i class="ri-shopping-basket-line me-2"></i>{{Auth::user()->user_name}}</span>
                        </a>
                    </li>

                    @foreach($branches as $branch)
                    	<li class="nav-item">
	                        <a href="{{route('inventory.stock', ['company' => request()->route('company'),'shop' => Auth::user()->id,'branch' => $branch->id])}}" class="nav-link {{ request()->route('branch') == $branch->id ? 'active' : '' }}" id="{{$branch->id}}">
	                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
	                            <span class="d-none d-sm-block"><i class="ri-shopping-basket-line me-2"></i></i>{{$branch->user_name}}</span>
	                        </a>
                    	</li>
                    @endforeach
                    

                </ul>

                <form method="get" action="{{route('inventory.transfer', ['company' => request()->route('company'),'shop' => request()->route('shop'),'branch' => request()->route('branch')])}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Product/ Categoy/ Sub Category Name" name="product" value="{{ request('product') }}">
                                <span class="input-group-text"><a href="{{route('inventory.transfer', ['company' => request()->route('company'),'shop' => request()->route('shop'),'branch' => request()->route('branch')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-primary"> Search </button>
                        </div>
                    </div>
                </form>

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
                                        <th>Matrics</th>
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
													<img src="{{ asset('assets/images/category.jpg') }}" class="logo-dark me-1" alt="Product" height="30">
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
            <div class="card-footer border-0">
                {!! $stocks->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>
        </div>
    </div>
</div>

@endsection
