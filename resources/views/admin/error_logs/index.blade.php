@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Error Log</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">Error Logs</p>
					</div>
				</div>

				<div class="">
					
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>User</th>
									<th>Error</th>
									<th>Code</th>
									<th>URL</th>
									<th>Method</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
									@foreach($error_reports as $error_report)
									<tr>
										<td>
											{{ ($error_reports->currentPage() - 1) * $error_reports->perPage() + $loop->iteration }}
										</td>
										<td>
											@if($error_report->user_id != null)
												{{$error_report->user->name}}
											@else
												-
											@endif
										</td>
										<td>{{$error_report->error}}</td>
										<td>{{$error_report->code}}</td>
										<td>{{$error_report->url}}</td>
										<td>{{$error_report->method}}</td>
										<td>{{ $error_report->created_at->toFormattedDateString() }}</td>
									</tr>
									@endforeach
							</tbody>
						</table>
						@if($error_reports->isEmpty())
							@include('no-data')
                        @endif
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					{!! $error_reports->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>

@endsection