@extends('layouts.master')

@section('title')
    <title>{{ config('app.name')}} | Edit Bill</title>
@endsection

@section('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .select2-container .select2-selection--single { height: 38px; border: 1px solid #ced4da; border-radius: 4px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; color: #495057; padding-left: 10px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
</style>
@endsection

@section('body')
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="vendor-store-url" content="{{ route('vendor.store', ['company' => request()->route('company')]) }}">
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
    <div class="col-xl-12 col-md-12">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="card-title">Edit Bill</h4>
            </div>
            <form class="row" action="{{route('setting.order.bill.update', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                <div class="card-body">
                    <div class="row border-bottom pb-3 mb-4">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label text-muted d-flex justify-content-between align-items-center">
                                    <span>
                                        Invoice No  : {{$order->bill_id}}
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">

                                <label class="form-label text-muted">
                                    Date
                                    <a href="javascript:void(0)" id="editDate">
                                        <i class="ri-edit-line ms-1"></i>
                                    </a>
                                </label>

                                <div id="dateView">
                                    {{ \Carbon\Carbon::parse($order->billed_on)->format('d-m-Y') }}
                                </div>

                                <input type="date"
                                       id="billed_date"
                                       name="billed_date"
                                       class="form-control d-none"
                                       value="{{ \Carbon\Carbon::parse($order->billed_on)->format('Y-m-d') }}">

                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">

                                <label class="form-label text-muted">
                                    Time
                                    <a href="javascript:void(0)" id="editTime">
                                        <i class="ri-edit-line ms-1"></i>
                                    </a>
                                </label>

                                <div id="timeView">
                                    {{ \Carbon\Carbon::parse($order->billed_on)->format('h:i A') }}
                                </div>

                                <input type="time" id="billed_time" name="billed_time" class="form-control d-none" value="{{ \Carbon\Carbon::parse($order->billed_on)->format('H:i') }}">

                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label text-muted d-flex justify-content-between align-items-center">
                                    <span>
                                        Payment  : {{ $order_payment_details->map(fn($o) => $o->payment->name)->join(', ') }}
                                    </span>
                                </label>
                            </div>
                        </div>
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

                            <!-- Sales Details -->
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Sales Details</h5>
                                    </div>
                                    <div class="card-body">

                                        <div class="row mb-2">
                                            <div class="col-5 fw-semibold">Sales Person</div>
                                            <div class="col-7">{{ $order->billedBy->name }}</div>
                                        </div>

                                        @foreach($order_payment_details as $opd)
                                            <div class="row mb-2">
                                                <div class="col-5 fw-semibold">
                                                    {{ $opd->payment->name }}
                                                </div>
                                                <div class="col-7">
                                                    ₹ {{ number_format($opd->amount, 2) }}
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header d-flex align-items-center">
                            <h5 class="mb-0">Products</h5>

                            <button
                                type="button"
                                class="btn btn-primary ms-auto"
                                id="addProduct">
                                <i class="fa fa-plus me-1"></i> Add Product
                            </button>
                        </div>

                            <div class="card-body">

                                <table class="table table-bordered" id="productTable">
                                    <thead>
                                        <tr>
                                            <th width="35%">Product</th>
                                            <th width="10%">Qty</th>
                                            <th width="15%">Price</th>
                                            <th width="10%">Tax</th>
                                            <th width="15%">Total</th>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach($order_details as $detail)

                                        <tr>
                                            <td>

                                                <input type="hidden" name="product_id[]" value="{{ $detail->product_id }}">

                                                <strong class="d-block">{{ $detail->name }}</strong>

                                                <small class="text-muted d-block">
                                                    {{ $detail->product->category->name }}
                                                    &raquo;
                                                    {{ $detail->product->sub_category->name }}
                                                </small>

                                                @if($detail->size_id)
                                                    <br>
                                                    Size :
                                                    {{ optional(App\Models\Size::find($detail->size_id))->name }}
                                                @endif

                                                @if($detail->colour_id)
                                                    <br>
                                                    Colour :
                                                    {{ optional(App\Models\Colour::find($detail->colour_id))->name }}
                                                @endif

                                            </td>

                                            <td>

                                                @php
                                                    $stock = App\Models\Stock::where([
                                                        'shop_id'   => $order->shop_id,
                                                        'branch_id' => $order->branch_id,
                                                        'product_id'=> $detail->product_id,
                                                        'is_active' => 1,
                                                    ])->first();

                                                    // Total quantity reserved in queue
                                                    $queueStock = App\Models\QueueStock::where([
                                                            'product_id' => $detail->product_id,
                                                            'from'       => $order->branch_id,
                                                            'status'     => 0,
                                                        ])
                                                        ->sum('quantity');

                                                    $availableStock = $stock->quantity ?? 0;

                                                    // Actual free stock after deducting queued stock
                                                    $freeStock = max(0, $availableStock - $queueStock);

                                                    // Maximum editable quantity
                                                    $maxQty = $freeStock + $detail->quantity;
                                                @endphp

                                                <input type="number" name="qty[]" class="form-control qty" value="{{ $detail->quantity }}" min="1" max="{{ $maxQty }}" data-stock="{{ $availableStock }}" data-queue="{{ $queueStock }}" data-billed="{{ $detail->quantity }}">

                                                <div class="mt-1" style="font-size:11px;">
                                                    <span class="badge bg-secondary">B {{ $detail->quantity }}</span>
                                                    <span class="badge bg-info">S {{ $availableStock }}</span>
                                                    <span class="badge bg-warning text-dark">Q {{ $queueStock }}</span>
                                                    <span class="badge bg-success">F {{ $freeStock }}</span>
                                                </div>

                                            </td>

                                                <td>
                                                    <input type="number" name="price[]" class="form-control price" value="{{ $detail->product->discounted_price }}" step="0.01" min="0">
                                                </td>

                                            <td>

                                                {{ $detail->tax_percent }}%

                                            </td>

                                            <td class="lineTotal">

                                                {{ number_format($detail->selling_price,2) }}

                                            </td>

                                            <td>

                                                <button type="button" class="btn btn-danger btn-sm removeProduct"> Remove </button>
                                            </td>
                                        </tr>

                                        @endforeach
                                    </tbody>
                                </table>

                                <table class="d-none">
                                    <tr id="productTemplate">

                                        <td>
                                            <div class="row g-1">
                                                <div class="col-md-4">
                                                    <select class="form-control category">
                                                        <option value="">Category</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <select class="form-control sub_category">
                                                        <option value="">Sub Category</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <select class="form-control product">
                                                        <option value="">Product</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <input type="number" name="qty[]" class="form-control qty" value="1" min="1" max="0" data-stock="0" data-queue="0" data-billed="0">

                                            <small class="text-muted stock-info" style="font-size:11px;"></small>
                                        </td>

                                        <td>
                                            <input type="number" name="price[]" class="form-control price" value="0" step="0.01" min="0">
                                        </td>

                                        <td>
                                            <span class="taxCell">0</span>%
                                        </td>

                                        <td class="lineTotal">
                                            0.00
                                        </td>

                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm removeProduct"> Remove </button>
                                        </td>

                                    </tr>
                                </table>

                                <div class="row justify-content-end g-2">
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100" id="billUpdate"><i class="ri-save-line"></i> 
                                        Update</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const shopId = "{{ $order->shop_id }}";
    const branchId = "{{ $order->branch_id }}";
</script>

<script>
    $('#addProduct').click(function(){

        let row = $('#productTemplate').clone();

        row.removeAttr('id');

        row.removeClass('d-none');

        $('#productTable tbody').append(row);

        row.find('.productSelect').select2();

    });

    $(document).on('click','.removeProduct',function(){

        $(this).closest('tr').remove();

        calculateBill();

    });

    $(document).on('keyup change','.qty',function(){

        calculateBill();

    });

    $(document).on('change', '.category', function () {

        let row = $(this).closest('tr');

        $.ajax({
            url: "{{ route('setting.order.bill.get_sub_categories', request()->route('company')) }}",
            type: "GET",
            data: {
                shop_id: shopId,
                branch_id: branchId,
                category_id: $(this).val()
            },
            success: function (data) {

                let html = '<option value="">Select Sub Category</option>';

                $.each(data, function (i, item) {
                    html += '<option value="' + item.id + '">' + item.name + '</option>';
                });

                row.find('.sub_category').html(html);
                row.find('.product').html('<option value="">Select Product</option>');
            }
        });

    });

    $(document).on('change', '.sub_category', function () {

        let row = $(this).closest('tr');

        $.ajax({
            url: "{{ route('setting.order.bill.get_products', request()->route('company')) }}",
            type: "GET",
            data: {
                shop_id: shopId,
                branch_id: branchId,
                category_id: row.find('.category').val(),
                sub_category_id: $(this).val()
            },
            success: function (data) {

                let html = '<option value="">Select Product</option>';

                $.each(data, function (i, item) {
                    html += '<option value="' + item.id + '">' + item.name + '</option>';
                });

                row.find('.product').html(html);
            }
        });

    });



    $(document).on('change', '.product', function () {

        let row = $(this).closest('tr');

        $.ajax({
            url: "{{ route('setting.order.bill.get_product_detail', request()->route('company')) }}",
            type: "GET",
            data: {
                shop_id: shopId,
                branch_id: branchId,
                product: $(this).val()
            },
            success:function(data){

                let price = parseFloat(data.price);

                row.find('.price').val(price.toFixed(2));

                row.find('.taxCell').text(data.tax);

                row.find('.qty')
                .attr('max', data.free)
                .data('stock', data.stock)
                .data('queue', data.queue)
                .data('billed', 0);

                row.find('.stock-info').html(
                    '<span class="badge bg-secondary">B 0</span> ' +
                    '<span class="badge bg-info">S '+data.stock+'</span> ' +
                    '<span class="badge bg-warning text-dark">Q '+data.queue+'</span> ' +
                    '<span class="badge bg-success">F '+data.free+'</span>'
                );

                calculateBill();
            }
        });
    });

    function calculateBill(){

        let grand = 0;

        $('#productTable tbody tr').each(function(){

            let qty = parseFloat($(this).find('.qty').val()) || 0;
            let price = parseFloat($(this).find('.price').val()) || 0;

            let total = qty * price;

            $(this).find('.lineTotal').html(total.toFixed(2));

            grand += total;
        });

        $('#grandTotal').html(grand.toFixed(2));
    }

    $(document).on('input', '.price', function () {
        calculateBill();
    });

    $(document).on('input', '.qty', function () {

        let billed = parseInt($(this).data('billed')) || 0;
        let stock = parseInt($(this).data('stock')) || 0;
        let queue = parseInt($(this).data('queue')) || 0;

        let maxQty = billed + stock + queue;

        if (parseInt($(this).val()) > maxQty) {
            alert('Maximum allowed quantity is ' + maxQty);
            $(this).val(maxQty);
        }
    });

    $('#editDate').click(function () {
        $('#dateView').addClass('d-none');
        $('#billed_date').removeClass('d-none').focus();
    });

    $('#editTime').click(function () {
        $('#timeView').addClass('d-none');
        $('#billed_time').removeClass('d-none').focus();
    });

</script>
@endsection
