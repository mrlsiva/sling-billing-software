@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Demo Setup - {{ $shop->name }}</title>
@endsection

@section('body')
@php
    $draftUrl = route('admin.shop_setup.demo_draft', ['id' => $shop->id]);
    $categoryCount = count($plan['categories']);
    $subCount = collect($plan['categories'])->sum(fn ($c) => count($c['sub_categories']));
    $productCount = collect($plan['categories'])->sum(fn ($c) => collect($c['sub_categories'])->sum(fn ($s) => count($s['products'])));
    $stockTotal = collect($plan['categories'])->sum(fn ($c) => collect($c['sub_categories'])->sum(fn ($s) => collect($s['products'])->sum('stock')));
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Demo setup preview: {{ $shop->name }} <span class="text-muted fs-12">({{ $shop->slug_name }})</span></h5>
        <p class="text-muted fs-12 mb-0">Categories, sub-categories and products are edited by uploading an Excel file below. GST, units, staff and bill setup use the forms as before. Nothing is added to the shop until you click "Confirm and add".</p>
    </div>
    <div class="d-flex gap-2">
        <form method="post" action="{{ $draftUrl }}" onsubmit="return confirm('Discard all changes and go back to the default demo?');">
            @csrf
            <input type="hidden" name="action" value="reset">
            <button class="btn btn-sm btn-outline-secondary"><i class="ri-refresh-line me-1"></i> Reset to default</button>
        </form>
        <a href="{{ route('admin.shop_setup.show', ['id' => $shop->id]) }}" class="btn btn-sm btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Back
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <p class="mb-2">This will add to <span class="fw-semibold">{{ $shop->name }}</span>:</p>
        <ul class="mb-0">
            <li>GST rates: {{ implode('%, ', $plan['taxes']) }}%</li>
            <li>Units: {{ implode(', ', $plan['units']) }}</li>
            <li>Categories: {{ $categoryCount }}</li>
            <li>Sub-categories: {{ $subCount }}</li>
            <li>Products: {{ $productCount }}</li>
            <li>Stock: {{ $stockTotal }} units in total</li>
            <li>Billed by staff: {{ count($plan['staff']) }}</li>
            <li>Bill setup: bill prefix <span class="fw-semibold">{{ $plan['bill_prefix'] }}</span> (bills will be numbered {{ $plan['bill_prefix'] }}01, {{ $plan['bill_prefix'] }}02, ...)</li>
            <li>Purchase: invoice <span class="fw-semibold">{{ $plan['invoice'] }}</span> from vendor <span class="fw-semibold">{{ $plan['vendor'] }}</span>, one line per product, cash payment, unpaid status</li>
        </ul>
    </div>
</div>

{{-- Categories, sub-categories and products: Excel only --}}
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0">Categories, sub-categories and products</h6>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.shop_setup.demo_catalog_template', ['id' => $shop->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-download-2-line me-1"></i> Download template</a>
            <a href="{{ route('admin.shop_setup.demo_catalog_current', ['id' => $shop->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-download-2-line me-1"></i> Download current</a>
        </div>
    </div>
    <div class="card-body">
        <form method="post" action="{{ route('admin.shop_setup.demo_catalog_import', ['id' => $shop->id]) }}" enctype="multipart/form-data" class="row g-2 align-items-end mb-4">
            @csrf
            <div class="col-md-8">
                <label class="form-label text-muted">Upload Excel (Category, Sub Category, Products columns; Products is comma-separated)</label>
                <input type="file" name="file" class="form-control" accept=".xlsx" required>
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100"><i class="ri-upload-2-line me-1"></i> Upload and replace</button>
            </div>
        </form>
        <p class="text-muted fs-12">
            Uploading replaces the categories, sub-categories and products below with what's in the file.
            To edit: click "Download current", change it in Excel, then upload it back &mdash; a product whose name
            doesn't change keeps its existing price, GST, unit and stock; a new product name gets those assigned
            automatically.
        </p>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>Category</th><th>Sub Category</th><th>Products</th></tr></thead>
                <tbody>
                    @forelse($plan['categories'] as $category)
                        @forelse($category['sub_categories'] as $sub)
                            <tr>
                                <td class="fw-semibold">{{ $category['name'] }}</td>
                                <td>{{ $sub['name'] }}</td>
                                <td>{{ collect($sub['products'])->pluck('name')->implode(', ') ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="fw-semibold">{{ $category['name'] }}</td>
                                <td colspan="2" class="text-muted">No sub-categories</td>
                            </tr>
                        @endforelse
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">No categories yet. Upload a file to add some.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($productCount > 0)
            <hr>
            <h6 class="mb-2">Product details (price, GST, unit, stock)</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Product</th><th>Code</th><th>Category</th><th>Sub Category</th><th>Price</th><th>GST</th><th>Unit</th><th>Stock</th></tr></thead>
                    <tbody>
                        @foreach($plan['categories'] as $category)
                            @foreach($category['sub_categories'] as $sub)
                                @foreach($sub['products'] as $product)
                                    <tr>
                                        <td>{{ $product['name'] }}</td>
                                        <td>{{ $product['code'] }}</td>
                                        <td>{{ $category['name'] }}</td>
                                        <td>{{ $sub['name'] }}</td>
                                        <td>₹{{ number_format($product['price'], 2) }}</td>
                                        <td>{{ $product['tax'] }}%</td>
                                        <td>{{ $product['unit'] }}</td>
                                        <td>{{ $product['stock'] }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Billed by (staff)</h6></div>
    <div class="card-body">
        <ul class="list-unstyled mb-3">
            @forelse($plan['staff'] as $i => $staffName)
                <li class="d-flex justify-content-between align-items-center border-bottom py-1">
                    <span>{{ $staffName }}</span>
                    <form method="post" action="{{ $draftUrl }}" onsubmit="return confirm('Remove this staff?');">
                        @csrf
                        <input type="hidden" name="i" value="{{ $i }}">
                        <button name="action" value="delete_staff" class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                    </form>
                </li>
            @empty
                <li class="text-muted">No staff. Billing will have no "Billed By" choice until you add one.</li>
            @endforelse
        </ul>
        <form method="post" action="{{ $draftUrl }}" class="row g-2 align-items-center">
            @csrf
            <div class="col-md-8"><input type="text" name="name" class="form-control" placeholder="New staff name" required></div>
            <div class="col-md-4 text-end"><button name="action" value="add_staff" class="btn btn-outline-primary"><i class="ri-add-line me-1"></i> Add staff</button></div>
        </form>
    </div>
</div>

<div class="d-flex gap-2">
    <form method="post" action="{{ route('admin.shop_setup.demo', ['id' => $shop->id]) }}">
        @csrf
        <button class="btn btn-primary" {{ $categoryCount === 0 ? 'disabled' : '' }}><i class="ri-check-line me-1"></i> Confirm and add</button>
    </form>
    <a href="{{ route('admin.shop_setup.show', ['id' => $shop->id]) }}" class="btn btn-outline-secondary">Cancel</a>
</div>
@endsection
