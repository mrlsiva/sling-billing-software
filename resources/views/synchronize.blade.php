@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Synchronize Stock</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">Synchronize Stock</p>
					</div>

				</div>

				@if ($errors->any())
		            <div class="alert alert-danger">
		                <strong>Whoops!</strong> There were some problems with your input.<br><br>
		                <ul>
		                    @foreach ($errors->all() as $error)
		                        <li>{{ $error }}</li>
		                    @endforeach
		                </ul>
		            </div>
		        @endif

				<form method="GET" action="{{ route('synchronize_stock', ['company' => request()->route('company')]) }}">
				    <div class="row mb-2 p-3">
				        <div class="col-md-11">
				            <select class="form-control" name="filter" id="filter">
				                <option value="">Select</option>
				                <option value="transfer" {{ request('filter') == 'transfer' ? 'selected' : '' }}>
				                    Transferred
				                </option>
				                <option value="received" {{ request('filter', 'received') == 'received' ? 'selected' : '' }}>
				                    Received
				                </option>
				            </select>
				        </div>

				        <div class="col-md-1">
				            <button class="btn btn-primary">Search</button>
				        </div>
				    </div>
				</form>

				<div class="">
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>From</th>
									<th>To</th>
									<th>Initiated On</th>
									<th>Initiated By</th>
									<th>Updated On</th>
									<th>Updated By</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								@foreach($stocks as $stock)
								    <tr>
								        <td>
								            {{ ($stocks->currentPage() - 1) * $stocks->perPage() + $loop->iteration }}
								        </td>

								        <td>{{ optional($stock->From)->user_name ?? '-' }}</td>

								        <td>{{ optional($stock->To)->user_name ?? '-' }}</td>

								        <td>
								            {{ $stock->initiated_on ? \Carbon\Carbon::parse($stock->initiated_on)->format('d M Y') : '-' }}
								        </td>

								        <td>{{ optional($stock->initiatedBy)->user_name ?? '-' }}</td>

								        <td>
								            {{ $stock->updated_on ? \Carbon\Carbon::parse($stock->updated_on)->format('d M Y') : '-' }}
								        </td>

								        <td>{{ optional($stock->updatedBy)->user_name ?? '-' }}</td>

								        <td>
								            @if($stock->status == 0)
								                <span class="badge bg-warning">Pending</span>
								            @elseif($stock->status == 1)
								                <span class="badge bg-success">Approved</span>
								            @elseif($stock->status == 2)
								                <span class="badge bg-danger">Rejected</span>
								            @else
								                <span class="badge bg-secondary">Unknown</span>
								            @endif
								        </td>

								        <td>
								        	<a href="{{ route('synchronize_stock.view', ['company' => request()->route('company'), 'id' => $stock->id ]) }}" class="link-dark" target="_blank"><i class="ri-eye-fill align-middle fs-20" title="View"></i></a>

								        	<a href="" class="link-dark" target="_blank"><i class="ri-check-double-line align-middle fs-20" title="Accept"></i></a>

								        	<a href="" class="link-dark" target="_blank"><i class="ri-close-line align-middle fs-20" title="Reject"></i></a>
								        </td>
								    </tr>
								@endforeach
							</tbody>
						</table>
						@if($stocks->isEmpty())
                        	@include('no-data')
                        @endif
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					{!! $stocks->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>

@endsection

@section('script')

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        let searchInput = document.getElementById("searchInput");
        let clearFilter = document.getElementById("clearFilter");

        function toggleClear() {
            if (searchInput.value.trim() !== "") {
                clearFilter.style.display = "inline-flex";
            } else {
                clearFilter.style.display = "none";
            }
        }

        // Run on load (for prefilled request values)
        toggleClear();

        // Run on typing
        searchInput.addEventListener("input", toggleClear);
    });
</script>

@endsection