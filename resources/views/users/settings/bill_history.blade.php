@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Bill Edit History</title>
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <p class="card-title mb-0">Edit History &mdash; Bill #{{ $order->bill_id }}</p>
                    <small class="text-muted">Billed on {{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y h:i A') }}</small>
                </div>
                <a href="{{route('setting.order.bill.index', ['company' => request()->route('company')])}}" class="btn btn-soft-secondary btn-sm">
                    <i class="ri-arrow-left-line align-middle me-1"></i> Back
                </a>
            </div>
            <div class="card-body pt-3">

                @if(empty($timeline))
                    @include('no-data')
                @else
                    @foreach($timeline as $entry)
                    <div class="card border {{ $entry['is_latest'] ? 'border-primary' : '' }} mb-3">
                        <div class="card-header bg-light-subtle d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <i class="ri-user-line align-middle me-1"></i>
                                <strong>{{ $entry['edited_by'] }}</strong>
                                <span class="text-muted">edited this bill</span>
                                @if($entry['is_latest'])
                                    <span class="badge bg-soft-primary text-primary ms-1">Latest Edit</span>
                                @endif
                            </div>
                            <div class="text-muted">
                                <i class="ri-time-line align-middle me-1"></i>
                                {{ \Carbon\Carbon::parse($entry['edited_on'])->format('d M Y, h:i A') }}
                            </div>
                        </div>
                        <div class="card-body">

                            @if($entry['remarks'])
                                <p class="mb-3"><i class="ri-chat-quote-line align-middle me-1"></i><em>"{{ $entry['remarks'] }}"</em></p>
                            @endif

                            @if(empty($entry['order_changes']) && empty($entry['item_changes']) && empty($entry['payment_changes']))
                                <p class="text-muted mb-0">No field-level changes detected for this edit.</p>
                            @endif

                            @if(!empty($entry['order_changes']))
                                <h6 class="text-uppercase fs-12 text-muted mb-2">Bill Details</h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="bg-light-subtle">
                                            <tr>
                                                <th>Field</th>
                                                <th>Previous</th>
                                                <th>New</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($entry['order_changes'] as $change)
                                            <tr>
                                                <td>{{ $change['label'] }}</td>
                                                <td class="text-danger">{{ $change['old'] ?? '-' }}</td>
                                                <td class="text-success">{{ $change['new'] ?? '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @if(!empty($entry['item_changes']))
                                <h6 class="text-uppercase fs-12 text-muted mb-2">Product Line Items</h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="bg-light-subtle">
                                            <tr>
                                                <th>Status</th>
                                                <th>Product</th>
                                                <th>Previous Qty</th>
                                                <th>New Qty</th>
                                                <th>Previous Price</th>
                                                <th>New Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($entry['item_changes'] as $item)
                                            <tr>
                                                <td>
                                                    @if($item['status'] === 'added')
                                                        <span class="badge bg-soft-success text-success">Added</span>
                                                    @elseif($item['status'] === 'removed')
                                                        <span class="badge bg-soft-danger text-danger">Removed</span>
                                                    @else
                                                        <span class="badge bg-soft-warning text-warning">Changed</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item['name'] }}</td>
                                                <td class="text-danger">{{ $item['old_qty'] ?? '-' }}</td>
                                                <td class="text-success">{{ $item['new_qty'] ?? '-' }}</td>
                                                <td class="text-danger">{{ $item['old_price'] !== null ? number_format($item['old_price'],2) : '-' }}</td>
                                                <td class="text-success">{{ $item['new_price'] !== null ? number_format($item['new_price'],2) : '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @if(!empty($entry['payment_changes']))
                                <h6 class="text-uppercase fs-12 text-muted mb-2">Payments</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="bg-light-subtle">
                                            <tr>
                                                <th>Status</th>
                                                <th>Payment Mode</th>
                                                <th>Previous Amount</th>
                                                <th>New Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($entry['payment_changes'] as $payment)
                                            <tr>
                                                <td>
                                                    @if($payment['status'] === 'added')
                                                        <span class="badge bg-soft-success text-success">Added</span>
                                                    @elseif($payment['status'] === 'removed')
                                                        <span class="badge bg-soft-danger text-danger">Removed</span>
                                                    @else
                                                        <span class="badge bg-soft-warning text-warning">Changed</span>
                                                    @endif
                                                </td>
                                                <td>{{ $payment['name'] }}</td>
                                                <td class="text-danger">{{ $payment['old_amount'] !== null ? number_format($payment['old_amount'],2) : '-' }}</td>
                                                <td class="text-success">{{ $payment['new_amount'] !== null ? number_format($payment['new_amount'],2) : '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                        </div>
                    </div>
                    @endforeach
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
