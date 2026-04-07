@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Transfer Report</title>
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
    
                <p class="card-title mb-0">Transfer Report</p>

                <a href="{{route('branch.report', ['company' => request()->route('company')])}}" class="btn btn-sm btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </a>

            </div>
            <div class="card-body pt-2 ">

                <div class="tab-content pt-2 text-muted">
                    <div class="tab-pane show active" id="homeTabsJustified">

                        <form method="get" action="{{route('branch.report.transfer', ['company' => request()->route('company'),'branch' => request('branch')])}}">
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
                            <form method="get" action="{{route('branch.report.transfer.download_excel', ['company' => request()->route('company'),'branch' => request('branch')])}}">
                                <input type="hidden" class="form-control" name="from" value="{{ request('from') }}">
                                <input type="hidden" class="form-control" name="to" value="{{ request('to') }}">
                                <button class="btn btn-success">
                                    <i class="ri-file-excel-2-line"></i> Excel
                                </button>
                            </form>

                            <form method="get" action="{{route('branch.report.transfer.download_pdf', ['company' => request()->route('company'),'branch' => request('branch')])}}">
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
                                        <th>S.No</th>
                                        <th>Transfer Datetime</th>
                                        <th>Type</th>
                                        <th>From Branch</th>
                                        <th>To Branch</th>
                                        <th>Category</th>
                                        <th>Subcategory</th>
                                        <th>Item</th>
                                        <th>Item Code</th>
                                        <th class="text-end">Qty</th>
                                    </tr>
                                </thead> 
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ $datas->firstItem() + $key }}</td>

                                        <td>{{ \Carbon\Carbon::parse($data->transfer_on)->format('d M Y H:i') }}</td>

                                        <td>
                                            
                                            @if($data->to == Auth::user()->id)
                                                <span class="badge bg-success">Stock_In</span>
                                            @else
                                                <span class="badge bg-danger">Stock_Out</span>
                                            @endif
                                            
                                        </td>

                                        <td>{{ $data->transfer_from->user_name ?? '-' }}</td>

                                        <td>{{ $data->transfer_to->user_name ?? '-' }}</td>

                                        <td>{{ $data->category->name ?? '-' }}</td>

                                        <td>{{ $data->sub_category->name ?? '-' }}</td>

                                        <td>{{ $data->product->name ?? '-' }}</td>

                                        <td>{{ $data->product->code ?? '-' }}</td>

                                        <td class="text-end">{{ $data->quantity }}</td>
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
