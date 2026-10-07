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
        <p class="text-muted fs-12 mb-0">Edit names, prices, GST, unit or stock below, then click "Save all changes" once. Add or remove buttons apply right away. Nothing is added to the shop until you click "Confirm and add".</p>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" form="editForm" class="btn btn-sm btn-primary"><i class="ri-save-line me-1"></i> Save all changes</button>
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

{{-- This form has no fields of its own. Every editable name/code/price/GST/unit/stock input on the
     page points at it via the HTML form="editForm" attribute, so one submit saves every edit together. --}}
<form id="editForm" method="post" action="{{ $draftUrl }}">
    @csrf
    <input type="hidden" name="action" value="update_all">
</form>

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

@foreach($plan['categories'] as $ci => $category)
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center gap-2">
            <input type="text" form="editForm" name="categories[{{ $ci }}][name]" value="{{ $category['name'] }}" class="form-control form-control-sm fw-semibold" style="max-width:320px" required>
            <form method="post" action="{{ $draftUrl }}" onsubmit="return confirm('Remove this category and everything in it?');">
                @csrf
                <input type="hidden" name="c" value="{{ $ci }}">
                <button name="action" value="delete_category" class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line me-1"></i> Remove category</button>
            </form>
        </div>
        <div class="card-body">
            @foreach($category['sub_categories'] as $si => $sub)
                <div class="d-flex justify-content-between align-items-center mt-2 mb-1 gap-2">
                    <input type="text" form="editForm" name="categories[{{ $ci }}][sub_categories][{{ $si }}][name]" value="{{ $sub['name'] }}" class="form-control form-control-sm fw-semibold" style="max-width:320px" required>
                    <form method="post" action="{{ $draftUrl }}" onsubmit="return confirm('Remove this sub-category and its products?');">
                        @csrf
                        <input type="hidden" name="c" value="{{ $ci }}">
                        <input type="hidden" name="s" value="{{ $si }}">
                        <button name="action" value="delete_sub_category" class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line me-1"></i> Remove sub-category</button>
                    </form>
                </div>

                <div class="table-responsive mb-2">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Product</th><th>Code</th><th>Price</th><th>GST</th><th>Unit</th><th>Stock</th><th></th></tr></thead>
                        <tbody>
                            @forelse($sub['products'] as $pi => $product)
                                @php $fieldPrefix = "categories[{$ci}][sub_categories][{$si}][products][{$pi}]"; @endphp
                                <tr>
                                    <td><input type="text" form="editForm" name="{{ $fieldPrefix }}[name]" class="form-control form-control-sm" value="{{ $product['name'] }}" required></td>
                                    <td><input type="text" form="editForm" name="{{ $fieldPrefix }}[code]" class="form-control form-control-sm" value="{{ $product['code'] }}" required></td>
                                    <td><input type="number" step="0.01" min="1" form="editForm" name="{{ $fieldPrefix }}[price]" class="form-control form-control-sm" value="{{ $product['price'] }}" required></td>
                                    <td>
                                        <select form="editForm" name="{{ $fieldPrefix }}[tax]" class="form-select form-select-sm">
                                            @foreach($plan['taxes'] as $rate)
                                                <option value="{{ $rate }}" {{ (int) $product['tax'] === (int) $rate ? 'selected' : '' }}>{{ $rate }}%</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select form="editForm" name="{{ $fieldPrefix }}[unit]" class="form-select form-select-sm">
                                            @foreach($plan['units'] as $unit)
                                                <option value="{{ $unit }}" {{ $product['unit'] === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" min="0" step="1" form="editForm" name="{{ $fieldPrefix }}[stock]" class="form-control form-control-sm" value="{{ $product['stock'] }}" required></td>
                                    <td>
                                        <form method="post" action="{{ $draftUrl }}" onsubmit="return confirm('Remove this product?');">
                                            @csrf
                                            <input type="hidden" name="c" value="{{ $ci }}">
                                            <input type="hidden" name="s" value="{{ $si }}">
                                            <input type="hidden" name="p" value="{{ $pi }}">
                                            <button name="action" value="delete_product" class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-muted">No products in this sub-category.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <form method="post" action="{{ $draftUrl }}" class="row g-1 align-items-center mb-3 ms-0">
                    @csrf
                    <input type="hidden" name="c" value="{{ $ci }}">
                    <input type="hidden" name="s" value="{{ $si }}">
                    <div class="col-md-3"><input type="text" name="name" class="form-control form-control-sm" placeholder="New product name" required></div>
                    <div class="col-md-1"><input type="text" name="code" class="form-control form-control-sm" placeholder="Code" required></div>
                    <div class="col-md-1"><input type="number" step="0.01" min="1" name="price" class="form-control form-control-sm" placeholder="Price" required></div>
                    <div class="col-md-1">
                        <select name="tax" class="form-select form-select-sm">
                            @foreach($plan['taxes'] as $rate)<option value="{{ $rate }}">{{ $rate }}%</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="unit" class="form-select form-select-sm">
                            @foreach($plan['units'] as $unit)<option value="{{ $unit }}">{{ $unit }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-1"><input type="number" min="0" step="1" name="stock" class="form-control form-control-sm" value="6" required></div>
                    <div class="col-md-2 text-end"><button name="action" value="add_product" class="btn btn-sm btn-outline-primary"><i class="ri-add-line me-1"></i> Add product</button></div>
                </form>
            @endforeach

            <form method="post" action="{{ $draftUrl }}" class="row g-1 align-items-center mt-2">
                @csrf
                <input type="hidden" name="c" value="{{ $ci }}">
                <div class="col-md-8"><input type="text" name="name" class="form-control form-control-sm" placeholder="New sub-category name" required></div>
                <div class="col-md-4 text-end"><button name="action" value="add_sub_category" class="btn btn-sm btn-outline-primary"><i class="ri-add-line me-1"></i> Add sub-category</button></div>
            </form>
        </div>
    </div>
@endforeach

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

<div class="card mb-3">
    <div class="card-body">
        <form method="post" action="{{ $draftUrl }}" class="row g-2 align-items-center">
            @csrf
            <div class="col-md-8"><input type="text" name="name" class="form-control" placeholder="New category name" required></div>
            <div class="col-md-4 text-end"><button name="action" value="add_category" class="btn btn-outline-primary"><i class="ri-add-line me-1"></i> Add category</button></div>
        </form>
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" form="editForm" class="btn btn-outline-primary"><i class="ri-save-line me-1"></i> Save all changes</button>
    <form method="post" action="{{ route('admin.shop_setup.demo', ['id' => $shop->id]) }}">
        @csrf
        <button class="btn btn-primary" {{ $categoryCount === 0 ? 'disabled' : '' }}><i class="ri-check-line me-1"></i> Confirm and add</button>
    </form>
    <a href="{{ route('admin.shop_setup.show', ['id' => $shop->id]) }}" class="btn btn-outline-secondary">Cancel</a>
</div>
@endsection
