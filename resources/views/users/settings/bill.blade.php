@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Bill Setttings</title>
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <p class="card-title">Bill Settings</p>
                </div>
            </div>
            <div class="card-body pt-2 ">
                <ul class="nav nav-tabs nav-justified">

                    @foreach($branches as $Branch)
                    	<li class="nav-item">
	                        <a href="{{route('setting.bill.index', ['company' => request()->route('company'),'branch' => $Branch->id])}}" class="nav-link {{ $branch == $Branch->id ? 'active' : '' }}" id="{{$Branch->id}}">
	                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
	                            <span class="d-none d-sm-block"><i class="ri-shopping-basket-line me-2"></i></i>{{$Branch->user_name}}</span>
	                        </a>
                    	</li>
                    @endforeach
                    

                </ul>

                <div class="tab-content pt-2 text-muted">
                    <div class="tab-pane show active" id="homeTabsJustified">

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
                
                        <div class="d-flex justify-content-end p-3">
                            <a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#BillAdd"><i class='bx bxs-folder-plus'></i> Bill Setup</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Bill No</th>
                                        <th>Status</th>
                                        <th>Created On</th>
                                    </tr>
                                </thead> 
                                <tbody>
                                    @foreach($bills as $bill)
                                        <tr>
                                            <td>
                                                {{ ($bills->currentPage() - 1) * $bills->perPage() + $loop->iteration }}
                                            </td>
                                            <td>{{$bill->bill_number}}</td>
                                            <td>
                                                @if($bill->is_active == 1)
                                                    <span class="badge bg-soft-success text-success">Active</span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger">In-Active</span>
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($bill->setup_on)->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-0">
                {!! $bills->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="BillAdd" tabindex="-1" aria-labelledby="BillAdd" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" >
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Bill Setup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="row" action="{{route('setting.bill.store', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    <input type="hidden" name="branch_id" id="branch_id" value="{{$branch}}">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="bill" class="form-label">New Bill Number</label>
                                <div class="input-group">
                                    <input type="text" name="bill" id="bill" class="form-control" required="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
@endsection