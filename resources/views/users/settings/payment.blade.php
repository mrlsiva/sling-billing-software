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

					<!-- <a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#paymentEdit"><i class='bx bxs-edit'></i> Select/ Update </a> -->

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
						<!-- <table class="table align-middle mb-0 table-hover table-centered">
							<thead class="bg-light-subtle">
								<tr>
									<th>S.No</th>
									<th>Name</th>
								</tr>
							</thead>
							<tbody>
									@foreach($shop_payments as $shop_payment)
									<tr>
										<td>
											{{ $loop->iteration }}
										</td>
										<td>{{$shop_payment->payment->name}}</td>
									</tr>
									@endforeach
							</tbody>
						</table> -->
						<table class="table align-middle mb-0 table-hover table-centered">
							<thead>
								<tr>
									<th>S.No</th>
									<th>Payment Method</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								@foreach($payments as $payment)
									<tr>
										<td>
											{{ $loop->iteration }}
										</td>
										<td>{{ $payment->name }}</td>
										
										<td>
											<div class="form-check form-switch">
												<input class="form-check-input" type="checkbox" name="payments[]" value="{{ $payment->id }}"
													id="payment-{{ $payment->id }}"
													@if(in_array($payment->id, $shop_payment_ids)) checked @endif>
												<label class="form-check-label" for="payment-{{ $payment->id }}"></label>
											</div>
										</td>
										
									</tr>
								@endforeach
							</tbody>
						</table>

					</div>
					<!-- end table-responsive -->
				</div>
				
			</div>
		</div>
	</div>

    <!-- <div class="modal fade" id="paymentEdit" tabindex="-1" aria-labelledby="paymentEdit" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Update Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="row" action="{{route('setting.payment.store', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                	@csrf
	                <div class="modal-body">

	                   <div class="row">
		                    <div class="col-md-12">
		                        <div class="mb-3">
		                        	<label for="choices-single-groups" class="form-label text-muted">Payment Method</label>
                                    <span class="text-danger">*</span>
                                    <select class="form-control" data-choices name="payments[]" id="payments" multiple="">
                                        <option value=""> Select </option>
                                        @foreach($payments as $payment)
										    <option value="{{ $payment->id }}" @if(in_array($payment->id, $shop_payment_ids)) selected @endif>
										        {{ $payment->name }}
										    </option>
										@endforeach
                                    </select>
		                        </div>
		                    </div>
	                   </div>
	                   
	                </div>
	                <div class="modal-footer">
	                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
	                    <button type="submit" class="btn btn-primary">Submit</button>
	                </div>
                </form>
            </div>
        </div>
    </div> -->

@endsection