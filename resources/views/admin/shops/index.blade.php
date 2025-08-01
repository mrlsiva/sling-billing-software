@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Shop</title>
@endsection

@section('body')

	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
							<p class="card-title">All Shops</p>
					</div>
					<a href="{{route('admin.shop.create')}}" class="btn btn-outline-primary btn-sm fw-semibold"><i class='bx bxs-folder-plus'></i> Create Shop</a>
				</div>
				<div class="">
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>Shop Name</th>
									<th>Slug Name</th>
									<th>Mobile Number</th>
									<th>Payment Method</th>
									<th>Expiry Date</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								@foreach($shops as $shop)
									<tr>
										<td>{{$shop->name}}</td>
										<td>{{$shop->user_name}}</td>
										<td>{{$shop->phone}}</td>
										<td>
											@if($shop->user_detail->payment_method == 1)
												<span class="badge bg-soft-primary text-primary">Monthly</span>
											@elseif($shop->user_detail->payment_method == 2)
												<span class="badge bg-soft-primary text-primary">Quarterly</span>
											@elseif($shop->user_detail->payment_method == 3)
												<span class="badge bg-soft-primary text-primary">Semi-Yearly</span>
											@elseif($shop->user_detail->payment_method == 4)
												<span class="badge bg-soft-primary text-primary">Yearly</span>
											@else
												-
											@endif
										</td>

										@php

					                        $paymentDate = \Carbon\Carbon::parse($shop->user_detail->payment_date);
					                        $paymentMethod = $shop->user_detail->payment_method;

					                        switch ($paymentMethod) {
					                            case 1:
					                                $nextPaymentDate = $paymentDate->copy()->addMonth();
					                                break;
					                            case 2:
					                                $nextPaymentDate = $paymentDate->copy()->addMonths(3);
					                                break;
					                            case 3:
					                                $nextPaymentDate = $paymentDate->copy()->addMonths(6);
					                                break;
					                            case 4:
					                                $nextPaymentDate = $paymentDate->copy()->addYear();
					                                break;
					                            default:
					                                $nextPaymentDate = null;
					                        }
                    					@endphp

										<td>{{ $nextPaymentDate ? $nextPaymentDate->format('d M Y') : '-' }}</td>
										<td>
											@if($shop->is_lock == 1)
												<span class="badge bg-soft-danger text-danger">Locked</span>
											@elseif($shop->is_delete == 1)
												<span class="badge bg-soft-danger text-danger">Deleted</span>
											@elseif($shop->is_active == 0)
												<span class="badge bg-soft-danger text-danger">In-active</span>
											@else
												<span class="badge bg-soft-success text-success">Active</span>
											@endif
										</td>
										<td>
											<div class="d-flex gap-3">
												<a href="{{route('admin.shop.view', ['id' => $shop->id])}}" class="text-muted"><i class="ri-eye-line align-middle fs-20"></i></a>
												<a href="{{route('admin.shop.edit', ['id' => $shop->id])}}" class="link-dark"><i class="ri-edit-line align-middle fs-20"></i></a>

												@if($shop->is_delete == 0)
												<a href="{{route('admin.shop.delete', ['id' => $shop->id])}}" class="link-danger"  onclick="return confirm('Are you sure you want to delete this shop?');"><i class="ri-delete-bin-5-line align-middle fs-20"></i></a>
												@endif
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
					{!! $shops->withQueryString()->links('pagination::bootstrap-5') !!}
				</div>
			</div>
		</div>
	</div>
@endsection