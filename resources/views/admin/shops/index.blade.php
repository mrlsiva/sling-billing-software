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

				<form method="get" action="{{route('admin.shop.index')}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Name/ User Name/ Slug Name/ Phone" name="shop" value="{{ request('shop') }}">
                                <span class="input-group-text"><a href="{{route('admin.shop.index')}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-primary"> Search </button>
                        </div>
                    </div>
                </form>

				<div class="">
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>Image</th>
									<th>Shop Name</th>
									<th>Slug Name</th>
									<th>User Name</th>
									<th>Mobile Number</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								@foreach($shops as $shop)
									<tr>
										<td>
											<img src="{{ asset('storage/' . $shop->logo) }}" class="logo-dark me-1" alt="Shop" height="30">
										</td>
										<td>{{$shop->name}}</td>
										<td>{{$shop->slug_name}}</td>
										<td>{{$shop->user_name}}</td>
										<td>{{$shop->phone}}</td>
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