@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Purchase Report</title>
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
    
                <p class="card-title mb-0">Purchase Report</p>

                <a href="{{route('report', ['company' => request()->route('company')])}}" class="btn btn-sm btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </a>

            </div>
            <div class="card-body pt-2 ">

                <div class="tab-content pt-2 text-muted">
                    <div class="tab-pane show active" id="homeTabsJustified">

                        <form method="get" action="{{route('report.purchase', ['company' => request()->route('company')])}}">
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
                            <form method="get" action="{{route('report.purchase.download_excel', ['company' => request()->route('company')])}}">
                                <input type="hidden" name="from" value="{{ request('from') }}">
                                <input type="hidden" name="to" value="{{ request('to') }}">
                                <button class="btn btn-success">
                                    <i class="ri-file-excel-2-line"></i> Excel
                                </button>
                            </form>

                            <form method="get" action="{{route('report.purchase.download_pdf', ['company' => request()->route('company')])}}">
                                <input type="hidden" name="from" value="{{ request('from') }}">
                                <input type="hidden" name="to" value="{{ request('to') }}">
                                <button class="btn btn-success">
                                    <i class="ri-file-pdf-2-line"></i> PDF
                                </button>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Purchase Datetime</th>
                                        <th>Type</th>
                                        <th>Invoice No</th>
                                        <th>Invoice Date</th>
                                        <th>Due Date</th>
                                        <th>Vendor</th>
                                        <th>Category</th>
                                        <th>Sub Category</th>
                                        <th>Item</th>
                                        <th>Item Code</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Base Rate (In ₹)</th>
                                        <th class="text-end">Value (In ₹)</th>
                                        <th class="text-end">GST (In ₹)</th>
                                        <th class="text-end">Base Value (In ₹)</th>
                                        <th class="text-end">NLC (In ₹)</th>
                                    </tr>
                                </thead> 
                                <tbody>
                                    @foreach($datas as $key => $data)
                                        @php
                                            $nlc = $data->quantity > 0 ? ($data->gross_cost / $data->quantity) : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $datas->firstItem() + $key }}</td>

                                            <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d M Y H:i') }}</td>

                                            <td>Purchase Ordered</td>

                                            <td>{{ $data->invoice_no }}</td>

                                            <td>{{ \Carbon\Carbon::parse($data->invoice_date)->format('d M Y') }}</td>

                                            <td>{{ \Carbon\Carbon::parse($data->due_date)->format('d M Y') }}</td>

                                            <td>{{ $data->vendor->name ?? '-' }}</td>

                                            <td>{{ $data->category->name ?? '-' }}</td>

                                            <td>{{ $data->sub_category->name ?? '-' }}</td>

                                            <td>{{ $data->product->name ?? '-' }}</td>

                                            <td>{{ $data->product->code ?? '-' }}</td>

                                            <td class="text-end">{{ $data->quantity }}</td>

                                            <td class="text-end">{{ number_format($data->price_per_unit, 2) }}</td>

                                            <td class="text-end">{{ number_format($data->gross_cost, 2) }}</td>

                                            <td class="text-end">{{ number_format($data->gross_cost - $data->net_cost, 2) }}</td>

                                            <td class="text-end">{{ number_format($data->net_cost, 2) }}</td>

                                            <td class="text-end">{{ number_format($nlc, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if($datas->isEmpty())
                                @include('no-data')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-0">
                {!! $datas->withQueryString()->links('pagination::bootstrap-5') !!}
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
