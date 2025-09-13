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
            <form class="row" action="{{ route('vendor.purchase_order.store', ['company' => request()->route('company')]) }}" method="post" enctype="multipart/form-data" id="productCreate">
                @csrf
                <div class="card">
                    <div class="card-header pb-0">
                        <h4 class="card-title">Purchase Order</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="choices-single-groups" class="form-label text-muted">Vendor</label>
                                    <span class="text-danger">*</span>
                                    <select class="form-control"  name="vendor" id="vendor" required="">
                                        <option value=""> Select </option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="invoice" class="form-label">Invoice No</label>
                                    <input type="text" name="invoice" id="invoice" value="{{old('invoice')}}" class="form-control" placeholder="Enter Invoice No">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="invoice_date" class="form-label">Invoice Date</label>
                                    <input type="date" name="invoice_date" id="invoice_date" value="{{old('invoice_date')}}" class="form-control" placeholder="Enter Invoice Date" required="">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" name="due_date" id="due_date" value="{{old('due_date')}}" class="form-control" placeholder="Enter Due Date">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="choices-single-groups" class="form-label text-muted">Category</label>
                                    <span class="text-danger">*</span>
                                    <select class="form-control"  name="category" id="category" required="">
                                        <option value=""> Select </option>
                                        @foreach($categories as $category)
                                            <option value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="choices-single-groups" class="form-label text-muted">Sub Category</label>
                                    <span class="text-danger">*</span>
                                    <select class="form-control"  name="sub_category" id="sub_category" required="">
                                        <option value=""> Select </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="choices-single-groups" class="form-label text-muted">Product</label>
                                    <span class="text-danger">*</span>
                                    <select class="form-control"  name="product" id="product" required="">
                                        <option value=""> Select </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="imei" class="form-label">IMEI</label>
                                    <input type="text" name="imei" id="imei" value="{{old('imei')}}" class="form-control" placeholder="Enter IMEI">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="choices-single-groups" class="form-label text-muted">Unit</label>
                                    <span class="text-danger">*</span>
                                    <select class="form-control"  name="unit" id="unit" required="" readonly>
                                        <option value=""> Select </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <span class="text-danger">*</span>
                                    <input type="number" name="quantity" id="quantity" value="{{old('quantity')}}" class="form-control" placeholder="Enter Quantity" min="1" step="1"  required="" >
                                    <small id="quantity_error" class="text-danger d-none">Quantity must be greater than 0</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price_per_unit" class="form-label">Price Per Unit</label>
                                    <span class="text-danger">*</span>
                                    <input type="number" name="price_per_unit" id="price_per_unit" value="{{old('price_per_unit')}}" class="form-control" placeholder="Enter Price Per Unit" min="1" step="1" required="">
                                    <small id="price_error" class="text-danger d-none">Price must be greater than 0</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tax" class="form-label">Tax (In %)</label>
                                    <select class="form-control"  name="tax" id="tax">
                                        <option value=""> Select </option>
                                        @foreach($taxes as $tax)
                                            <option value="{{$tax->name}}"> {{$tax->name}} </option>
                                        @endforeach
                                    </select>
                                    <small id="tax_error" class="text-danger d-none">Tax cannot be negative</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="discount" class="form-label">Discount</label>
                                    <input type="number" name="discount" id="discount" value="{{old('discount')}}" class="form-control" placeholder="Enter Discount">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="net_cost" class="form-label">Net Cost</label>
                                    <span class="text-danger">*</span>
                                    <input type="number" name="net_cost" id="net_cost" value="{{old('net_cost')}}" class="form-control" placeholder="Enter Net Cost" required="" readonly="">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="gross_cost" class="form-label">Gross Cost</label>
                                    <span class="text-danger">*</span>
                                    <input type="number" name="gross_cost" id="gross_cost" value="{{old('gross_cost')}}" class="form-control" placeholder="Enter Gross Cost" required="" readonly="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-3 mb-3 rounded">
                    <div class="row justify-content-end g-2">
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100"><i class="ri-save-line"></i> Save Change</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('vendor.purchase_order.index', ['company' => request()->route('company')]) }}" class="btn btn-outline-secondary w-100"><i class="ri-close-circle-line"></i> Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script src="{{asset('assets/js/users/purchase.js')}}"></script>
@endsection