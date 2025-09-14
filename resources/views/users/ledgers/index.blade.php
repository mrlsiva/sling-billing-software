@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Product Transfer</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"
    rel="stylesheet" />
@endsection

@section('body')
<div class="row">
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
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{route('vendor.index', ['company' => request()->route('company')])}}" class="btn btn-sm"> <i class="bx bx-arrow-back me-1"></i></a>
                    <p class="card-title mb-0">{{$vendor->name}}</p>
                </div>
                <a data-bs-toggle="modal" data-bs-target="#paymentAdd" class="btn btn-sm btn-success"> <i class="bx bx-plus me-1"></i>New Entry </a>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-xl-12">
        <div class="card">
            <div class="card-body ">
                <form method="GET" action="{{ route('vendor.ledger.index', ['company' => request()->route('company'),'id' => $vendor->id ]) }}">
                    <div class="row align-items-center">
                        <div class="col-3">
                            <h4 class="mt-3">Choose date range</h4>
                        </div>
                        <div class="col-3">
                            <label for="fromDate" class="col-form-label mb-1 pb-1">From:</label>
                            <input type="text" id="from_date" name="from_date" class="form-control datepicker" value="{{ request('from_date') }}" />
                        </div>
                        <div class="col-3">
                            <label for="toDate" class="col-form-label mb-1 pb-1">To:</label>
                            <input type="text" id="to_date" name="to_date" class="form-control datepicker" value="{{ request('to_date') }}" />
                        </div>
                        <div class="col-3">
                            <label for="toDate" class="col-form-label mb-1 pb-1">&nbsp</label>

                            <button type="submit" class="btn btn-primary w-100">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body ">
                <div class="d-flex align-items-center gap-3">
                    <img src="assets/images/food-icon/sup-2.png" alt="" class="img-fluid">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">
                            Rs. {{ number_format($totalGross, 2) }}
                        </p>
                        <p class="card-title mb-0">Total Purchased</p>
                    </div>
                    <!-- <div class="ms-auto">
                        <a href="#!" class="btn btn-primary avatar-sm rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ri-eye-line align-middle fs-16 text-white"></i>
                        </a>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body ">
                <div class="d-flex align-items-center gap-3">
                    <img src="assets/images/food-icon/sup-3.png" alt="" class="img-fluid">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">
                            Rs. {{ number_format($totalPaid, 2) }}
                        </p>
                        <p class="card-title mb-0">Total Billed Paid</p>
                    </div>
                    <div class="ms-auto">
                        <a href="#!" class="btn btn-primary avatar-sm rounded-circle d-flex align-items-center justify-content-center viewBill" data-vendor="{{ $vendor->id }}">
                            <i class="ri-eye-line align-middle fs-16 text-white"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body ">
                <div class="d-flex align-items-center gap-3">
                    <img src="assets/images/food-icon/sup-4.png" alt="" class="img-fluid">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1" id="balance_amount"data-balance="{{ $balance }}">
                            Rs. {{ number_format($balance, 2) }}
                        </p>
                        <p class="card-title mb-0">Total Balance</p>
                    </div>
                    <!-- <div class="ms-auto">
                        <a href="#!" class="btn btn-primary avatar-sm rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ri-eye-line align-middle fs-16 text-white"></i>
                        </a>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body ">
                <div class="d-flex align-items-center gap-3">
                    <img src="assets/images/food-icon/sup-4.png" alt="" class="img-fluid">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1" >
                            Rs. {{ number_format($vendor->prepaid_amount, 2) }}
                        </p>
                        <p class="card-title mb-0">Prepaid Amount</p>
                    </div>
                    <!-- <div class="ms-auto">
                        <a href="#!" class="btn btn-primary avatar-sm rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ri-eye-line align-middle fs-16 text-white"></i>
                        </a>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h5 class="card-title mb-0">Ledger</h5>
                    <div class="search-bar ms-auto">
                        <span style="top: 5px;"><i class="bx bx-search"></i></span>
                        <form method="GET" action="{{ route('vendor.ledger.index', ['company' => request()->route('company'),'id' => $vendor->id ]) }}">
                        <input type="hidden" name="from_date" value="{{ request('from_date') }}" />
                        <input type="hidden" name="to_date" value="{{ request('to_date') }}" />
                        <input type="search" class="form-control form-control-sm" id="search" name="search" placeholder="Search..." value="{{ request('search') }}">
                        </form>
                    </div>

                </div> <!-- end row -->
            </div>
            <div>
                <div class="table-responsive table-centered">
                    <table class="table table-striped text-nowrap mb-0">
                        <thead class="text-uppercase fs-12">
                            <tr>
                                <th class="border-0 py-2 text-dark">Invoice</th>
                                <th class="border-0 py-2 text-dark">Purchase Date</th>
                                <th class="border-0 py-2 text-dark">Due Date</th>
                                <th class="border-0 py-2 text-dark">Bill Amount</th>
                                <th class="border-0 py-2 text-dark">Paid Amount</th>
                                <th class="border-0 py-2 text-dark">Discount</th>
                                <th class="border-0 py-2 text-dark">Payment Status</th>
                                <th class="border-0 py-2 text-dark">Via</th>
                                <th class="border-0 py-2 text-dark">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase_orders as $purchase_order)
                                <tr>
                                    <td>
                                        @if($purchase_order->invoice_no != null)
                                            <a class="fw-medium">{{$purchase_order->invoice_no}}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($purchase_order->invoice_date)->format('d M Y') }}</td>
                                    <td>
                                        @if($purchase_order->due_date != null)
                                            {{ \Carbon\Carbon::parse($purchase_order->due_date)->format('d M Y') }}
                                        @else
                                            -
                                        @endif 
                                    </td>
                                    <td>Rs. {{number_format($purchase_order->gross_cost,2)}}</td>
                                    @php
                                        $payment_details = App\Models\VendorPaymentDetail::where('purchase_order_id', $purchase_order->id)->get();
                                    @endphp
                                    <td>Rs. {{number_format($payment_details->sum('amount'),2)}}</td>
                                    <td>
                                        @if($purchase_order->discount != null)
                                            Rs. {{number_format($purchase_order->discount,2)}}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($purchase_order->status == 0)
                                            <span class="badge badge-soft-danger">Unpaid</span>
                                        @elseif($purchase_order->status == 1)
                                            <span class="badge badge-soft-success">Paid</span>

                                        @elseif($purchase_order->status == 2)
                                            <span class="badge badge-soft-warning">Partially Paid</span>
                                        @endif
                                    </td>

                                    @php
                                        $payment_detail = App\Models\VendorPaymentDetail::where('purchase_order_id', $purchase_order->id)->latest()->first();
                                    @endphp

                                    <td>
                                        @if($payment_detail != null)

                                            @if($payment_detail->payment_id != null)
                                                {{$payment_detail->payment->name}}
                                            @else
                                                -
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if($purchase_order->status != 1)
                                            <button type="button" class="btn btn-sm btn-soft-secondary me-1" data-bs-toggle="modal" data-bs-target="#purchaseEdit" data-id="{{ $purchase_order->id }}" data-old_amount="{{ $purchase_order->gross_cost }}">
                                                <i class="bx bx-edit fs-16"></i>
                                            </button>
                                        @else
                                            -
                                        @endif

                                    </td>
                                </tr>

                                @php
                                    $purchase_details = App\Models\PurchaseOrderDetail::where('purchase_order_id', $purchase_order->id)->get();
                                @endphp

                                @if ($purchase_details->isNotEmpty())
                                    <tr>
                                        <td colspan="9">
                                            <table class="table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Updated On</th>
                                                        <th scope="col">Previous Amount</th>
                                                        <th scope="col">Updated Amount</th>
                                                        <th scope="col">Reason</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($purchase_details as $purchase_detail)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($purchase_detail->updated_on)->format('d M Y') }}</td>
                                                        <td>Rs. {{number_format($purchase_detail->old_amount,2)}}</td>
                                                        <td>Rs. {{number_format($purchase_detail->new_amount,2)}}</td>
                                                        <td>{{$purchase_detail->comment}}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                @endif

                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div> <!-- end card body -->
            <div class="card-footer border-0">
                {!! $purchase_orders->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>
        </div> <!-- end card -->
    </div> <!-- end col -->
</div>

<div class="modal fade" id="paymentAdd" tabindex="-1" aria-labelledby="paymentAdd" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" >
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Add Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="row" action="{{route('vendor.payment.store', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data" id="paymentStore">
                @csrf
                <div class="modal-body">

                    <input type="hidden" name="vendor_id" value="{{$vendor->id}}">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="payment" class="form-label">Payment</label>
                                <select class="form-control" name="payment" id="payment" required="">
                                    <option value="">Select</option>
                                    @foreach($payment_methods as $payment_method)
                                    <option value="{{$payment_method->id}}">{{$payment_method->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label text-muted">Payment Amount</label>
                                <input type="number" id="payment_amount" name="payment_amount" class="form-control" required="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label text-muted">Comment</label>
                                <input type="text" id="comment" name="comment" class="form-control">
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

<div class="modal fade" id="purchaseEdit" tabindex="-1" aria-labelledby="purchaseEdit" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" >
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Edit Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="row" action="{{route('vendor.purchase_order.update', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    <input type="hidden" name="purchase_order_id" id="purchase_order_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label text-muted">Old Amount</label>
                                <input type="text" id="old_amount" name="old_amount" class="form-control" required="" readonly="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label text-muted">New Amount</label>
                                <input type="number" id="new_amount" name="new_amount" class="form-control" required="" >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label text-muted">Reason</label>
                                <input type="text" id="reason" name="reason" class="form-control" placeholder="Enter Reason">
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

<div class="modal fade" id="billView" tabindex="-1" aria-labelledby="billView" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" >
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">View Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover table-centered">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>S.No</th>
                                <th>Payment Method</th>
                                <th>Amount</th>
                                <th>Paid On</th>
                                <th>Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Bootstrap Datepicker JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
    $(document).ready(function() {
    // initialize both datepickers
    $('#from_date').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function(e) {
        // set the selected date as the minimum for to_date
        $('#to_date').datepicker('setStartDate', e.date);
    });

    $('#to_date').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var purchaseEditModal = document.getElementById('purchaseEdit');
    purchaseEditModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;

        // Get data from button
        var id = button.getAttribute('data-id');
        var oldAmount = button.getAttribute('data-old_amount');

        // Set values into modal inputs
        purchaseEditModal.querySelector('#purchase_order_id').value = id;
        purchaseEditModal.querySelector('#old_amount').value = oldAmount;
    });
});
</script>

