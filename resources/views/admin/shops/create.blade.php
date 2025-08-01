@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Shop Create</title>
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
            <form class="row" action="{{route('admin.shop.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-header pb-0">
                        <h4 class="card-title">Add New Shop</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-12 col-md-12 mb-3">
                                <label for="name" class="form-label">Upload Shop Logo</label>
                                <div class="input-group">
                                    <input type="file" name="logo" id="logo" class="form-control">
                                </div>
                                <div class="alert bg-soft-success m-2 secret" id="fileNameDisplay" role="alert"></div>
                            </div>
                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Shop Name</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="name" id="name" value="{{old('name')}}" class="form-control" placeholder="Enter Name">
                                </div>
                                
                            </div>
                            
                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Mobile Number</label>
                                    <span class="text-danger">*</span>
                                    <input type="tel" name="phone" id="phone" value="{{old('phone')}}"  class="form-control" placeholder="Enter Mobile Number" maxlength="10" pattern="[0-9]{10}" inputmode="numeric">
                                </div>
                                
                            </div>
                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="phone1" class="form-label">Alternate Mobile Number</label>
                                    <input type="tel" name="phone1" id="phone1" class="form-control" value="{{old('phone1')}}" placeholder="Enter Alternate Mobile Number"  maxlength="10" pattern="[0-9]{10}" inputmode="numeric">
                                </div>
                                
                            </div>
                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" id="address" name="address" value="{{old('address')}}" class="form-control" placeholder="Enter Address">
                                </div>
                                
                            </div>
                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="email" value="{{old('email')}}" class="form-control" placeholder="Enter Email">
                                </div>
                                
                            </div>

                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="slug_name" class="form-label">Slug Name</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" id="slug_name" name="slug_name" value="{{old('slug_name')}}" class="form-control" placeholder="Enter Slug Name">
                                </div>
                                
                            </div>

                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <span class="text-danger">*</span>
                                    <input type="password" id="password" name="password" value="{{old('password')}}" class="form-control" placeholder="Enter Password">
                                </div>
                                
                            </div>

                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <span class="text-danger">*</span>
                                    <input type="password" name="password_confirmation" id="password_confirmation" value="{{old('password_confirmation')}}" class="form-control" placeholder="Confirm Password">
                                </div>
                                
                            </div>

                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="gst" class="form-label">Company GSTin</label>
                                    <input type="text" id="gst" name="gst" class="form-control" placeholder="Enter Gst" value="{{old('email')}}">
                                </div>
                                
                            </div>

                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="primary_colour" class="form-label">Primary Color</label>
                                    <input type="color" id="primary_colour" name="primary_colour" value="{{ old('primary_colour', '#000000') }}" class="form-control">
                                </div>

                                
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="secondary_colour" class="form-label">Secondary Color</label>
                                    <input type="color" id="secondary_colour" name="secondary_colour" value="{{old('secondary_colour')}}" class="form-control" placeholder="Enter Secondary Color code">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="secondary_colour" class="form-label">Payment Method</label>
                                    <select class="form-control" data-choices name="payment_method" id="payment_method">
                                        <option value=""> Choose Payment</option>
                                        <option value="1">Monthly</option>
                                        <option value="2">Quarterly</option>
                                        <option value="3">Semi-Yearly</option>
                                        <option value="4">Yearly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header pb-0">
                        <h4 class="card-title">Bank details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                           
                            <div class="col-md-4">
                                
                                    <div class="mb-3">
                                        <label for="bank" class="form-label">Bank Name</label>
                                        <input type="text" id="bank" name="bank" class="form-control" placeholder="Enter Bank Name" value="{{old('bank')}}">
                                    </div>
                                
                            </div>
                            <div class="col-md-4">
                                
                                    <div class="mb-3">
                                        <label for="bank" class="form-label">Account Holder Name</label>
                                        <input type="text" id="holder_name" name="holder_name" class="form-control" placeholder="Enter Account Holder Name" value="{{old('holder_name')}}">
                                    </div>
                                
                            </div>
                            <div class="col-md-4">
                                
                                    <div class="mb-3">
                                        <label for="account_number" class="form-label">Enter A/C No</label>
                                        <input type="text" id="account_number" name="account_number" class="form-control" placeholder="Enter Account Number" inputmode="numeric" pattern="[0-9]*" maxlength="16" value="{{old('account_number')}}">
                                    </div>
                                
                            </div>
                            <div class="col-md-4">
                                
                                    <div class="mb-3">
                                        <label for="category-name" class="form-label">Confirm A/C No </label>
                                        <input type="text" id="confirm_account_number" name="confirm_account_number" class="form-control" placeholder="Confirm Account Number" inputmode="numeric" pattern="[0-9]*" maxlength="16" value="{{old('confirm_account_number')}}">
                                    </div>
                                
                            </div>
                            <div class="col-md-4">
                                
                                    <div class="mb-3">
                                        <label for="branch" class="form-label">Branch</label>
                                        <input type="text" id="branch" name="branch" class="form-control" placeholder="Enter Branch" value="{{old('branch')}}">
                                    </div>
                                
                            </div>
                            <div class="col-md-4">
                                
                                    <div class="mb-3">
                                        <label for="ifsc_code" class="form-label">IFSC Code</label>
                                        <input type="text" id="ifsc_code" name="ifsc_code" class="form-control" value="{{old('ifsc_code')}}" placeholder="Enter IFSC Code">
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
                            <a href="{{route('admin.shop.index')}}" class="btn btn-outline-secondary w-100"><i class="ri-close-circle-line"></i> Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection