@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Daily Report</title>
@endsection

@section('style')
<style>
    .stat-card {
        position: relative;
        background: #fff;
        border-radius: 14px;
        border: 1px solid #eef0f4;
        box-shadow: 0 1px 3px rgba(16,24,40,0.04);
        padding: 18px 20px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 16px;
        text-decoration: none;
    }
    a.stat-card:hover {
        box-shadow: 0 4px 14px rgba(16,24,40,0.08);
        transform: translateY(-1px);
        transition: all .15s ease;
    }
    .stat-card-blob {
        position: absolute;
        right: -18px;
        bottom: -18px;
        width: 84px;
        height: 84px;
        border-radius: 50%;
        background: var(--accent-bg, #f1f5f9);
        opacity: .55;
        pointer-events: none;
    }
    .stat-card-icon {
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 14px;
        background: var(--accent-bg, #f1f5f9);
        color: var(--accent, #475467);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        position: relative;
        z-index: 1;
    }
    .stat-card-text {
        position: relative;
        z-index: 1;
        min-width: 0;
    }
    .stat-card-label-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .stat-card-label {
        font-size: .9rem;
        color: #475467;
        font-weight: 500;
    }
    .stat-card-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--accent, #101828);
        margin-top: 2px;
    }
    .stat-card-select {
        border: 1px solid #d0d5dd;
        border-radius: 8px;
        font-size: .8rem;
        padding: 2px 6px;
        background: #fff;
        color: #344054;
        max-width: 90px;
    }
</style>
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
    
                <p class="card-title mb-0">Daily Report</p>

                <a href="{{route('branch.report', ['company' => request()->route('company')])}}" class="btn btn-sm btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </a>

            </div>
            <div class="card-body pt-2 ">



            	<form method="get" action="{{route('branch.report.daily', ['company' => request()->route('company'),'branch' => request('branch')])}}">
            		<div class="row mb-2">
            			<div class="col-md-11">
            				<div class="mb-2">
            					<label for="date" class="form-label">Date</label>
            					<input type="date" id="date" name="date" value="{{ request('date', now()->format('Y-m-d')) }}" class="form-control">
            				</div>
            			</div>

            			<div class="col-md-1 mt-4">
            				<button class="btn btn-primary"> Search </button>
            			</div>
            		</div>
            	</form>

            	<div class="d-flex justify-content-end p-3 gap-2">
            		<form method="get" action="{{route('branch.report.daily.download_excel', ['company' => request()->route('company'),'branch' => request('branch')])}}">
            			<input type="hidden" name="date" value="{{ request('date') }}">
            			<button class="btn btn-success">
            				<i class="ri-file-excel-2-line"></i> Excel
            			</button>
            		</form>

            		<form method="get" action="{{route('branch.report.daily.download_pdf', ['company' => request()->route('company'),'branch' => request('branch')])}}">
            			<input type="hidden" name="date" value="{{ request('date') }}">
            			<button class="btn btn-success">
            				<i class="ri-file-pdf-2-line"></i> PDF
            			</button>
            		</form>
            	</div>

            	<div class="row mb-3 g-3 align-items-stretch">

            		<div class="col-md-3">
                        <div class="stat-card" style="--accent:#7c3aed; --accent-bg:#ede9fe;">
                            <div class="stat-card-blob"></div>
                            <span class="stat-card-icon"><i class="ri-wallet-3-line"></i></span>
                            <div class="stat-card-text">
                                <div class="stat-card-label">Opening Balance</div>
                                @if($cash_summary['seeded'])
                                <div class="stat-card-value">₹ {{ number_format($cash_summary['opening_balance'], 2) }}</div>
                                @else
                                <div class="stat-card-value" style="font-size:1rem;">Not Set</div>
                                @endif
                            </div>
                        </div>
                    </div>

            		<div class="col-md-3">
                        <div class="stat-card" style="--accent:#16a34a; --accent-bg:#dcfce7;">
                            <div class="stat-card-blob"></div>
                            <span class="stat-card-icon"><i class="ri-line-chart-line"></i></span>
                            <div class="stat-card-text">
                                <div class="stat-card-label-row">
                                    <span class="stat-card-label">Today Sales</span>
                                    <i class="ri-eye-fill" style="cursor:pointer; color:var(--accent);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#salesModal"></i>
                                </div>
                                <div class="stat-card-value">₹ {{ number_format($totalSales,2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card" style="--accent:#0d9488; --accent-bg:#ccfbf1;">
                            <div class="stat-card-blob"></div>
                            <span class="stat-card-icon"><i class="ri-calendar-2-line"></i></span>
                            <div class="stat-card-text">
                                <div class="stat-card-label">Monthly Sales</div>
                                <div class="stat-card-value">₹ {{ number_format($monthly_sales, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card" style="--accent:#16a34a; --accent-bg:#dcfce7;">
                            <div class="stat-card-blob"></div>
                            <span class="stat-card-icon"><i class="ri-download-2-line"></i></span>
                            <div class="stat-card-text">
                                <div class="stat-card-label">Product In</div>
                                <div class="stat-card-value">₹ {{ number_format($productInAmount, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card" style="--accent:#dc2626; --accent-bg:#fee2e2;">
                            <div class="stat-card-blob"></div>
                            <span class="stat-card-icon"><i class="ri-upload-2-line"></i></span>
                            <div class="stat-card-text">
                                <div class="stat-card-label">Product Out</div>
                                <div class="stat-card-value">₹ {{ number_format($productOutAmount, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card" style="--accent:#0d9488; --accent-bg:#ccfbf1;">
                            <div class="stat-card-blob"></div>
                            <span class="stat-card-icon"><i class="ri-bank-card-line"></i></span>
                            <div class="stat-card-text">
                                <div class="stat-card-label-row">
                                    <span class="stat-card-label">Today Sales by Mode</span>
                                    <select id="salesModeSelect" class="stat-card-select">
                                        <option value="all" selected>All</option>
                                        @foreach($paymentModes as $mode)
                                            <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="stat-card-value" id="salesModeAmount">₹ 0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <a href="{{ route('branch.credit', ['company' => request()->route('company'),'date' =>  request('date', now()->format('Y-m-d')) ]) }}" class="stat-card" style="--accent:#ea580c; --accent-bg:#ffedd5;">
                            <div class="stat-card-blob"></div>
                            <span class="stat-card-icon"><i class="ri-team-line"></i></span>
                            <div class="stat-card-text">
                                <div class="stat-card-label">Credit Amount</div>
                                <div class="stat-card-value">₹ {{ number_format($credit_amount, 2) }}</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card" style="--accent:#d97706; --accent-bg:#fef3c7;">
                            <div class="stat-card-blob"></div>
                            <span class="stat-card-icon"><i class="ri-percent-line"></i></span>
                            <div class="stat-card-text">
                                <div class="stat-card-label">Discount Amount</div>
                                <div class="stat-card-value">₹ {{ number_format($discount_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card" style="--accent:#0d9488; --accent-bg:#ccfbf1;">
                            <div class="stat-card-blob"></div>
                            <span class="stat-card-icon"><i class="ri-exchange-line"></i></span>
                            <div class="stat-card-text">
                                <div class="stat-card-label-row">
                                    <span class="stat-card-label">OS Recd by Mode</span>
                                    <select id="osRecdModeSelect" class="stat-card-select">
                                        <option value="all" selected>All</option>
                                        @foreach($paymentModes as $mode)
                                            <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="stat-card-value" id="osRecdModeAmount">₹ 0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <a href="{{ route('branch.expense.index', ['company' => request()->route('company'),'date' => request('date', now()->format('Y-m-d')) ]) }}" class="stat-card" style="--accent:#dc2626; --accent-bg:#fee2e2;">
                            <div class="stat-card-blob"></div>
                            <span class="stat-card-icon"><i class="ri-bank-card-2-line"></i></span>
                            <div class="stat-card-text">
                                <div class="stat-card-label">Expenses</div>
                                <div class="stat-card-value">₹ {{ number_format($expense_amount, 2) }}</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-3">
                        <a href="{{ route('branch.cash_handover.index', ['company' => request()->route('company'),'date' => request('date', now()->format('Y-m-d')) ]) }}" class="stat-card" style="--accent:#e11d48; --accent-bg:#ffe4e6;">
                            <div class="stat-card-blob"></div>
                            <span class="stat-card-icon"><i class="ri-send-plane-line"></i></span>
                            <div class="stat-card-text">
                                <div class="stat-card-label">Cash Given to HO</div>
                                <div class="stat-card-value">₹ {{ number_format($cash_summary['handover_today'], 2) }}</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card" style="--accent:#d97706; --accent-bg:#fef3c7;">
                            <div class="stat-card-blob"></div>
                            <span class="stat-card-icon"><i class="ri-wallet-3-line"></i></span>
                            <div class="stat-card-text">
                                <div class="stat-card-label">Cash Balance</div>
                                @if($cash_summary['seeded'])
                                <div class="stat-card-value">₹ {{ number_format($cash_summary['closing_balance'], 2) }}</div>
                                @else
                                <div class="stat-card-value" style="font-size:1rem;">Not Set</div>
                                @endif
                            </div>
                        </div>
                    </div>


            	</div>

                @if(!$orders->isEmpty())
            	<h5 class="mt-4">Order Report</h5>

            	<div class="table-responsive">
            		<table class="table table-bordered table-hover">
            			<thead class="table-light">
            				<tr>
            					<th>S.No</th>
            					<th>Branch/ HO</th>
            					<th>Bill ID</th>
            					<th>Amount (In ₹)</th>
            					<th>Billed On</th>
            					<th>Billed By</th>
                                <th>Mode Of Payment</th>
            					<th>Customer</th>
            				</tr>
            			</thead> 
            			<tbody>
            				@foreach($orders as $order)
            				<tr>
            					<td>
            						{{  $loop->iteration }}
            					</td>
            					<td>
            						@if($order->branch_id != null)
            						{{$order->branch->user_name}}
            						@else
            						{{$order->shop->user_name}}
            						@endif
            					</td>
            					<td>
            						{{$order->bill_id}}
            					</td>
            					<td>
            						{{ $order->bill_amount - ($order->is_refunded ? ($order->total_refund ?? 0) : 0) }}
            					</td>
            					<td>
            						{{ \Carbon\Carbon::parse($order->billed_on)->format('d-m-Y') }}
            					</td>
            					<td>
            						{{ $order->billedBy->name }}
            					</td>
                                <td>
                                    @foreach($order->payments as $payment)
                                        <span class="badge bg-primary">
                                            {{ $payment->payment->name ?? 'N/A' }} 
                                            ₹ {{ number_format($payment->amount, 2) }}
                                        </span><br>
                                    @endforeach
                                    @if($order->order_discount != 0)
                                        <span class="badge bg-primary">
                                            Discount 
                                            ₹ {{ number_format($order->order_discount, 2) }}
                                        </span><br>
                                    @endif
                                    @if($order->is_refunded)
                                        <span class="badge bg-primary">
                                            Refund 
                                            ₹ {{ number_format($order->total_refund, 2) }}
                                            <br>
                                            {{ \Carbon\Carbon::parse($order->refunds->last()->refund_on)->format('d-m-Y') }}
                                        </span>
                                    @endif
                                </td>
            					<td>
            						{{ $order->customer->phone }} ({{ $order->customer->name }})
            					</td>
            				</tr>
            				@endforeach
            			</tbody>
            		</table>
            		@if($orders->isEmpty())
            		  @include('no-data')
            		@endif
            	</div>
            	{{ $orders->links('pagination::bootstrap-5') }}
                @endif

                @if(!$expenses_list->isEmpty())
                <h5 class="mt-4">Expenses</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>S.No</th>
                                <th>Title</th>
                                <th>Amount (In ₹)</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenses_list as $expense)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $expense->title }}</td>
                                <td>{{ number_format($expense->amount, 2) }}</td>
                                <td>{{ $expense->created_at->format('h:i A') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">Total</td>
                                <td>{{ number_format($expense_amount, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                {{ $expenses_list->links('pagination::bootstrap-5') }}
                @endif

                @if(!$productIn->isEmpty())
                <h5 class="mt-4">Product In</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>S.No</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Amount (In ₹)</th>
                            </tr>
                        </thead> 
                        <tbody>
                            @foreach($productIn as $index => $product_in)
                                @php
                                    $price = $product_in->product->price ?? 0;
                                    $amount = $price * $product_in->quantity;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $product_in->product->name ?? '-' }}</td>
                                    <td>{{ $product_in->quantity }}</td>
                                    <td>₹ {{ number_format($amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($productIn->isEmpty())
                        @include('no-data')
                    @endif
                </div>
                @endif

                @if(!$productOut->isEmpty())
                <h5 class="mt-4">Product Out</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>S.No</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Amount (In ₹)</th>
                            </tr>
                        </thead> 
                        <tbody>
                            @foreach($productOut as $index => $product_out)
                                @php
                                    $price = $product_out->product->price ?? 0;
                                    $amount = $price * $product_out->quantity;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $product_out->product->name ?? '-' }}</td>
                                    <td>{{ $product_out->quantity }}</td>
                                    <td>₹ {{ number_format($amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($productOut->isEmpty())
                        @include('no-data')
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="salesModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Payment Mode Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Mode</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentSummary as $index => $summary)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $summary->payment->name ?? 'N/A' }}</td>
                                <td>₹ {{ number_format($summary->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No Data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    const salesByMode = {
        @foreach($paymentModes as $mode)
        {{ $mode->id }}: {{ $paymentSummary->firstWhere('payment_id', $mode->id)?->total_amount ?? 0 }},
        @endforeach
    };

    const osRecdByMode = {
        @foreach($paymentModes as $mode)
        {{ $mode->id }}: {{ $os_recd_summary->firstWhere('payment_id', $mode->id)?->amount ?? 0 }},
        @endforeach
    };

    function formatRupees(value) {
        return '₹ ' + Number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function refreshModeCard(selectId, amountId, dataMap) {
        const select = document.getElementById(selectId);
        const amountEl = document.getElementById(amountId);

        function render() {
            const val = select.value === 'all'
                ? Object.values(dataMap).reduce((sum, v) => sum + v, 0)
                : (dataMap[select.value] ?? 0);
            amountEl.textContent = formatRupees(val);
        }

        select.addEventListener('change', render);
        render();
    }

    document.addEventListener('DOMContentLoaded', function () {
        refreshModeCard('salesModeSelect', 'salesModeAmount', salesByMode);
        refreshModeCard('osRecdModeSelect', 'osRecdModeAmount', osRecdByMode);
    });
</script>
@endsection
