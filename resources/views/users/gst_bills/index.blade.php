@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | GST Bill</title>
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <p class="card-title">GST Bill</p>
                </div>
                <div>
                    <a class="btn btn-outline-primary btn-sm fw-semibold" href="{{route('gst_bill.create', ['company' => request()->route('company'),'branch' => request()->route('branch')])}}"><i class='bx bxs-folder-plus'></i> Create GST Bill</a>
                    <a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkUpload"><i class='bx bxs-folder-plus'></i> Bulk Upload</a>
                </div>
            </div>
            <div class="card-body pt-2 ">
                <ul class="nav nav-tabs nav-justified">

                	 <li class="nav-item">
                        <a href="{{route('gst_bill.index', ['company' => request()->route('company'),'branch' => 0])}}" class="nav-link {{ request()->route('branch') == 0 ? 'active' : '' }}" id="{{Auth::user()->id}}">
                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
                            <span class="d-none d-sm-block"><i class="ri-store-2-line me-2"></i>{{Auth::user()->user_name}}</span>
                        </a>
                    </li>

                   
                    @foreach($branches as $branch)
                    	<li class="nav-item">
	                        <a href="{{route('gst_bill.index', ['company' => request()->route('company'),'branch' => $branch->id])}}" class="nav-link {{ request()->route('branch') == $branch->id ? 'active' : '' }}" id="{{$branch->id}}">
	                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
	                            <span class="d-none d-sm-block"><i class="ri-store-2-line me-2"></i></i>{{$branch->user_name}}</span>
	                        </a>
                    	</li>
                    @endforeach

                </ul>

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

                @if(session('error_alert'))
                <div class="alert alert-danger">
                  <strong>Warning! </strong>{{ session('error_alert') }}<br>
                </div>
                @endif

                <div class="tab-content pt-2 text-muted">
                    <div class="tab-pane show active" id="homeTabsJustified">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Order ID</th>
                                        <th>Amount (in ₹)</th>
                                        <th>Billed On</th>
                                        <th>Billed By</th>
                                        <th>Customer</th>
                                        <th>Action</th>
                                    </tr>
                                </thead> 
                                <tbody>
                                	@foreach($gst_bills as $gst_bill)
                                        <tr>

                                            <td>
                                                {{ ($gst_bills->currentPage() - 1) * $gst_bills->perPage() + $loop->iteration }}
                                            </td>
                                            <td>#{{$gst_bill->order_id}}</td>
                                            <td>₹{{ number_format($gst_bill->total_gross, 2) }}</td>
                                            <td>{{ \Carbon\Carbon::parse( $gst_bill->transfer_on)->format('l, d F Y h:i A') }}</td>
                                            <td>{{$gst_bill->sold_by}}</td>
                                            <td>{{$gst_bill->customer_name}}</td>
                                            <td>
                                                <a href="{{ route('gst_bill.view_bill', ['company' => request()->route('company'),'id' => $gst_bill->id ]) }}" class="link-dark" target="_blank"><i class="ri-printer-line align-middle fs-20" title="Print Bill"></i></a>
                                            </td>
                                            
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if($gst_bills->isEmpty())
                                @include('no-data')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-0">
				{!! $gst_bills->withQueryString()->links('pagination::bootstrap-5') !!}
			</div>
        </div>
    </div>
</div>

<div class="modal fade" id="bulkUpload" tabindex="-1" aria-labelledby="bulkUpload" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" >
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Bulk Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="row" action="{{route('gst_bill.bulk_upload', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-12 d-flex justify-content-end">
                            <a href="{{ asset('assets/templates/gst_bill.xlsx') }}" download="Gst_Bill.xlsx">Download Template</a>
                        </div>
                    </div>

                    <input type="hidden" name="branch" value="{{request()->route('branch')}}">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name" class="form-label">Upload File</label>
                                <div class="input-group">
                                    <input type="file" name="file" id="file" class="form-control" accept=".xlsx">
                                </div>
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
</div>



@endsection