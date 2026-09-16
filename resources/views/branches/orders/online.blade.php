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
                <div>
                    <a href="{{route('branch.order.index', ['company' => request()->route('company')])}}" class="btn btn-sm btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body pt-2 ">

                <form method="get" action="{{route('branch.order.index', ['company' => request()->route('company')])}}">
                    <div class="row mb-2">
                        <div class="col-md-11">
                            <div class="input-group ">
                                <span class="input-group-text" id="addon-wrapping"><i class="ri-search-line align-middle fs-20"></i></span>
                                <input type="text" class="form-control" placeholder="Customer Name/ Customer Phone/ Customer GST/ Bill No" name="order" value="{{ request('order') }}" id="searchInput">
                                <span class="input-group-text" id="clearFilter" style="display: {{ request('order') ? 'inline-flex' : 'none' }}"><a href="{{route('branch.order.index', ['company' => request()->route('company')])}}" class="link-dark"><i class="ri-close-large-line align-middle fs-20"></i></a></span>
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
										<th>Customer</th>
                                        <th>Customer GST</th>
                                        <th>Status</th>
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
                                            @if($order->branch_id != null)
										        {{$order->branch->name}} ({{$order->branch->user_name}})
                                            @else
                                                {{$order->shop->name}} ({{$order->shop->user_name}})
                                            @endif
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
											{{ $order->customer->phone }} ({{ $order->customer->name }})
										</td>
                                        <td>
                                            @if($order->customer->gst != null)
                                                {{ $order->customer->gst }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                        	@if($order->status == 0)
                                        		<span class="badge bg-soft-success text-success">Order Placed</span>
                                            @elseif($order->status == 1)
                                                <span class="badge bg-soft-success text-success">Order Approved</span>
                                            @elseif($order->status == 2)
                                                <span class="badge bg-soft-success text-success">Order Packed</span>
                                            @elseif($order->status == 3)
                                                <span class="badge bg-soft-success text-success">Order In-Transit</span>
                                            @elseif($order->status == 4)
                                                <span class="badge bg-soft-success text-success">Order Delivered</span>
                                            @elseif($order->status == 5)
                                                <span class="badge bg-soft-success text-success">Order Declined</span>
                                            @endif
                                        </td>
										<td>


                                            <a href="{{ route('branch.order.online.edit', ['company' => request()->route('company'), 'id' => $order->id]) }}" class="link-dark"><i class="ri-edit-line align-middle fs-20" title="Edit Bill"></i></a>

                                            @if($order->status != 0)
                                                <a href="{{ route('branch.billing.view_bill', ['company' => request()->route('company'),'id' => $order->id ]) }}" class="link-dark" target="_blank"><i class="ri-eye-line align-middle fs-20" title="View Bill"></i></a>

												<a href="{{ route('branch.billing.get_bill', ['company' => request()->route('company'),'id' => $order->id ]) }}" class="link-dark" target="_blank"><i class="ri-printer-line align-middle fs-20" title="Print Bill"></i></a>
                                            @endif

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