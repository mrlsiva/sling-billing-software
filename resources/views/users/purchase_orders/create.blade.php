@extends('layouts.master')

@section('title')
    <title>{{ config('app.name')}} | Purchase Order</title>
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
    <div class="col-xl-12 col-md-12">
        <form class="row" action="{{ route('vendor.purchase_order.store', ['company' => request()->route('company')]) }}" method="post" enctype="multipart/form-data" id="purchaseOrderCreate">
            @csrf
            <div class="card">
                <div class="card-header pb-0">
                    <h4 class="card-title">Purchase Order - Multiple Items</h4>
                </div>
                <div class="card-body">
                    <!-- Purchase Order Header Information -->
                    <div class="row border-bottom pb-3 mb-4">
                        <div class="col-md-12">
                            <h5 class="text-primary">Purchase Order Details</h5>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label text-muted">Vendor <span class="text-danger">*</span></label>
                                <select class="form-control" name="vendor" id="vendor" required="">
                                    <option value=""> Select </option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Invoice No</label>
                                <input type="text" name="invoice" id="invoice" value="{{old('invoice')}}" class="form-control" placeholder="Enter Invoice No">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                <input type="date" name="invoice_date" id="invoice_date" value="{{old('invoice_date')}}" class="form-control" required="">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" id="due_date" value="{{old('due_date')}}" class="form-control">
                            </div>
                        </div>

                        <!-- <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select class="form-control" name="payment" id="payment">
                                    <option value=""> Select </option>
                                    @foreach($payments as $payment)
                                        <option value="{{$payment->id}}">{{$payment->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> -->
                    </div>

                    <!-- Products Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-primary">Products</h5>
                                <button type="button" class="btn btn-success btn-sm" id="addProductRow">
                                    <i class="ri-add-line"></i> Add Product
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Product Items Container -->
                    <div id="productsContainer"></div>

                    <!-- Purchase Order Summary -->
                    <div class="row mt-4 border-top pt-3">
                        <div class="col-md-12">
                            <h5 class="text-primary">Order Summary</h5>
                        </div>
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-semibold">Total Net Cost:</td>
                                        <td class="text-end fw-bold" id="totalNetCost">0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Total Tax:</td>
                                        <td class="text-end fw-bold" id="totalTax">0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Total Discount:</td>
                                        <td class="text-end fw-bold" id="totalDiscount">0.00</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="fw-bold fs-5">Grand Total:</td>
                                        <td class="text-end fw-bold fs-5 text-primary" id="grandTotal">0.00</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 mb-3 rounded">
                <div class="row justify-content-end g-2">
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100"><i class="ri-save-line"></i> Create Purchase Order</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('vendor.purchase_order.index', ['company' => request()->route('company')]) }}" class="btn btn-outline-secondary w-100"><i class="ri-close-circle-line"></i> Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Product Row Template -->
<div id="productRowTemplate" style="display:none;">
    <div class="product-row border rounded p-3 mb-3" data-row-index="0">
        <div class="row">
            <div class="col-md-12 mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="text-muted mb-0">Product #<span class="product-number">1</span></h6>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-product-row">
                        <i class="ri-delete-bin-line"></i> Remove
                    </button>
                </div>
            </div>

            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label text-muted">Category <span class="text-danger">*</span></label>
                    <select class="form-control category-select" name="products[0][category]" required>
                        <option value=""> Select </option>
                        @foreach($categories as $category)
                            <option value="{{$category->id}}">{{$category->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label text-muted">Sub Category <span class="text-danger">*</span></label>
                    <select class="form-control sub-category-select" name="products[0][sub_category]" required>
                        <option value=""> Select </option>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label text-muted">Product <span class="text-danger">*</span></label>
                    <select class="form-control product-select" name="products[0][product]" required>
                        <option value=""> Select </option>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Quantity <span class="metric-display"></span> <span class="text-danger">*</span></label>
                    <input type="number" name="products[0][quantity]" class="form-control quantity-input" placeholder="Qty" min="1" step="1" required>
                    <input type="hidden" name="products[0][unit]" class="unit-input">
                </div>
            </div>

            <div class="col-md-2">
                <div class="mb-3">
                    <label class="form-label">Price/Unit <span class="text-danger">*</span></label>
                    <input type="number" name="products[0][price_per_unit]" class="form-control price-input" placeholder="Price" min="0.01" step="0.01" required>
                </div>
            </div>

            <div class="col-md-2">
                <div class="mb-3">
                    <label class="form-label">Tax (%)</label>
                    <select class="form-control tax-input" name="products[0][tax]">
                        <option value="0"> No Tax </option>
                        @foreach($taxes as $tax)
                            <option value="{{$tax->name}}"> {{$tax->name}}% </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <div class="mb-3">
                    <label class="form-label">Discount</label>
                    <input type="number" name="products[0][discount]" class="form-control discount-input" placeholder="Discount" min="0" step="0.01">
                </div>
            </div>

            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Net Cost</label>
                    <input type="number" name="products[0][net_cost]" class="form-control net-cost-input" readonly>
                </div>
            </div>

            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Gross Cost</label>
                    <input type="number" name="products[0][gross_cost]" class="form-control gross-cost-input" readonly>
                </div>
            </div>

            <div class="col-md-12">
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <input type="checkbox" class="form-check-input me-2 enable-imei-checkbox" id="enableImei[0]">
                        <label class="form-check-label" for="enableImei[0]">Enable IMEI Numbers</label>
                    </div>
                    <div class="imei-container row" style="display: none;">
                        <!-- IMEI inputs will be generated here based on quantity -->
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{asset('assets/js/users/purchase.js')}}"></script>
@endsection
