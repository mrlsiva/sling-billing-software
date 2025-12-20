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
                            <div class="input-group">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Product/ Categoy/ Sub Category Name" name="product" value="{{ request('product') }}" id="searchInput">
                                <span class="input-group-text" id="clearFilter" style="display: {{ request('product') ? 'inline-flex' : 'none' }}"><a href="{{route('branch.product.index', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
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
									<th>Matrics</th>
									<th>Price (₹)</th>
									<th>Stock</th>
									<th>Total Price (₹)</th>
									<th>Variation</th>
									<th>Action</th>
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

										@php
                                            $variation = \App\Models\StockVariation::where('stock_id', $stock->id)->first();
                                        @endphp

                                        @if($variation && ($variation->size_id !== null || $variation->colour_id !== null))
	                                        <td>
	                                            <a href="#!" class="text-dark view-variations" data-stock-id="{{ $stock->id }}" title="View Variations">
	                                                <i class="ri-eye-line fs-18"></i>
	                                            </a>
	                                        </td>
                                        @else
                                        	<td>
                                            	-
                                            </td>
                                        @endif
										<td>
											@php
												$user_detail = App\Models\UserDetail::where('user_id',Auth::user()->id)->first();
											@endphp
											@if($user_detail->is_scan_avaiable == 1)
												<div class="d-flex gap-3">
													<a href="{{ route('branch.product.qrcode', ['company' => request()->route('company'),'product' => $stock->product->id ]) }}" target="_blank" ><i class="ri-qr-code-line align-middle fs-20" title="Print QR"></i></a>

													<a href="{{ route('branch.product.barcode', ['company' => request()->route('company'),'id' => $stock->product->id ]) }}" target="_blank"><i class="ri-barcode-line align-middle fs-20" title="Bar Code"></i></a>
												</div>
											@else
												-
											@endif
										</td>
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

	<div class="modal fade" id="qrCode" tabindex="-1" aria-labelledby="qrCode" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">QR Download</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                	<div class="row">
                		<div class="col-md-4">

                		</div>
                	</div>
                </div>
                <div class="modal-footer">
                	<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                	<button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('modal')
<div class="modal fade" id="stockVariationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Stock Variations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- ajax content -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        let searchInput = document.getElementById("searchInput");
        let clearFilter = document.getElementById("clearFilter");

        function toggleClear() {
            if (searchInput.value.trim() !== "") {
                clearFilter.style.display = "inline-flex";
            } else {
                clearFilter.style.display = "none";
            }
        }

        // Run on load (for prefilled request values)
        toggleClear();

        // Run on typing
        searchInput.addEventListener("input", toggleClear);
    });
</script>

<script>
    $(document).on('click', '.view-variations', function (e) {
        e.preventDefault();

        let stockId = $(this).data('stock-id');

        $.ajax({
            url: stockId + "/get_stock_variation",
            type: "GET",
            success: function (html) {
                $('#stockVariationModal .modal-body').html(html);
                $('#stockVariationModal').modal('show');
            },
            error: function () {
                alert('Failed to load stock variations');
            }
        });
    });
</script>

@endsection