<script type="text/javascript">
    jQuery(document).ready(function () {
    jQuery(document).on('click', '.viewBill', function () {
        var vendorId = jQuery(this).data('vendor');

        jQuery.ajax({
            url: '../get-vendor-payments/' + vendorId,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                let tbody = jQuery('#billView tbody');
                tbody.empty(); // clear previous data

                if (data.length > 0) {
                    jQuery.each(data, function (index, payment) {
                        tbody.append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td>${payment.payment ? payment.payment.name : 'Prepaid'}</td>
                                <td>${payment.amount}</td>
                                <td>${payment.paid_on ? payment.paid_on : '-'}</td>
                                <td>${payment.comment ? payment.comment : '-'}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append(`
                        <tr>
                            <td colspan="5" class="text-center text-muted">No payments found</td>
                        </tr>
                    `);
                }

                // Show modal
                jQuery('#billView').modal('show');
            }
        });
    });
});

</script>

<!-- <script>
document.addEventListener('DOMContentLoaded', function () {
    let form = document.getElementById('paymentStore');
    form.addEventListener('submit', function (e) {
        let paymentInput = document.getElementById('payment_amount');
        let balance = parseFloat(document.getElementById('balance_amount').getAttribute('data-balance'));

        let amount = parseFloat(paymentInput.value);

        if (isNaN(amount) || amount <= 0) {
            e.preventDefault();
            alert("Please enter a valid payment amount.");
            return;
        }

        if (amount > balance) {
            e.preventDefault();
            alert("Payment amount cannot exceed remaining balance of Rs. " + balance.toFixed(2));
            return;
        }
    });
});
</script> -->

@endsection