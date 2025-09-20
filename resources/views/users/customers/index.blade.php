@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Customers</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Customer</p>
					</div>
				</div>

				<form method="get" action="{{route('customer.index', ['company' => request()->route('company')])}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Customer Name/ Customer Phone" name="customer" value="{{ request('customer') }}">
                                <span class="input-group-text"><a href="{{route('customer.index', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-primary"> Search </button>
                        </div>
                    </div>
                </form>

				<div class="">

					<div class="d-flex justify-content-end p-3">
						<form method="get" action="{{route('customer.download', ['company' => request()->route('company')])}}">
							<input type="hidden" class="form-control" name="customer" value="{{ request('customer') }}">
							<button class="btn btn-success"> Download </button>
						</form>
					</div>
					
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Name</th>
									<th>Phone</th>
									<th>Alternate Phone</th>
									<th>Address</th>
									<th>Gender</th>
									<th>DOB</th>
									<th>Order History</th>
								</tr>
							</thead>
							<tbody>
									@foreach($users as $user)
									<tr>
										<td>
											{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
										</td>
										<td>{{$user->name}}</td>
										<td>{{$user->phone}}</td>
										<td>
											@if($user->alt_phone != null)
												{{$user->alt_phone}}
											@else
												-
											@endif
										</td>
										<td>
											@if($user->address != null)
												{{$user->address}}
											@else
												-
											@endif
											<br>
											@if($user->pincode != null)
												{{$user->pincode}}
											@endif
										</td>
										<td>
											@if($user->gender_id != null)
												{{$user->gender->name}}
											@else
												-
											@endif
										</td>
										<td>
											@if($user->dob != null)
												{{ \Carbon\Carbon::parse($user->dob)->format('d M Y') }}
											@else
												-
											@endif
										</td>
										<td>
											<div class="d-flex gap-3">
												<a href="{{ route('customer.order', ['company' => request()->route('company'),'id' => $user->id ]) }}" class="text-muted" title="Order History"><i class="ri-eye-line align-middle fs-20"></i></a>
											</div>
										</td>
									</tr>
									@endforeach
							</tbody>
						</table>
					</div>
					<!-- end table-responsive -->
				</div>
				<div class="card-footer border-0">
					{!! $users->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
				
			</div>
		</div>
	</div>
@endsection