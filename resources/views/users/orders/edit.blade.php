@extends('layouts.master')

@section('title')
    <title>{{ config('app.name')}} | Edit Bill</title>
@endsection

@section('style')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .select2-container .select2-selection--single { height: 38px; border: 1px solid #ced4da; border-radius: 4px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; color: #495057; padding-left: 10px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
</style>
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

        @if(session('error'))
            <div class="alert alert-danger">
              <strong>Warning! </strong>{{ session('error') }}<br>
            </div>
        @endif

        <div class="col-xl-12 col-md-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h4 class="card-title">Online Order Update</h4>
                </div>
                <div class="card-body">
                    <div class="row border-bottom pb-3 mb-4">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label text-muted">
                                    Invoice No
                                </label>
                                <div id="invoiceView">
                                    {{ $order->bill_id }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">

                                <label class="form-label text-muted">
                                    Date
                                </label>

                                <div id="dateView">
                                    {{ \Carbon\Carbon::parse($order->billed_on)->format('d-m-Y') }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">

                                <label class="form-label text-muted">
                                    Time
                                </label>

                                <div id="timeView">
                                    {{ \Carbon\Carbon::parse($order->billed_on)->format('h:i A') }}
                                </div>

                            </div>
                        </div>
                        @if($order->status != 0 && $order->status != 5)
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label text-muted d-flex justify-content-between align-items-center">
                                        <span>
                                            Payment  : {{ $order_payment_details->map(fn($o) => $o->payment->name)->join(', ') }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @else
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label text-muted d-flex justify-content-between align-items-center">
                                        <span>
                                            Payment  : -
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @endif
                        @php
                        $billing_address = App\Models\BillingAddress::where('order_id', $order->id)->first();
                        @endphp

                        <div class="row mt-3">
                            <!-- Customer Details -->
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Customer Details</h5>
                                    </div>
                                    <div class="card-body">

                                        @if($billing_address)

                                            <div class="row mb-2">
                                                <div class="col-4 fw-semibold">Name</div>
                                                <div class="col-8">{{ strtoupper($billing_address->name) }}</div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-4 fw-semibold">Address</div>
                                                <div class="col-8">
                                                    {{ strtoupper($billing_address->address) }}
                                                    @if($billing_address->pincode)
                                                    - {{ $billing_address->pincode }}
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-4 fw-semibold">Mobile</div>
                                                <div class="col-8">
                                                    {{ $billing_address->phone }}
                                                    @if($billing_address->alt_phone)
                                                    , {{ $billing_address->alt_phone }}
                                                    @endif
                                                </div>
                                            </div>

                                        @else

                                            <div class="row mb-2">
                                                <div class="col-4 fw-semibold">Name</div>
                                                <div class="col-8">{{ strtoupper($order->customer->name) }}</div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-4 fw-semibold">Address</div>
                                                <div class="col-8">
                                                    {{ strtoupper($order->customer->address) }}
                                                    @if($order->customer->pincode)
                                                    - {{ $order->customer->pincode }}
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-4 fw-semibold">Mobile</div>
                                                <div class="col-8">
                                                    {{ $order->customer->phone }}
                                                    @if($order->customer->alt_phone)
                                                    , {{ $order->customer->alt_phone }}
                                                    @endif
                                                </div>
                                            </div>

                                            @if($order->customer->gst)
                                                <div class="row">
                                                    <div class="col-4 fw-semibold">GSTIN</div>
                                                    <div class="col-8">{{ $order->customer->gst }}</div>
                                                </div>
                                            @endif

                                        @endif

                                    </div>
                                </div>
                            </div>
                            <!-- Order Status -->
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Order Staus</h5>
                                    </div>
                                    <div class="card-body">

                                        
                                        <div class="row mb-2">
                                            <div class="col-5 fw-semibold">Status</div>

                                            <div class="col-7">
                                                @if($order->status != 0 && $order->status != 5)
                                                <select class="form-control" name="status" id="status" data-order-id="{{ $order->id }}">

                                                    <option value="">Select</option>

                                                    @php
                                                        $currentStatus = (int) $order->status;
                                                    @endphp

                                                    <option value="1" {{ $currentStatus == 1 ? 'selected' : '' }}>
                                                        Order Approved
                                                    </option>

                                                    <option value="2" {{ $currentStatus == 2 ? 'selected' : '' }}>
                                                        Order Packed
                                                    </option>

                                                    <option value="3" {{ $currentStatus == 3 ? 'selected' : '' }}>
                                                        Order In-Transit
                                                    </option>

                                                    <option value="4" {{ $currentStatus == 4 ? 'selected' : '' }}>
                                                        Order Delivered
                                                    </option>

                                                </select>
                                                @else
                                                    @if($order->status == 0) 
                                                        <span class="badge bg-soft-success text-success">Order Placed</span> 
                                                    @elseif($order->status == 1) 
                                                        <span class="badge bg-soft-success text-success">Order Approved</span>
                                                    @elseif($order->status == 5) 
                                                        <span class="badge bg-soft-success text-success">Order Declined</span> 
                                                    @endif
                                                @endif

                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <div class="col-5 fw-semibold">Update On</div>
                                            <div class="col-7">
                                                {{ $order->updated_at->toFormattedDateString() }}
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Product Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0 table-hover table-centered">
                                                <thead class="bg-light-subtle">
                                                    <tr>
                                                        <th>S.No</th>
                                                        <th>Product</th>
                                                        <th>Qty</th>
                                                        <th>Price</th>
                                                        <th>Tax</th>
                                                        <th>Total</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead> 
                                                <tbody>
                                                    @foreach($order_details as $detail)
                                                        <tr>
                                                            <td>{{$loop->iteration}}</td>
                                                            <td>{{$detail->product->name}}</td>
                                                            <td>{{$detail->quantity}}</td>
                                                            <td>{{$detail->price}}</td>
                                                            <td>{{$detail->tax_amount}}</td>
                                                            <td>{{$detail->price * $detail->quantity}}</td>
                                                            @php
                                                                $productStatus = (int) $detail->status;
                                                            @endphp

                                                            <td>
                                                                @if($order->status == 0)
                                                                <select
                                                                    class="form-control product-status"
                                                                    name="product_status"
                                                                    data-detail-id="{{ $detail->id }}"
                                                                >
                                                                    <option value="">Select</option>

                                                                    <option value="0"
                                                                        {{ $productStatus == 0 ? 'selected' : '' }}
                                                                        {{ $productStatus != 0 ? 'disabled' : '' }}>
                                                                        Order Placed
                                                                    </option>

                                                                    <option value="1" {{ $productStatus == 1 ? 'selected' : '' }}>
                                                                        Order Approved
                                                                    </option>

                                                                    <option value="2" {{ $productStatus == 2 ? 'selected' : '' }}>
                                                                        Order Declined
                                                                    </option>
                                                                </select>
                                                                @else
                                                                    @if($detail->status == 1) 
                                                                        <span class="badge bg-soft-success text-success">Order Approved</span>
                                                                    @elseif($detail->status == 2) 
                                                                        <span class="badge bg-soft-success text-success">Order Declined</span> 
                                                                    @endif   
                                                                @endif
                                                            </td>

                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($order->status == 0)
                            <div class="col-md-12">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Payment Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" id="orderUpdateForm" action="{{ route('order.online.update', ['company' => request()->route('company'),'id' => $order->id]) }}"enctype="multipart/form-data">
                                        @csrf
                                        <div class="row g-2">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="status" class="form-label">Status</label>
                                                    <select class="form-control" name="status" id="status">
                                                        <option value="">Select</option>
                                                        <option value="1">Order Approved</option>
                                                        <option value="5">Order Declined</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-2">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="payment" class="form-label">Payment</label>
                                                    <select class="form-control" data-choices name="payment" id="payment">
                                                        <option value="">Select</option>
                                                        @foreach($payments as $payment)
                                                        <option value="{{$payment->id}}">{{$payment->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-2 secret" id="cash">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="cash" class="form-label">Cash</label>
                                                    <span class="text-danger">*</span>
                                                    <input type="number" name="cash_amount" id="cash_amount" value="{{old('cash_amount')}}"
                                                        class="form-control" placeholder="Amount">
                                                </div>
                                            </div>

                                            <div class=" d-flex justify-content-center">
                                                <button type="button" class="btn btn-primary" id="cash_add" onclick="cash_add()"><i
                                                        class="ri-bank-line"></i> Add</button>
                                            </div>
                                        </div>

                                        <div class="row g-2 secret" id="card">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="card_number" class="form-label">Card Number</label>
                                                    <!-- <span class="text-danger">*</span> -->
                                                    <input type="number" name="card_number" id="card_number" value="{{old('card_number')}}"
                                                        class="form-control" placeholder="Card Number">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="card_name" class="form-label">Card Name</label>
                                                    <span class="text-danger">*</span>
                                                    <input type="text" name="card_name" id="card_name" value="{{old('card_name')}}"
                                                        class="form-control" placeholder="Card Name">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="card_amount" class="form-label">Amount</label>
                                                    <span class="text-danger">*</span>
                                                    <input type="number" name="card_amount" id="card_amount" value="{{old('card_amount')}}"
                                                        class="form-control" placeholder="Amount">
                                                </div>
                                            </div>

                                            <div class=" d-flex justify-content-center">
                                                <button type="button" class="btn btn-primary" id="card_add" onclick="card_add()"><i
                                                        class="ri-bank-line"></i> Add</button>
                                            </div>
                                        </div>

                                        <div class="row g-2 secret" id="finance">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="finance_card" class="form-label">Finance Reference Number</label>
                                                    <!-- <span class="text-danger">*</span> -->
                                                    <input type="text" name="finance_card" id="finance_card"
                                                        value="{{old('finance_card')}}" class="form-control"
                                                        placeholder="Finance Reference Number">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="finance_type" class="form-label">Finance</label>
                                                    <span class="text-danger">*</span>
                                                    <select class="form-control" data-choices name="finance_type" id="finance_type">
                                                        <option value="">Select</option>
                                                        @foreach($finances as $finance)
                                                        <option value="{{$finance->id}}">{{$finance->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="finance_amount" class="form-label">Amount</label>
                                                    <span class="text-danger">*</span>
                                                    <input type="number" name="finance_amount" id="finance_amount"
                                                        value="{{old('finance_amount')}}" class="form-control" placeholder="Amount">
                                                </div>
                                            </div>
                                            <div class=" d-flex justify-content-center">
                                                <button type="button" class="btn btn-primary" id="finance_add" onclick="finance_add()"><i
                                                        class="ri-bank-line"></i> Add</button>
                                            </div>
                                        </div>

                                        <div class="row g-2 secret" id="exchange">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="exchange_amount" class="form-label">Exchange</label>
                                                    <span class="text-danger">*</span>
                                                    <input type="number" name="exchange_amount" id="exchange_amount"
                                                        value="{{old('exchange_amount')}}" class="form-control"
                                                        placeholder="Exchange Amount">
                                                </div>
                                            </div>
                                            <div class=" d-flex justify-content-center">
                                                <button type="button" class="btn btn-primary" id="exchange_add" onclick="exchange_add()"><i
                                                        class="ri-bank-line"></i> Add</button>
                                            </div>
                                        </div>

                                        <div class="row g-2 secret" id="credit">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="credit_amount" class="form-label">Credit</label>
                                                    <span class="text-danger">*</span>
                                                    <input type="number" name="credit_amount" id="credit_amount"
                                                        value="{{old('credit_amount')}}" class="form-control" placeholder="Credit">
                                                </div>
                                            </div>
                                            <div class=" d-flex justify-content-center">
                                                <button type="button" class="btn btn-primary" id="credit_add" onclick="credit_add()"><i
                                                        class="ri-bank-line"></i> Add</button>
                                            </div>
                                        </div>

                                        <div class="row g-2 secret" id="cheque">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="cheque_number" class="form-label">Cheque No</label>
                                                    <span class="text-danger">*</span>
                                                    <input type="number" name="cheque_number" id="cheque_number"
                                                        value="{{old('cheque_number')}}" class="form-control" placeholder="Cheque No">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="cheque_amount" class="form-label">Amount</label>
                                                    <span class="text-danger">*</span>
                                                    <input type="number" name="cheque_amount" id="cheque_amount"
                                                        value="{{old('cheque_amount')}}" class="form-control" placeholder="Amount">
                                                </div>
                                            </div>
                                            <div class=" d-flex justify-content-center">
                                                <button type="button" class="btn btn-primary" id="cheque_add" onclick="cheque_add()"><i
                                                        class="ri-bank-line"></i> Add</button>
                                            </div>
                                        </div>

                                        <div class="row g-2 secret" id="upi">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="upi_amount" class="form-label">UPI Amount</label>
                                                    <span class="text-danger">*</span>
                                                    <input type="number" name="upi_amount" id="upi_amount" value="{{old('upi_amount')}}"
                                                        class="form-control" placeholder="UPI Amount">
                                                </div>
                                            </div>
                                            <div class=" d-flex justify-content-center">
                                                <button type="button" class="btn btn-primary" id="upi_add" onclick="upi_add()"><i
                                                        class="ri-bank-line"></i> Add</button>
                                            </div>
                                        </div>

                                        <h5 class="fw-semibold my-3">Payment info</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered bg-light-subtle">
                                                <thead>
                                                    <tr>
                                                        <td class="fw-semibold">Method</td>
                                                        <td class="fw-semibold">Amount</td>
                                                        <td class="fw-semibold">Action</td>
                                                    </tr>
                                                </thead>
                                                <tbody id="payment-info-body">
                                                    <!-- Rows will be appended dynamically -->
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td>
                                                            <p class="d-flex mb-0 align-items-center gap-1 fw-semibold text-success"
                                                                id="received_cash">Total Cash: </p>
                                                        </td>
                                                        <td colspan="2">
                                                            <p class="d-flex mb-0 align-items-center gap-1 fw-semibold text-success"
                                                                id="amount_text1">Payable Amount: ₹ {{ number_format($order->bill_amount, 2) }}</p>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <div class="d-flex justify-content-end">

                                            <input type="hidden" name="payments" id="payments">

                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                        </form>


                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')

<script>
    const orderBillAmount = {{ (float) $order->bill_amount }};

jQuery(document).ready(function () {
    jQuery('select[name="payment"]').on('change', function () {
        var payment = jQuery(this).val();
        if (payment) {
            if (payment == 1) {
                $('#cash').removeClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if (payment == 2) {
                $('#cash').addClass('secret');
                $('#card').removeClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if (payment == 3) {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').removeClass('secret');
            }
            else if (payment == 4) {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').removeClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if (payment == 5) {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').removeClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if (payment == 6) {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').removeClass('secret');
                $('#cheque').addClass('secret');
                $('#upi').addClass('secret');
            }
            else if (payment == 7) {
                $('#cash').addClass('secret');
                $('#card').addClass('secret');
                $('#finance').addClass('secret');
                $('#exchange').addClass('secret');
                $('#credit').addClass('secret');
                $('#cheque').removeClass('secret');
                $('#upi').addClass('secret');
            }

        }
    });
});

function appendPaymentRow(method, amount, extraData = {}) {

    let tbody = $("#payment-info-body");
    let rowId = Date.now() + Math.floor(Math.random() * 1000);

    amount = parseFloat(amount) || 0;

    if (amount <= 0) {
        showPaymentError("Please enter a valid payment amount.");
        return;
    }

    // ---------------------------------------------
    // Calculate current payment total
    // ---------------------------------------------
    let currentTotal = 0;

    tbody.find("tr").each(function () {

        let rowAmount = parseFloat(
            $(this).find("td").eq(1).text().replace(/[^\d.-]/g, "")
        ) || 0;

        currentTotal += rowAmount;
    });

    // ---------------------------------------------
    // Check bill amount
    // ---------------------------------------------
    let newTotal = currentTotal + amount;

    if (newTotal > orderBillAmount) {

        let remaining = orderBillAmount - currentTotal;

        showPaymentError(
            "Payment amount cannot exceed remaining amount of ₹" +
            Math.max(remaining, 0).toFixed(2)
        );

        return;
    }

    // ---------------------------------------------
    // Display name
    // ---------------------------------------------
    let displayMethod = method;

    if (method === "Card" && extraData.card_name) {

        displayMethod = `Card - ${extraData.card_name}`;

    } else if (method === "Finance" && extraData.finance_type_name) {

        displayMethod = `Finance - ${extraData.finance_type_name}`;

    } else if (method === "Cheque" && extraData.cheque_number) {

        displayMethod = `Cheque - ${extraData.cheque_number}`;
    }

    // ---------------------------------------------
    // ALWAYS APPEND NEW ROW
    // ---------------------------------------------
    tbody.append(`
        <tr
            data-id="${rowId}"
            data-method="${method}"
            data-extra='${JSON.stringify(extraData)}'
        >
            <td>${displayMethod}</td>

            <td>₹${amount.toFixed(2)}</td>

            <td>
                <button
                    type="button"
                    class="btn btn-sm btn-danger delete-row"
                >
                    <i class="ri-delete-bin-line"></i>
                </button>
            </td>
        </tr>
    `);

    updateTotal();
}

$(document).on("click", ".delete-row", function () {
    $(this).closest("tr").remove(); // remove row
    updateTotal(); // recalc total
});

function updateTotal() {
    let total = 0;
    $("#payment-info-body tr").each(function () {
        let amt = parseFloat($(this).find("td").eq(1).text().replace("₹", "")) || 0;
        total += amt;
    });
    $("#received_cash").text(`Total Cash: ₹${total.toFixed(2)}`);
}

function getRemainingPayment() {

    let total = 0;

    $("#payment-info-body tr").each(function () {

        total += parseFloat(
            $(this).find("td").eq(1).text().replace(/[^\d.-]/g, "")
        ) || 0;

    });

    return Math.max(orderBillAmount - total, 0);
}

$('select[name="payment"]').on('change', function () {

    let remaining = getRemainingPayment();

    switch ($(this).val()) {

        case "1":
            $("#cash_amount").val(remaining.toFixed(2));
            break;

        case "2":
            $("#card_amount").val(remaining.toFixed(2));
            break;

        case "3":
            $("#upi_amount").val(remaining.toFixed(2));
            break;

        case "4":
            $("#exchange_amount").val(remaining.toFixed(2));
            break;

        case "5":
            $("#finance_amount").val(remaining.toFixed(2));
            break;

        case "6":
            $("#credit_amount").val(remaining.toFixed(2));
            break;

        case "7":
            $("#cheque_amount").val(remaining.toFixed(2));
            break;

        default:
            break;
    }
});

$(document).ready(function () {

    $("#cash_add").on("click", function (e) {
        e.preventDefault();
        cash_add();
    });

    $("#card_add").on("click", function (e) {
        e.preventDefault();
        card_add();
    });

    $("#finance_add").on("click", function (e) {
        e.preventDefault();
        finance_add();
    });

    $("#exchange_add").on("click", function (e) {
        e.preventDefault();
        exchange_add();
    });

    $("#credit_add").on("click", function (e) {
        e.preventDefault();
        credit_add();
    });

    $("#cheque_add").on("click", function (e) {
        e.preventDefault();
        cheque_add();
    });

    $("#upi_add").on("click", function (e) {
        e.preventDefault();
        upi_add();
    });

});

function cash_add() {

    let cash_amount = $("#cash_amount").val().trim();

    if (
        cash_amount === "" ||
        isNaN(cash_amount) ||
        parseFloat(cash_amount) <= 0
    ) {
        showPaymentError("Amount is required");
        return;
    }

    cash_amount = parseFloat(cash_amount);

    let remaining = getRemainingPayment();

    if (cash_amount > remaining) {
        showPaymentError(
            "Amount cannot be greater than remaining amount ₹" +
            remaining.toFixed(2)
        );
        return;
    }

    appendPaymentRow("Cash", cash_amount);

    $("#cash_amount").val("");
    $("#amount_fill").prop("checked", false);
}


function card_add() {

    let card_number = $("#card_number").val().trim();
    let card_name = $("#card_name").val().trim();
    let card_amount = $("#card_amount").val().trim();

    if (card_name === "") {
        showPaymentError("Card name cannot be empty");
        return;
    }

    if (
        card_amount === "" ||
        isNaN(card_amount) ||
        parseFloat(card_amount) <= 0
    ) {
        showPaymentError("Please enter a valid positive amount");
        return;
    }

    card_amount = parseFloat(card_amount);

    if (card_number !== "" && !/^\d{13,19}$/.test(card_number)) {
        showPaymentError("Invalid card number (must be 13–19 digits)");
        return;
    }

    let remaining = getRemainingPayment();

    if (card_amount > remaining) {
        showPaymentError(
            "Amount cannot be greater than remaining amount ₹" +
            remaining.toFixed(2)
        );
        return;
    }

    appendPaymentRow("Card", card_amount, {
        card_name: card_name,
        card_number: card_number
    });

    $("#card_number, #card_name, #card_amount").val("");
    $("#card_fill").prop("checked", false);
}



function finance_add() {

    let finance_card = $("#finance_card").val().trim();
    let finance_type = $("#finance_type").val().trim();
    let finance_amount = $("#finance_amount").val().trim();

    if (
        finance_type === "" ||
        finance_amount === "" ||
        isNaN(finance_amount) ||
        parseFloat(finance_amount) <= 0
    ) {
        showPaymentError("Invalid Input");
        return;
    }

    finance_amount = parseFloat(finance_amount);

    if (
        finance_card !== "" &&
        !/^\d{8,}$/.test(finance_card)
    ) {
        showPaymentError(
            "Invalid Finance Card Number (min 8 digits)"
        );
        return;
    }

    let finance_type_text =
        $("#finance_type option:selected").text();

    let remaining = getRemainingPayment();

    if (finance_amount > remaining) {
        showPaymentError(
            "Amount cannot be greater than remaining amount ₹" +
            remaining.toFixed(2)
        );
        return;
    }

    appendPaymentRow("Finance", finance_amount, {
        finance_type: finance_type,
        finance_type_name: finance_type_text,
        finance_card: finance_card
    });

    $("#finance_card, #finance_type, #finance_amount").val("");
    $("#finance_fill").prop("checked", false);
}



function exchange_add() {

    let exchange_amount = $("#exchange_amount").val().trim();

    if (
        exchange_amount === "" ||
        isNaN(exchange_amount) ||
        parseFloat(exchange_amount) <= 0
    ) {
        showPaymentError("Invalid Input");
        return;
    }

    exchange_amount = parseFloat(exchange_amount);

    let remaining = getRemainingPayment();

    if (exchange_amount > remaining) {
        showPaymentError(
            "Amount cannot be greater than remaining amount ₹" +
            remaining.toFixed(2)
        );
        return;
    }

    appendPaymentRow("Exchange", exchange_amount);

    $("#exchange_amount").val("");
    $("#exchange_fill").prop("checked", false);
}


function credit_add() {

    let credit_amount = $("#credit_amount").val().trim();

    if (
        credit_amount === "" ||
        isNaN(credit_amount) ||
        parseFloat(credit_amount) <= 0
    ) {
        showPaymentError("Invalid Input");
        return;
    }

    credit_amount = parseFloat(credit_amount);

    let remaining = getRemainingPayment();

    if (credit_amount > remaining) {
        showPaymentError(
            "Amount cannot be greater than remaining amount ₹" +
            remaining.toFixed(2)
        );
        return;
    }

    appendPaymentRow("Credit", credit_amount);

    $("#credit_amount").val("");
    $("#credit_fill").prop("checked", false);
}


function cheque_add() {

    let cheque_number = $("#cheque_number").val().trim();
    let cheque_amount = $("#cheque_amount").val().trim();

    if (
        cheque_number === "" ||
        cheque_amount === "" ||
        isNaN(cheque_amount) ||
        parseFloat(cheque_amount) <= 0
    ) {
        showPaymentError("Invalid Input");
        return;
    }

    cheque_amount = parseFloat(cheque_amount);

    if (!/^\d{6,}$/.test(cheque_number)) {
        showPaymentError(
            "Invalid Cheque Number (min 6 digits)"
        );
        return;
    }

    let remaining = getRemainingPayment();

    if (cheque_amount > remaining) {
        showPaymentError(
            "Amount cannot be greater than remaining amount ₹" +
            remaining.toFixed(2)
        );
        return;
    }

    appendPaymentRow("Cheque", cheque_amount, {
        cheque_number: cheque_number
    });

    $("#cheque_number, #cheque_amount").val("");
    $("#cheque_fill").prop("checked", false);
}


function upi_add() {

    let upi_amount = $("#upi_amount").val().trim();

    if (
        upi_amount === "" ||
        isNaN(upi_amount) ||
        parseFloat(upi_amount) <= 0
    ) {
        showPaymentError("Invalid Input");
        return;
    }

    upi_amount = parseFloat(upi_amount);

    let remaining = getRemainingPayment();

    if (upi_amount > remaining) {
        showPaymentError(
            "Amount cannot be greater than remaining amount ₹" +
            remaining.toFixed(2)
        );
        return;
    }

    appendPaymentRow("UPI", upi_amount);

    $("#upi_amount").val("");
    $("#upi_fill").prop("checked", false);
}

function showPaymentError(message) {
    const event = new CustomEvent("toast", {
        detail: {
            text: message,
            gravity: "top",
            position: "right",
            className: "success",
            duration: 5000,
            close: true,
        }
    });

    document.dispatchEvent(event);
}

$(document).on('change', '.product-status', function () {

    let select = $(this);
    let detailId = select.data('detail-id');
    let status = select.val();

    if (status === "") {
        return;
    }

    $.ajax({
        url: "{{ route('order.online.detail.status.update', ['company' =>  request()->route('company')]) }}",
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            detail_id: detailId,
            status: status
        },

        success: function (response) {

            if (response.success) {

                const event = new CustomEvent("toast", {
                    detail: {
                        text: response.message,
                        gravity: "top",
                        position: "right",
                        className: "success",
                        duration: 5000,
                        close: true,
                    }
                });

                document.dispatchEvent(event);

            }
        },

        error: function (xhr) {

            let message = "Unable to update order status.";

            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            const event = new CustomEvent("toast", {
                detail: {
                    text: message,
                    gravity: "top",
                    position: "right",
                    className: "error",
                    duration: 5000,
                    close: true,
                }
            });

            document.dispatchEvent(event);

            // Restore previous value if update failed
            location.reload();
        }
    });
});

$("#orderUpdateForm").on("submit", function (e) {

    let paymentData = [];

    $("#payment-info-body tr").each(function () {

        let method = $(this).data("method");

        let amt = parseFloat(
            $(this).find("td").eq(1).text().replace(/[₹,]/g, "")
        ) || 0;

        let extra = {};

        let extraAttr = $(this).attr("data-extra");

        if (extraAttr) {
            try {
                extra = JSON.parse(extraAttr);
            } catch (e) {
                extra = {};
            }
        }

        paymentData.push({
            method: method,
            amount: amt,
            extra: extra
        });
    });

    // Put payment data into hidden input
    $("#payments").val(JSON.stringify(paymentData));
});

$("#status").on("change", function () {

    let status = $(this).val();
    let orderId = $(this).data("order-id");

    if (!status) {
        return;
    }

    $.ajax({
        url: "{{ route('order.online.status.update', ['company' =>  request()->route('company')]) }}",

        type: "POST",

        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            id: orderId,
            status: status
        },

        success: function (response) {

            if (response.status === "success") {

                const event = new CustomEvent("toast", {
                    detail: {
                        text: response.message,
                        gravity: "top",
                        position: "right",
                        className: "success",
                        duration: 3000,
                        close: true
                    }
                });

                document.dispatchEvent(event);
            }
        },

        error: function (xhr) {

            let message = "Something went wrong.";

            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            const event = new CustomEvent("toast", {
                detail: {
                    text: message,
                    gravity: "top",
                    position: "right",
                    className: "error",
                    duration: 5000,
                    close: true
                }
            });

            document.dispatchEvent(event);
        }
    });
});
</script>

@endsection

