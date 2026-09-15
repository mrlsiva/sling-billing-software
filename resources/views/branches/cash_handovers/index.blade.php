@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Cash to HO</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">Cash to HO</p>
					</div>
				</div>

				@if ($errors->any())
		            <div class="alert alert-danger m-3">
		                <strong>Whoops!</strong> There were some problems with your input.<br><br>
		                <ul>
		                    @foreach ($errors->all() as $error)
		                        <li>{{ $error }}</li>
		                    @endforeach
		                </ul>
		            </div>
		        @endif

		        @if(session('toast_success'))
		        <div class="alert alert-success m-3">{{ session('toast_success') }}</div>
		        @endif

				<div class="p-3">

					@if(!$seed)
					<div class="alert alert-warning">
						<b>Opening cash balance not set yet.</b> Set the cash currently in hand at this branch once, and the Cash Balance will be tracked automatically from that date onward.
					</div>

					<form method="post" action="{{route('branch.cash_handover.seed', ['company' => request()->route('company')])}}" class="row g-2 align-items-end mb-4">
						@csrf
						<div class="col-md-4">
							<label class="form-label text-muted">Opening Balance (₹)</label>
							<input type="number" step="0.01" min="0" name="opening_balance" class="form-control" required>
						</div>
						<div class="col-md-4">
							<label class="form-label text-muted">As of Date</label>
							<input type="date" name="effective_date" class="form-control" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" required>
						</div>
						<div class="col-md-3">
							<button type="submit" class="btn btn-primary w-100">Set Opening Balance</button>
						</div>
					</form>
					@else

					<div class="row mb-4">
						<div class="col-md-3">
							<div class="card border shadow-sm">
								<div class="card-body text-center">
									<h6 class="text-muted">Opening Balance</h6>
									<h4 class="text-primary">₹ {{ number_format($summary['opening_balance'], 2) }}</h4>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card border shadow-sm">
								<div class="card-body text-center">
									<h6 class="text-muted">Cash In (Sales + Collections)</h6>
									<h4 class="text-success">₹ {{ number_format($summary['cash_in_today'], 2) }}</h4>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card border shadow-sm">
								<div class="card-body text-center">
									<h6 class="text-muted">Expenses + Given to HO</h6>
									<h4 class="text-danger">₹ {{ number_format($summary['expenses_today'] + $summary['handover_today'], 2) }}</h4>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card border shadow-sm">
								<div class="card-body text-center">
									<h6 class="text-muted">Cash Balance (Closing)</h6>
									<h4 class="text-warning">₹ {{ number_format($summary['closing_balance'], 2) }}</h4>
								</div>
							</div>
						</div>
					</div>

					@if($summary['before_tracking'])
					<div class="alert alert-info">Cash tracking for this branch starts from {{ \Carbon\Carbon::parse($summary['seed_date'])->format('d M Y') }}.</div>
					@else
					<p class="text-muted small mb-4">
						Opening balance set on {{ \Carbon\Carbon::parse($seed->effective_date)->format('d M Y') }}: ₹ {{ number_format($seed->opening_balance, 2) }}
						&nbsp;|&nbsp; Today's cash sales: ₹ {{ number_format($summary['pos_cash_today'], 2) }}
						&nbsp;|&nbsp; Outstanding collected in cash: ₹ {{ number_format($summary['os_cash_today'], 2) }}
					</p>
					@endif

					<form method="post" action="{{route('branch.cash_handover.store', ['company' => request()->route('company')])}}" class="row g-2 align-items-end mb-4">
						@csrf
						<div class="col-md-3">
							<label class="form-label text-muted">Amount Given to HO</label>
							<input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="Amount" required>
						</div>
						<div class="col-md-5">
							<label class="form-label text-muted">Remarks</label>
							<input type="text" name="remarks" class="form-control" placeholder="Optional" maxlength="150">
						</div>
						<div class="col-md-2">
							<button type="submit" class="btn btn-primary w-100"><i class="ri-add-line align-middle"></i> Add</button>
						</div>
					</form>
					@endif

					<form method="get" action="{{route('branch.cash_handover.index', ['company' => request()->route('company')])}}" class="row g-2 mb-3">
						<div class="col-md-3">
							<label class="form-label text-muted">Date</label>
							<input type="date" name="date" class="form-control" value="{{ $date }}" max="{{ date('Y-m-d') }}" onchange="this.form.submit()">
						</div>
					</form>

					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Amount</th>
									<th>Remarks</th>
									<th>Time</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								@foreach($handovers as $handover)
								<tr>
									<td>{{ $loop->iteration }}</td>
									<td>{{ number_format($handover->amount, 2) }}</td>
									<td>{{ $handover->remarks ?? '-' }}</td>
									<td>{{ $handover->created_at->format('h:i A') }}</td>
									<td>
										<form method="post" action="{{ route('branch.cash_handover.destroy', ['company' => request()->route('company'), 'id' => $handover->id]) }}" onsubmit="return confirm('Delete this entry?');">
											@csrf
											<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
										</form>
									</td>
								</tr>
								@endforeach
							</tbody>
							@if($handovers->isNotEmpty())
							<tfoot>
								<tr class="fw-bold">
									<td class="text-end">Total</td>
									<td>{{ number_format($total_amount, 2) }}</td>
									<td colspan="3"></td>
								</tr>
							</tfoot>
							@endif
						</table>
						@if($handovers->isEmpty())
                        	@include('no-data')
                        @endif
					</div>

				</div>
				<div class="card-footer border-0">
					{!! $handovers->links('pagination::bootstrap-5') !!}
				</div>
			</div>
		</div>
	</div>
@endsection
