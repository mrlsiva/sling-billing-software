@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Expenses</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">Daily Expenses</p>
					</div>
					<div>
						<a href="{{route('branch.cash_handover.index', ['company' => request()->route('company')])}}" class="btn btn-outline-primary btn-sm fw-semibold"><i class="ri-safe-2-line"></i> Cash to HO</a>
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

					<form method="post" action="{{route('branch.expense.store', ['company' => request()->route('company')])}}" class="row g-2 align-items-end mb-4">
						@csrf
						<div class="col-md-5">
							<label class="form-label text-muted">Title</label>
							<input type="text" name="title" list="expenseTitles" class="form-control" placeholder="e.g. Tea, Flowers, Food Exp" maxlength="100" required autocomplete="off">
							<datalist id="expenseTitles">
								@foreach($titles as $title)
									<option value="{{ $title }}">
								@endforeach
							</datalist>
						</div>

						<div class="col-md-3">
							<label class="form-label text-muted">Amount</label>
							<input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="Amount" required>
						</div>

						<div class="col-md-2">
							<button type="submit" class="btn btn-primary w-100"><i class="ri-add-line align-middle"></i> Add Expense</button>
						</div>
					</form>

					<form method="get" action="{{route('branch.expense.index', ['company' => request()->route('company')])}}" class="row g-2 mb-3">
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
									<th>Title</th>
									<th>Amount</th>
									<th>Time</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								@foreach($expenses as $expense)
								<tr>
									<td>{{ $loop->iteration }}</td>
									<td>{{ $expense->title }}</td>
									<td>{{ number_format($expense->amount, 2) }}</td>
									<td>{{ $expense->created_at->format('h:i A') }}</td>
									<td>
										<form method="post" action="{{ route('branch.expense.destroy', ['company' => request()->route('company'), 'id' => $expense->id]) }}" onsubmit="return confirm('Delete this expense?');">
											@csrf
											<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
										</form>
									</td>
								</tr>
								@endforeach
							</tbody>
							@if($expenses->isNotEmpty())
							<tfoot>
								<tr class="fw-bold">
									<td colspan="2" class="text-end">Total</td>
									<td>{{ number_format($total_amount, 2) }}</td>
									<td colspan="2"></td>
								</tr>
							</tfoot>
							@endif
						</table>
						@if($expenses->isEmpty())
                        	@include('no-data')
                        @endif
					</div>

				</div>
				<div class="card-footer border-0">
					{!! $expenses->links('pagination::bootstrap-5') !!}
				</div>
			</div>
		</div>
	</div>
@endsection
