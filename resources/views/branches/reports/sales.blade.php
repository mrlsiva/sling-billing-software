@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Sales Report</title>
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
    
                <p class="card-title mb-0">Sales Report</p>

                <a href="{{route('branch.report', ['company' => request()->route('company')])}}" class="btn btn-sm btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </a>

            </div>
            <div class="card-body pt-2 ">

                <div class="tab-content pt-2 text-muted">
                    <div class="tab-pane show active" id="homeTabsJustified">

                        <form method="get" action="{{route('branch.report.sales', ['company' => request()->route('company'),'branch' => request('branch')])}}">
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <div class="mb-2">
                                        <label for="from" class="form-label">From Date</label>
                                        <input type="date" id="from" name="from" value="{{ request('from') }}" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="mb-2">
                                        <label for="to" class="form-label">To Date</label>
                                        <input type="date" id="to" name="to" value="{{ request('to') }}" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-2 mt-4">
                                    <button class="btn btn-primary"> Search </button>
                                </div>
                            </div>
                        </form>

                        <div class="d-flex justify-content-end p-3 gap-2">
                            <form method="get" action="{{route('branch.report.sales.download_excel', ['company' => request()->route('company'),'branch' => request('branch')])}}">
                                <input type="hidden" class="form-control" name="from" value="{{ request('from') }}">
                                <input type="hidden" class="form-control" name="to" value="{{ request('to') }}">
                                <button class="btn btn-success">
                                    <i class="ri-file-excel-2-line"></i> Excel
                                </button>
                            </form>

                            <form method="get" action="{{route('branch.report.sales.download_pdf', ['company' => request()->route('company'),'branch' => request('branch')])}}">
                                <input type="hidden" class="form-control" name="from" value="{{ request('from') }}">
                                <input type="hidden" class="form-control" name="to" value="{{ request('to') }}">
                                <button class="btn btn-success">
                                    <i class="ri-file-pdf-2-line"></i> PDF
                                </button>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date Time</th>
                                        <th>Issued By</th>
                                        <th>Sales By</th>
                                        <th>Customer</th>
                                        <th>Mobile</th>
                                        <th>Address</th>
                                        <th>Category</th>
                                        <th>Subcategory</th>
                                        <th>Item</th>
                                        <th>Item Code</th>
                                        <th>Qty</th>
                                        <th>Gross (In ₹)</th>
                                        <th>Tax (In ₹)</th>
                                        <th>Net (In ₹)</th>
                                    </tr>   
                                </thead> 
                                <tbody>
                                    @foreach($orders as $order)
                                        @foreach($order->details as $detail)

                                            @php
                                                // total refunded qty for this product in this order
                                                $refundedQty = 0;

                                                if ($order->is_refunded) {
                                                    foreach ($order->refunds as $refund) {
                                                        foreach ($refund->details as $rDetail) {
                                                            if ($rDetail->product_id == $detail->product_id) {
                                                                $refundedQty += $rDetail->quantity;
                                                            }
                                                        }
                                                    }
                                                }

                                                $finalQty = $detail->quantity - $refundedQty;

                                                // avoid negative (safety)
                                                if ($finalQty < 0) $finalQty = 0;

                                                $gross = $detail->price * $finalQty;
                                                $tax   = $detail->tax_amount * $finalQty;
                                                $net   = ($detail->price - $detail->tax_amount) * $finalQty;
                                            @endphp
                                            
                                            <tr>
                                                <td>{{ $order->bill_id }}</td>
                                                <td>{{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y H:i') }}</td>
                                                <td>User</td>
                                                <td>{{ $order->billedBy->name ?? '' }}</td>
                                                <td>{{ $order->customer->name ?? '' }}</td>
                                                <td>{{ $order->customer->phone ?? '' }}</td>
                                                <td>{{ $order->customer->address ?? '' }}</td>
                                                <td>{{ $detail->product->category->name ?? '' }}</td>
                                                <td>{{ $detail->product->sub_category->name ?? '' }}</td>
                                                <td>{{ $detail->name }}</td>
                                                <td>{{ $detail->product->code }}</td>
                                                <td>
                                                    {{ $finalQty }}
                                                    @if($refundedQty > 0)
                                                        <small class="text-danger">(Refunded: {{ $refundedQty }})</small>
                                                    @endif
                                                </td>

                                                <td>{{ $gross }}</td>
                                                <td>{{ $tax }}</td>
                                                <td>{{ $net }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                            @if($orders->isEmpty())
                                @include('no-data')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-0">
				{!! $orders->withQueryString()->links('pagination::bootstrap-5') !!}
			</div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const fromInput = document.getElementById("from");
            const toInput = document.getElementById("to");

            fromInput.addEventListener("change", function () {
                toInput.min = fromInput.value; // set min date
                if (toInput.value && toInput.value < fromInput.value) {
                    toInput.value = fromInput.value; // auto-correct if invalid
                }
            });

            // If "from" already has a value on load, set "to" min accordingly
            if (fromInput.value) {
                toInput.min = fromInput.value;
            }
        });
    </script>
@endsection
