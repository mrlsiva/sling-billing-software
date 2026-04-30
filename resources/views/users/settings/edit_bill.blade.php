@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Bill Edit</title>
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <p class="card-title">Bill Edit</p>
                </div>
            </div>
            <div class="card-body pt-2 ">
                <ul class="nav nav-tabs nav-justified">

                	 <li class="nav-item">
                        <a href="{{route('setting.bill.index', ['company' => request()->route('company'),'branch' => 0])}}" class="nav-link {{ request()->route('branch') == 0 ? 'active' : '' }}" id="{{Auth::user()->id}}">
                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
                            <span class="d-none d-sm-block"><i class="ri-store-2-line me-2"></i>{{Auth::user()->user_name}}</span>
                        </a>
                    </li>

                   
                    @foreach($branches as $branch)
                    	<li class="nav-item">
	                        <a href="{{route('setting.bill.index', ['company' => request()->route('company'),'branch' => $branch->id])}}" class="nav-link {{ request()->route('branch') == $branch->id ? 'active' : '' }}" id="{{$branch->id}}">
	                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
	                            <span class="d-none d-sm-block"><i class="ri-store-2-line me-2"></i></i>{{$branch->user_name}}</span>
	                        </a>
                    	</li>
                    @endforeach

                </ul>

                <form method="get" action="{{route('setting.bill.index', ['company' => request()->route('company'),'branch' => request()->route('branch')])}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Bill No" name="order" value="{{ request('order') }}" id="searchInput">
                                <span class="input-group-text" id="clearFilter" style="display: {{ request('order') ? 'inline-flex' : 'none' }}"><a href="{{route('setting.bill.index', ['company' => request()->route('company'),'branch' => request()->route('branch')])}}" class="link-dark"><i class="ri-close-large-line align-middle fs-20"></i></a></span>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-primary"> Search </button>
                        </div>
                    </div>
                </form>

                <div class="tab-content pt-2 text-muted">
                    <div class="tab-pane show active" id="homeTabsJustified">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>S.No</th>
										<th>Bill ID</th>
										<th>Billed On</th>
										<th>Action</th>
                                    </tr>
                                </thead> 
                                <tbody>
                                	@foreach($orders as $order)
									<tr>
										<td>
											{{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}
										</td>
										<td>
											{{$order->bill_id}}
										</td>
										<td>
											{{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y h:i A') }}
										</td>
										<td>

                                            <a href="javascript:void(0)"
                                               class="text-decoration-underline text-decoration-none editBill"
                                               data-id="{{ $order->id }}"
                                               data-bill="{{ $order->bill_id }}"
                                               data-date="{{ \Carbon\Carbon::parse($order->billed_on)->format('Y-m-d\TH:i') }}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#billEdit">
                                                <i class="ri-edit-line fs-18"></i>
                                            </a> 

										</td>
									</tr>
								@endforeach
                                </tbody>
                            </table>
                            @if($orders->isEmpty())
                                @include('no-data')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-0">
				{!! $orders->withQueryString()->links('pagination::bootstrap-5') !!}
			</div>
        </div>
    </div>
</div>

<div class="modal fade" id="billEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bill Edit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form class="row" action="{{route('setting.bill.edit', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="bill_id" id="bill_id">
                    <input type="datetime-local" name="billed_on" id="billed_on" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
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

<script>
    $(document).on('click', '.editBill', function () {
        let id   = $(this).data('id');
        let bill = $(this).data('bill');
        let date = $(this).data('date');

        $('#bill_id').val(bill);
        $('#billed_on').val(date);
    });
</script>
@endsection