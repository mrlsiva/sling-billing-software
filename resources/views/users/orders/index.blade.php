@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Order History</title>
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <p class="card-title">Order History</p>
                </div>
            </div>
            <div class="card-body pt-2 ">
                <ul class="nav nav-tabs nav-justified">

                	 <li class="nav-item">
                        <a href="{{route('order.index', ['company' => request()->route('company'),'branch' => 0])}}" class="nav-link {{ request()->route('branch') == 0 ? 'active' : '' }}" id="{{Auth::user()->id}}">
                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
                            <span class="d-none d-sm-block"><i class="ri-store-2-line me-2"></i>{{Auth::user()->user_name}}</span>
                        </a>
                    </li>

                   
                    @foreach($branches as $branch)
                    	<li class="nav-item">
	                        <a href="{{route('order.index', ['company' => request()->route('company'),'branch' => $branch->id])}}" class="nav-link {{ request()->route('branch') == $branch->id ? 'active' : '' }}" id="{{$branch->id}}">
	                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
	                            <span class="d-none d-sm-block"><i class="ri-store-2-line me-2"></i></i>{{$branch->user_name}}</span>
	                        </a>
                    	</li>
                    @endforeach

                </ul>

                <form method="get" action="{{route('order.index', ['company' => request()->route('company'),'branch' => request()->route('branch')])}}">
                    <div class="row mb-2 p-3">
                        <div class="col-md-11">
                            <div class="input-group">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Branch Name/ Branch Username/ Customer Name/ Customer Phone/ Bill No" name="order" value="{{ request('order') }}" id="searchInput">
                                <span class="input-group-text" id="clearFilter" style="display: {{ request('order') ? 'inline-flex' : 'none' }}"><a href="{{route('order.index', ['company' => request()->route('company'),'branch' => request()->route('branch')])}}" class="link-dark"><i class="ri-filter-off-line align-middle fs-20"></i></a></span>
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
                                        <th>Branch</th>
										<th>Bill ID</th>
										<th>Amount (In ₹)</th>
										<th>Billed On</th>
										<th>Billed By</th>
										<th>Customer</th>
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
											{{$order->branch->name}}
										</td>
										<td>
											{{$order->bill_id}}
										</td>
										<td>
											{{$order->bill_amount}}
										</td>
										<td>
											{{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y') }}
										</td>
										<td>
											{{ $order->billedBy->name }}
										</td>
										<td>
											{{ $order->customer->phone }} ({{ $order->customer->name }})
										</td>
										<td>

                                            <a href="{{ route('order.view_bill', ['company' => request()->route('company'),'id' => $order->id ]) }}" class="link-dark" target="_blank"><i class="ri-eye-line align-middle fs-20" title="View Bill"></i></a>
                                            
											<a href="{{ route('order.get_bill', ['company' => request()->route('company'),'id' => $order->id ]) }}" class="link-dark" target="_blank"><i class="ri-printer-line align-middle fs-20" title="Print Bill"></i></a>
										</td>
									</tr>
								@endforeach
                                </tbody>
                            </table>
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