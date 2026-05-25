@extends('layouts.master') 
@section('title') 
    <title>{{ config('app.name')}} | {{$product->name}}</title> 
@endsection
@section('body') 

    <style>
        .table th,
        .table td{
            font-size:13px;
            vertical-align:middle;
        }

        .table thead th{
            white-space:nowrap;
        }

        .table tbody tr:hover{
            background:#f8fbff;
        }

        .badge{
            font-size:11px;
        }
    </style>

    <div class="row"> 
        <div class="col-xl-12 col-md-12"> 
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <div class="d-flex align-items-center gap-2">

                            <a href="{{ url()->previous() }}">
                                <i class="bx bx-arrow-back me-1"></i>
                            </a>

                            <div>
                                <h5 class="mb-0 fw-bold">
                                    Stock Ledger :
                                    {{ $product->name }}
                                </h5>

                                <small class="text-muted">
                                    {{ request('from') }} to {{ request('to') }}
                                </small>
                            </div>

                        </div>

                        <div>
                            <span class="badge bg-primary p-2">
                                Closing Stock :
                                {{ $totals['closing_qty'] }}
                            </span>
                        </div>

                    </div>
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr class="text-center">

                                    <th rowspan="2" width="110">Date</th>

                                    <th rowspan="2">Particulars</th>

                                    <th rowspan="2" width="120">Vch Type</th>

                                    <th rowspan="2" width="120">Vch No</th>

                                    <th colspan="2" class="bg-success text-white">
                                        Inwards
                                    </th>

                                    <th colspan="2" class="bg-danger text-white">
                                        Outwards
                                    </th>

                                    <th colspan="2" class="bg-primary text-white">
                                        Closing
                                    </th>

                                </tr>

                                <tr class="text-center">

                                    <th width="90">Qty</th>
                                    <th width="120">Value</th>

                                    <th width="90">Qty</th>
                                    <th width="120">Value</th>

                                    <th width="90">Qty</th>
                                    <th width="120">Value</th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse($ledger as $row)

                                    <tr>

                                        <td>
                                            {{ \Carbon\Carbon::parse($row['date'])->format('d-M-y') }}
                                        </td>

                                        <td class="fw-semibold">
                                            {{ $row['particulars'] }}
                                        </td>

                                        <td>
                                            @if($row['voucher_type'] == 'Purchase')
                                                <span class="badge bg-success">
                                                    Purchase
                                                </span>

                                            @elseif($row['voucher_type'] == 'Purchase Refund')
                                                <span class="badge bg-warning text-dark">
                                                    Purchase Refund
                                                </span>

                                            @elseif($row['voucher_type'] == 'Sales')
                                                <span class="badge bg-danger">
                                                    Sales
                                                </span>

                                            @elseif($row['voucher_type'] == 'Sales Refund')
                                                <span class="badge bg-info text-dark">
                                                    Sales Refund
                                                </span>

                                            @else
                                                <span class="badge bg-secondary">
                                                    {{ $row['voucher_type'] }}
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $row['voucher_no'] }}
                                        </td>

                                        {{-- INWARD --}}

                                        <td class="text-end">

                                            @if($row['in_qty'] > 0)
                                                {{ number_format($row['in_qty']) }}
                                            @endif

                                        </td>

                                        <td class="text-end">

                                            @if($row['in_value'] > 0)
                                                ₹ {{ number_format($row['in_value'], 2) }}
                                            @endif

                                        </td>

                                        {{-- OUTWARD --}}

                                        <td class="text-end">

                                            @if($row['out_qty'] > 0)
                                                {{ number_format($row['out_qty']) }}
                                            @endif

                                        </td>

                                        <td class="text-end text-danger">

                                            @if($row['out_value'] > 0)
                                                ₹ {{ number_format($row['out_value'], 2) }}
                                            @endif

                                        </td>

                                        {{-- CLOSING --}}

                                        <td class="text-end fw-bold">

                                            {{ number_format($row['closing_qty']) }}

                                        </td>

                                        <td class="text-end fw-bold text-primary">

                                            ₹ {{ number_format($row['closing_value'], 2) }}

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            No Ledger Found
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                            <tfoot class="table-light fw-bold">

                                <tr>

                                    <td colspan="4" class="text-end">
                                        Totals :
                                    </td>

                                    <td class="text-end">
                                        {{ number_format($totals['total_in_qty']) }}
                                    </td>

                                    <td class="text-end text-success">
                                        ₹ {{ number_format($totals['total_in_value'], 2) }}
                                    </td>

                                    <td class="text-end">
                                        {{ number_format($totals['total_out_qty']) }}
                                    </td>

                                    <td class="text-end text-danger">
                                        ₹ {{ number_format($totals['total_out_value'], 2) }}
                                    </td>

                                    <td class="text-end">
                                        {{ number_format($totals['closing_qty']) }}
                                    </td>

                                    <td class="text-end text-primary">
                                        ₹ {{ number_format($totals['closing_value'], 2) }}
                                    </td>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                </div>
            </div> 
        </div> 
    </div> 
</div> 
@endsection