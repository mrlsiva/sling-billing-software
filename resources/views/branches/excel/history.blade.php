@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Bulk upload history</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">Bulk upload history</p>
					</div>
				</div>

				<div class="">
					
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Upload On</th>
									<th>Module</th>
									<th>Total Record</th>
									<th>Successfull Record</th>
									<th>Error Record</th>
									<th>Uploaded Excel</th>
									<th>Log</th>
								</tr>
							</thead>
							<tbody>
								@forelse($histories as $history)
									<tr>
									    <td>
									        {{ ($histories->currentPage() - 1) * $histories->perPage() + $loop->iteration }}
									    </td>

									    <td>
									        {{ $history->run_on ? \Carbon\Carbon::parse($history->run_on)->format('d M Y, h:i A') : '-' }}
									    </td>

									    <td>
									        {{ $history->module ?? '-' }}
									    </td>

									    <td>
									        {{ $history->total_record ?? 0 }}
									    </td>

									    <td>
									        <span class="badge bg-success">
									            {{ $history->successfull_record ?? 0 }}
									        </span>
									    </td>

									    <td>
									        @if($history->error_record > 0)
									            <span class="badge bg-danger">
									                {{ $history->error_record }}
									            </span>
									        @else
									            <span class="badge bg-secondary">0</span>
									        @endif
									    </td>

									    <td>
									        @if($history->excel)
									            <a href="{{ asset('storage/'.$history->excel) }}"
									               target="_blank"
									               class="btn btn-sm btn-outline-primary">
									                Download
									            </a>
									        @else
									            -
									        @endif
									    </td>

									    <td>
									        @if($history->log)
									            <a href="{{ asset('storage/'.$history->log) }}"
									               target="_blank"
									               class="btn btn-sm btn-outline-secondary">
									                View Log
									            </a>
									        @else
									            -
									        @endif
									    </td>
									</tr>
								@empty
									<tr>
									    <td colspan="8" class="text-center text-muted">
									        No bulk upload history found.
									    </td>
									</tr>
								@endforelse
							</tbody>

						</table>
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					{!! $histories->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>

@endsection