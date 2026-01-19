@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Payment Method</title>
@endsection

@section('body')
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title">All Payment Method</p>
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

				<div class="">
					<div class="table-responsive">
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Name</th>
									<th>Active / In-Active</th>
								</tr>
							</thead>
							<tbody>
									@foreach($shop_payments as $shop_payment)
									<tr>
										<td>
											{{ $loop->iteration }}
										</td>
										<td>{{$shop_payment->payment->name}}</td>
										<td>
										    <form action="{{ route('setting.payment.update', ['company' => request()->route('company')]) }}" method="post" onsubmit="return confirm('Are you sure you want to change the payment status?')">
										        @csrf
										        <input type="hidden" name="id" value="{{ $shop_payment->id }}">
										        <div class="form-check form-switch">
										            <input class="form-check-input" type="checkbox" name="is_active" onchange="if(confirm('Are you sure you want to change the payment status?')) { this.form.submit(); } else { this.checked = !this.checked; }"
										                {{ $shop_payment->is_active == 1 ? 'checked' : '' }}>
										        </div>
										    </form>
										</td>
									</tr>
									@endforeach
							</tbody>
						</table>
						@if($shop_payments->isEmpty())
                        	@include('no-data')
                        @endif
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection