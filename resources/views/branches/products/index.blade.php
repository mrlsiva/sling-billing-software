@extends('layouts.master')

@section('title')
<title>{{ config('app.name') }} | Products</title>
@endsection

@section('body')

@php
    $user_detail = App\Models\UserDetail::where('user_id', Auth::user()->id)->first();
@endphp

<div class="row">
    <div class="col-xl-12">
        <div class="card p-3">

            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <p class="card-title">All Products</p>
                </div>
                <div class="d-flex justify-content-end gap-2 align-items-center">
                    <div class="form-check mb-0">
                        <form method="get" action="{{ route('branch.product.index', ['company' => request()->route('company')]) }}" id="stockFilterForm">
                            <input type="hidden" name="product" value="{{ request('product') }}">
                            <input class="form-check-input" type="checkbox" id="checkbox-stock" name="stock_in" value="1"
                                {{ request('stock_in') == 1 ? 'checked' : '' }}
                                onchange="this.form.submit()">
                            <label class="form-check-label" for="checkbox-stock">Show only available products</label>
                        </form>
                    </div>
                    <form method="get" action="{{ route('branch.product.download', ['company' => request()->route('company')]) }}">
                        <input type="hidden" name="product" value="{{ request('product') }}">
                        <input type="hidden" name="stock_in" value="{{ request('stock_in') }}">
                        <button class="btn btn-success btn-sm"><i class="ri-download-2-line me-1"></i> Download</button>
                    </form>
                </div>
            </div>

            <div class="card-body pt-2">
                <form method="get" action="{{ route('branch.product.index', ['company' => request()->route('company')]) }}">
                    <input type="hidden" name="stock_in" value="{{ request('stock_in') }}">
                    <div class="row mb-2">
                        <div class="col-md-11">
                            <div class="input-group">
                                <span class="input-group-text py-0" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Product / Category / Sub Category Name"
                                    name="product" value="{{ request('product') }}" id="searchInput">
                                <span class="input-group-text" id="clearFilter"
                                    style="display: {{ request('product') ? 'inline-flex' : 'none' }}">
                                    <a href="{{ route('branch.product.index', ['company' => request()->route('company')]) }}" class="link-dark">
                                        <i class="ri-close-large-line align-middle fs-20"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-1 text-end">
                            <button class="btn btn-primary">Search</button>
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
                                    <th>Category</th>
                                    <th>Product</th>
                                    <th>Metrics</th>
                                    <th>Price (₹)</th>
                                    <th>Stock</th>
                                    <th>Total Price (₹)</th>
                                    <th>Variation</th>
                                    <th>IMEI</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $stock)
                                @php
                                    $variation = \App\Models\StockVariation::where('stock_id', $stock->id)->first();
                                @endphp
                                <tr>
                                    <td>{{ ($stocks->currentPage() - 1) * $stocks->perPage() + $loop->iteration }}</td>

                                    <td>
                                        @if($stock->product->image)
                                            <img src="{{ asset('storage/' . $stock->product->image) }}" alt="Product" height="30">
                                        @else
                                            <img src="{{ asset('assets/images/category.jpg') }}" alt="Product" height="30">
                                        @endif
                                    </td>

                                    <td>{{ $stock->category->name }} - {{ $stock->sub_category->name }}</td>
                                    <td>{{ $stock->product->name }}</td>
                                    <td>{{ $stock->product->metric->name ?? '-' }}</td>
                                    <td>{{ $stock->product->price }}</td>
                                    <td>{{ $stock->quantity }}</td>
                                    <td>{{ number_format($stock->product->price * $stock->quantity, 2) }}</td>

                                    <td>
                                        @if($variation && ($variation->size_id !== null || $variation->colour_id !== null))
                                            <a href="#!" class="text-dark view-variations" data-stock-id="{{ $stock->id }}" title="View Variations">
                                                <i class="ri-eye-line fs-18"></i>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if(!empty($stock->imei))
                                            <a href="javascript:void(0);" onclick="showImei('{{ $stock->imei }}')" title="View IMEI">
                                                <i class="ri-eye-line fs-18"></i>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if($user_detail && $user_detail->is_scan_avaiable == 1)
                                            <div class="d-flex gap-3">
                                                <a href="{{ route('branch.product.qrcode', ['company' => request()->route('company'), 'product' => $stock->product->id]) }}" target="_blank">
                                                    <i class="ri-qr-code-line align-middle fs-20" title="Print QR"></i>
                                                </a>
                                                <a href="{{ route('branch.product.barcode', ['company' => request()->route('company'), 'id' => $stock->product->id]) }}" target="_blank">
                                                    <i class="ri-barcode-line align-middle fs-20" title="Bar Code"></i>
                                                </a>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
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
            {{-- end card-body --}}

            <div class="card-footer border-0">
                {!! $stocks->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>

        </div>
    </div>
</div>

{{-- IMEI Modal --}}
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
            <div class="modal-body"></div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("searchInput");
        const clearFilter = document.getElementById("clearFilter");

        function toggleClear() {
            clearFilter.style.display = searchInput.value.trim() !== "" ? "inline-flex" : "none";
        }

        toggleClear();
        searchInput.addEventListener("input", toggleClear);
    });
</script>

<script>
    $(document).on('click', '.view-variations', function (e) {
        e.preventDefault();
        const stockId = $(this).data('stock-id');
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

    function showImei(imei) {
        const list = imei.split(',').filter(i => i.trim() !== '');
        const html = '<ul>' + list.map(i => '<li>' + i.trim() + '</li>').join('') + '</ul>';
        document.getElementById('imeiList').innerHTML = html;
        new bootstrap.Modal(document.getElementById('imeiModal')).show();
    }
</script>
@endsection
