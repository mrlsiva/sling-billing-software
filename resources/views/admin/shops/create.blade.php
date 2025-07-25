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
                                    <input type="file" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Shop Name</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" id="name" value="{{old('name')}}" class="form-control" placeholder="Enter Name">
                                </div>
                                
                            </div>
                            
                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Mobile Number</label>
                                    <span class="text-danger">*</span>
                                    <input type="tel" id="phone" value="{{old('phone')}}" class="form-control" placeholder="Enter Mobile Number" maxlength="10" pattern="[0-9]{10}" inputmode="numeric">
                                </div>
                                
                            </div>
                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="phone1" class="form-label">Alternate Mobile Number</label>
                                    <input type="tel" id="phone1" class="form-control" value="{{old('phone1')}}" placeholder="Enter Alternate Mobile Number"  maxlength="10" pattern="[0-9]{10}" inputmode="numeric">
                                </div>
                                
                            </div>
                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" id="address" name="address" value="{{old('address')}}" class="form-control" placeholder="Enter Address">
                                </div>
                                
                            </div>
                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" value="{{old('email')}}" class="form-control" placeholder="Enter Email">
                                </div>
                                
                            </div>

                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="slug_name" class="form-label">Slug Name</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" id="slug_name" value="{{old('slug_name')}}" class="form-control" placeholder="Enter Slug Name">
                                </div>
                                
                            </div>

                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <span class="text-danger">*</span>
                                    <input type="password" id="password" value="{{old('password')}}" class="form-control" placeholder="Enter Password">
                                </div>
                                
                            </div>

                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <span class="text-danger">*</span>
                                    <input type="password" name="confirm_password" id="confirm_password" value="{{old('confirm_password')}}" class="form-control" placeholder="Confirm Password">
                                </div>
                                
                            </div>

                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="gst" class="form-label">Company GSTin</label>
                                    <span class="text-danger">*</span>
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