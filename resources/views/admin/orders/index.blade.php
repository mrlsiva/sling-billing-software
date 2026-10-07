@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Orders</title>
@endsection

@section('body')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <p class="card-title mb-0">Orders</p>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.order.export', request()->query()) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-download-2-line me-1"></i> Export</a>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importOrdersModal"><i class="ri-upload-2-line me-1"></i> Import historical orders</button>
        </div>
    </div>

    <div class="card-body">
        <form method="get" action="{{ route('admin.order.index') }}" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="shop_id" id="shopSelect" class="form-select" onchange="this.form.submit()">
                    <option value="">All shops</option>
                    @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="branch_id" class="form-select" {{ $branches->isEmpty() ? 'disabled' : '' }}>
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control" placeholder="From"></div>
            <div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control" placeholder="To"></div>
            <div class="col-md-2"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Bill ID or phone"></div>
            <div class="col-md-1"><button class="btn btn-primary w-100">Filter</button></div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover table-centered">
                <thead class="bg-light-subtle">
                    <tr>
                        <th>Shop</th>
                        <th>Branch</th>
                        <th>Bill ID</th>
                        <th>Amount</th>
                        <th>Billed On</th>
                        <th>Billed By</th>
                        <th>Customer</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->shop->name ?? '-' }}</td>
                            <td>{{ $order->branch->name ?? 'Shop (no branch)' }}</td>
                            <td>{{ $order->bill_id }}</td>
                            <td>₹{{ number_format($order->bill_amount - ($order->is_refunded ? ($order->total_refund ?? 0) : 0), 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($order->billed_on)->format('d-m-Y h:i A') }}</td>
                            <td>{{ $order->billedBy->name ?? '-' }}</td>
                            <td>{{ $order->customer->phone ?? '-' }} ({{ $order->customer->name ?? '-' }})</td>
                            <td>
                                @if($order->is_paid == 1)
                                    <span class="badge bg-soft-success text-success">Paid</span>
                                @else
                                    <span class="badge bg-soft-danger text-danger">Unpaid</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No orders found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links('pagination::bootstrap-5') }}
    </div>
</div>

<div class="modal fade" id="importOrdersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Historical Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('admin.order.bulk_import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ asset('assets/templates/order.xlsx') }}" download="Order_Template.xlsx">Download Template</a>
                    </div>
                    <p class="text-muted fs-12">
                        For orders that happened before this system was used &mdash; record-keeping only. It does not change
                        product stock. Shop must match an existing shop's slug name; Branch is optional (leave blank for
                        shop-level sales). Payment Mode must match an existing payment mode
                        ({{ \App\Models\Payment::where('is_active',1)->pluck('name')->implode(', ') }}). Paid is Yes or No
                        (defaults to Yes). Bill ID is optional &mdash; one is generated if left blank.
                    </p>
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
