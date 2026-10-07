@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Shop Setup</title>
@endsection

@section('body')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <p class="card-title mb-0">Shop Setup</p>
        <div class="btn-group">
            <a href="{{ route('admin.shop_setup.index', ['mode' => 'all']) }}"
               class="btn btn-sm {{ $mode === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All Shops</a>
            <a href="{{ route('admin.shop_setup.index', ['mode' => 'new']) }}"
               class="btn btn-sm {{ $mode === 'new' ? 'btn-primary' : 'btn-outline-primary' }}">Add New Details</a>
        </div>
    </div>

    <div class="card-body">
        @if($visibleShops->isEmpty())
            <div class="text-center text-muted py-5">
                @if($mode === 'new')
                    No new shops. Every shop already has categories, products, or GST rates.
                @else
                    No shops found.
                @endif
            </div>
        @else
            <div class="mb-3" style="max-width: 420px;">
                <label for="shopSelect" class="form-label text-muted">Choose shop</label>
                <select id="shopSelect" class="form-select">
                    <option value="">Select shop</option>
                    @foreach($visibleShops as $shop)
                        <option value="{{ route('admin.shop_setup.show', ['id' => $shop->id]) }}">
                            {{ $shop->name }} ({{ $shop->slug_name }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('shopSelect');
    if (select) {
        select.addEventListener('change', function () {
            if (this.value) window.location.href = this.value;
        });
    }
});
</script>
@endsection
