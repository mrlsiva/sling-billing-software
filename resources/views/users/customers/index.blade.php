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

				<div class="">
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