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

                <form method="get" action="{{route('inventory.stock', ['company' => request()->route('company'),'shop' => request()->route('shop'),'branch' => request()->route('branch')])}}" id="stockFilterForm">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Product/ Categoy/ Sub Category Name" name="product" value="{{ request('product') }}" id="searchInput">
                                <span class="input-group-text" id="clearFilter" style="display: {{ request('product') ? 'inline-flex' : 'none' }}"><a href="{{route('inventory.stock', ['company' => request()->route('company'),'shop' => request()->route('shop'),'branch' => request()->route('branch')])}}" class="link-dark"><i class="ri-close-large-line align-middle fs-20"></i></a></span>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-primary"> Search </button>
                        </div>

                        <div class="col-md-12 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_available" id="show_available" value="1"
                                    {{ request('show_available') ? 'checked' : '' }}
                                    onchange="document.getElementById('stockFilterForm').submit();">
                                <label class="form-check-label" for="show_available">Show available stock</label>
                            </div>
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
                                        <th>IMEI</th>
                                        <th>Variations</th>
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
											<td>
                                                <a href="javascript:void(0)" class="text-decoration-underline text-decoration-none viewProductTimeline" data-id="{{ $stock->product->id }}"data-name="{{ $stock->product->name }}"data-bs-toggle="modal" data-bs-target="#productTimelineModal">
                                                    {{$stock->product->name}}
                                                </a>
                                            </td>
											<td>{{$stock->product->metric->name ?? '-'}}</td>
											<td>{{$stock->product->price}}</td>
											<td>{{$stock->quantity}}</td>
											<td>{{ number_format($stock->product->price * $stock->quantity, 2) }}</td>

                                            <td>
                                                @if(!empty($stock->imei))
                                                    <a href="javascript:void(0);" 
                                                       onclick="showImei('{{ $stock->imei }}')" 
                                                       title="View IMEI">
                                                        <i class="ri-eye-line fs-18"></i>
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>

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
                                                <td>-</td>
                                            @endif

                                		</tr>
                                	@endforeach
                                </tbody>
                            </table>
                            @if($stocks->isEmpty())
                                @include('no-data')
                            @endif
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

<div class="modal fade" id="imeiModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">IMEI Numbers</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="imeiList"></div>
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
            url: "../../" + stockId + "/get_stock_variation",
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

<script>
    function showImei(imei) {
        let list = imei.split(',');
        let html = '<ul>';

        list.forEach(function(item) {
            if(item.trim() !== '') {
                html += '<li>' + item.trim() + '</li>';
            }
        });

        html += '</ul>';

        document.getElementById('imeiList').innerHTML = html;

        let modal = new bootstrap.Modal(document.getElementById('imeiModal'));
        modal.show();
    }
</script>


@endsection
