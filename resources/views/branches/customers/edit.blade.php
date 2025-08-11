@extends('layouts.master')

@section('title')
    <title>{{ config('app.name')}} | Customer Edit</title>
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
            <form class="row" action="{{route('branch.customer.update', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-header pb-0">
                        <h4 class="card-title">Update Customer</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <input type="hidden" name="id" value="{{$user->id}}">

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="name" id="name" value="{{$user->name}}" class="form-control" placeholder="Enter Name">
                                </div>  
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <span class="text-danger">*</span>
                                    <input type="tel" name="phone" id="phone" value="{{$user->phone}}"  class="form-control" placeholder="Enter Phone Number" maxlength="10" pattern="[0-9]{10}" inputmode="numeric">
                                </div>
                            </div>

                            <div class="col-md-4">
                                
                                <div class="mb-3">
                                    <label for="alt-phone" class="form-label">Alternate Mobile Number</label>
                                    <input type="tel" name="alt_phone" id="alt_phone" value="{{$user->alt_phone}}"  class="form-control" placeholder="Enter Alternate Phone Number" maxlength="10" pattern="[0-9]{10}" inputmode="numeric">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" id="address" name="address" value="{{$user->address}}" class="form-control" placeholder="Enter Address">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Pincode</label>
                                    <input type="text" id="pincode" name="pincode" value="{{$user->pincode}}" class="form-control" placeholder="Enter Pincode">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="choices-single-groups" class="form-label text-muted">Gender</label>
                                    <select class="form-control" name="gender" id="gender">
                                        <option value=""> Select </option>
                                        @foreach($genders as $gender)
                                            <option value="{{$gender->id}}" {{$user->gender_id == $gender->id ? 'selected' : '' }}>{{$gender->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="date" class="form-label">DOB</label>
                                    <input type="date" name="dob" id="dob" value="{{$user->dob}}" class="form-control" placeholder="Enter DOB" max="{{ date('Y-m-d') }}">
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
                            <a href="{{route('branch.customer.index', ['company' => request()->route('company')])}}" class="btn btn-outline-secondary w-100"><i class="ri-close-circle-line"></i> Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection