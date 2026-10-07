@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Shop Setup - {{ $shop->name }}</title>
@endsection

@section('body')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Shop Setup: {{ $shop->name }} <span class="text-muted fs-12">({{ $shop->slug_name }})</span></h5>
    </div>
    <div class="d-flex gap-2">
        @if(!$hasSetup)
            <a href="{{ route('admin.shop_setup.demo_preview', ['id' => $shop->id]) }}" class="btn btn-sm btn-primary"><i class="ri-magic-line me-1"></i> Demo setup</a>
        @endif
        <a href="{{ route('admin.shop_setup.index', ['mode' => 'all']) }}" class="btn btn-sm btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Back
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

{{-- Summary --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-2"><div class="card p-3 text-center"><div class="fs-20 fw-semibold">{{ $summary['categories'] }}</div><div class="text-muted fs-12">Categories</div></div></div>
    <div class="col-6 col-md-2"><div class="card p-3 text-center"><div class="fs-20 fw-semibold">{{ $summary['sub_categories'] }}</div><div class="text-muted fs-12">Sub-categories</div></div></div>
    <div class="col-6 col-md-2"><div class="card p-3 text-center"><div class="fs-20 fw-semibold">{{ $summary['products'] }}</div><div class="text-muted fs-12">Products</div></div></div>
    <div class="col-6 col-md-2"><div class="card p-3 text-center"><div class="fs-20 fw-semibold">{{ $summary['staff'] }}</div><div class="text-muted fs-12">Staff</div></div></div>
    <div class="col-6 col-md-2"><div class="card p-3 text-center"><div class="fs-20 fw-semibold">{{ $summary['gst_rates']->count() }}</div><div class="text-muted fs-12">GST rates</div></div></div>
    <div class="col-6 col-md-2"><div class="card p-3 text-center"><div class="fs-20 fw-semibold">{{ $metrics->count() }}</div><div class="text-muted fs-12">Units</div></div></div>
</div>

<div class="row g-3">

    {{-- GST --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">GST</h6></div>
            <div class="card-body">
                <p class="mb-2"><span class="text-muted">GST number:</span> <span class="fw-semibold">{{ $summary['gst_number'] ?? 'Not set' }}</span></p>
                <p class="mb-3">
                    @forelse($summary['gst_rates'] as $rate)
                        <span class="badge bg-soft-primary text-primary me-1">{{ $rate->name }}%</span>
                    @empty
                        <span class="text-muted">No GST rates yet</span>
                    @endforelse
                </p>
                <form method="post" action="{{ route('admin.shop_setup.tax', ['id' => $shop->id]) }}" class="d-flex gap-2">
                    @csrf
                    <input type="number" step="0.01" min="0" max="100" name="name" class="form-control" placeholder="e.g. 18" required>
                    <button class="btn btn-primary">Add rate</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Staff --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Staff</h6></div>
            <div class="card-body">
                <ul class="list-unstyled mb-3">
                    @forelse($staff as $s)
                        <li class="d-flex justify-content-between border-bottom py-1">
                            <span>{{ $s->name }}</span><span class="text-muted">{{ $s->phone ?? '-' }}</span>
                        </li>
                    @empty
                        <li class="text-muted">No staff yet</li>
                    @endforelse
                </ul>
                <form method="post" action="{{ route('admin.shop_setup.staff', ['id' => $shop->id]) }}" class="row g-2">
                    @csrf
                    <div class="col-md-6"><input type="text" name="name" class="form-control" placeholder="Staff name" required></div>
                    <div class="col-md-4"><input type="text" name="phone" class="form-control" placeholder="Phone (10 digits)"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div>
                </form>
            </div>
        </div>
    </div>

    {{-- Categories --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Categories</h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.shop_setup.category_export', ['id' => $shop->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-download-2-line me-1"></i> Export</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importCategoriesModal"><i class="ri-upload-2-line me-1"></i> Import</button>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-3" style="max-height:200px;overflow:auto;">
                    @forelse($categories as $c)
                        <li class="border-bottom py-1">{{ $c->name }}</li>
                    @empty
                        <li class="text-muted">No categories yet</li>
                    @endforelse
                </ul>
                <form method="post" action="{{ route('admin.shop_setup.category', ['id' => $shop->id]) }}" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="name" class="form-control" placeholder="Category name" required>
                    <button class="btn btn-primary">Add</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Sub-categories --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Sub-categories</h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.shop_setup.sub_category_export', ['id' => $shop->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-download-2-line me-1"></i> Export</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importSubCategoriesModal"><i class="ri-upload-2-line me-1"></i> Import</button>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-3" style="max-height:200px;overflow:auto;">
                    @forelse($subCategories->take(200) as $sc)
                        <li class="border-bottom py-1 d-flex justify-content-between">
                            <span>{{ $sc->name }}</span>
                            <span class="text-muted fs-12">{{ optional($categories->firstWhere('id', $sc->category_id))->name }}</span>
                        </li>
                    @empty
                        <li class="text-muted">No sub-categories yet</li>
                    @endforelse
                </ul>
                @if($categories->isEmpty())
                    <p class="text-muted mb-0">Add a category first.</p>
                @else
                    <form method="post" action="{{ route('admin.shop_setup.sub_category', ['id' => $shop->id]) }}" class="row g-2">
                        @csrf
                        <div class="col-md-5">
                            <select name="category_id" class="form-select" required>
                                <option value="">Category</option>
                                @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-5"><input type="text" name="name" class="form-control" placeholder="Sub-category name" required></div>
                        <div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Products --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0">Products</h6>
                <form method="get" class="d-flex gap-2">
                    <input type="text" name="product" value="{{ request('product') }}" class="form-control form-control-sm" placeholder="Search name or code">
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All categories</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ request('category') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-outline-primary">Filter</button>
                </form>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.shop_setup.product_export', ['id' => $shop->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-download-2-line me-1"></i> Export</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importProductsModal"><i class="ri-upload-2-line me-1"></i> Import</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Name</th><th>Code</th><th>Category</th><th>Sub-category</th><th>Price</th><th>GST</th><th>Unit</th></tr></thead>
                        <tbody>
                            @forelse($products as $p)
                                <tr>
                                    <td>{{ $p->name }}</td>
                                    <td>{{ $p->code }}</td>
                                    <td>{{ $p->category->name ?? '-' }}</td>
                                    <td>{{ $p->sub_category->name ?? '-' }}</td>
                                    <td>₹{{ number_format($p->price, 2) }}</td>
                                    <td>{{ $p->tax->name ?? '-' }}%</td>
                                    <td>{{ $p->metric->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">No products found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {!! $products->links('pagination::bootstrap-5') !!}

                <hr>
                <h6>Add product</h6>
                @if($categories->isEmpty() || $summary['gst_rates']->isEmpty() || $metrics->isEmpty())
                    <p class="text-muted mb-0">
                        Before adding products, this shop needs:
                        @if($categories->isEmpty()) a category, @endif
                        @if($summary['gst_rates']->isEmpty()) a GST rate, @endif
                        @if($metrics->isEmpty()) a unit (metric), @endif
                        and a sub-category.
                    </p>
                @else
                    <form method="post" action="{{ route('admin.shop_setup.product', ['id' => $shop->id]) }}" class="row g-2">
                        @csrf
                        <div class="col-md-3">
                            <select name="category_id" class="form-select" required>
                                <option value="">Category</option>
                                @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="sub_category_id" class="form-select" required>
                                <option value="">Sub-category</option>
                                @foreach($subCategories as $sc)<option value="{{ $sc->id }}">{{ $sc->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Product name" required></div>
                        <div class="col-md-3"><input type="text" name="code" class="form-control" placeholder="Product code" required></div>
                        <div class="col-md-2"><input type="number" step="0.01" min="1" name="price" class="form-control" placeholder="Price" required></div>
                        <div class="col-md-2">
                            <select name="tax_id" class="form-select" required>
                                <option value="">GST</option>
                                @foreach($summary['gst_rates'] as $t)<option value="{{ $t->id }}">{{ $t->name }}%</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="metric_id" class="form-select" required>
                                <option value="">Unit</option>
                                @foreach($metrics as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-2"><input type="text" name="hsn_code" class="form-control" placeholder="HSN (optional)"></div>
                        <div class="col-md-2"><button class="btn btn-primary w-100">Add product</button></div>
                    </form>
                    <p class="text-muted fs-12 mt-2 mb-0">Opening stock is not set here. Stock is added from Inventory.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importCategoriesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('admin.shop_setup.category_import', ['id' => $shop->id]) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ asset('assets/templates/category.xlsx') }}" download="Category_Template.xlsx">Download Template</a>
                    </div>
                    <label class="form-label">Upload File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="importSubCategoriesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Sub-categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('admin.shop_setup.sub_category_import', ['id' => $shop->id]) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ asset('assets/templates/sub_category.xlsx') }}" download="Sub_Category_Template.xlsx">Download Template</a>
                    </div>
                    <p class="text-muted fs-12">The "Category" column must match a category that already exists in this shop.</p>
                    <label class="form-label">Upload File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="importProductsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('admin.shop_setup.product_import', ['id' => $shop->id]) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ asset('assets/templates/product.xlsx') }}" download="Product_Template.xlsx">Download Template</a>
                    </div>
                    <p class="text-muted fs-12">Category, sub-category, tax and metric (unit) must already exist in this shop.</p>
                    <label class="form-label">Upload File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
