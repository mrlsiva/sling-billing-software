@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Shop</title>
@endsection

@section('body')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                            <p class="card-title mb-0"> <img src="{{asset('assets/images/sling-logo.png')}}" class="logo-dark me-1" alt="user-profile-image" height="24"> Vasantham</p>
                    </div>
                    <div class="d-flex gap-3">
                        <!-- <a href="{{route('admin.shop.edit')}}" class="btn btn-sm btn-primary"><i class="ri-edit-line align-middle fs-20"></i> Edit</a> -->
                        <a href="{{route('admin.shop.edit')}}" class="link-dark"><i class="ri-edit-line align-middle fs-20"></i></a>
                        <!-- <div class="dropdown">
                            <a href="#" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" aria-expanded="false">
                                Reports
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">Export</a>
                                <a href="#!" class="dropdown-item">Import</a>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
	<div class="row">
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Shop Info</h4>
                </div>
                <div class="card-body">
                    <div class="pb-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Shop Name :</h5>
                            <p class="fw-medium mb-0">Vasantham</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Phone Number :</h5>
                            <p class="fw-medium mb-0">+91 9876543210 | +91 9876543210</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Address :</h5>
                            <p class="fw-medium mb-0">Tuticorin</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Email :</h5>
                            <p class="fw-medium mb-0">shop@email.com</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Company GSTin :</h5>
                            <p class="fw-medium mb-0">DFAPM6788N</p>
                    </div>
                    <div class="pt-3">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Primary Color :</h5>
                            <p class="fw-medium mb-0">#ffffff</p>
                    </div>
                    <div class="pt-3">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Secondary Color :</h5>
                            <p class="fw-medium mb-0">#000000</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Bank Info</h4>
                    <div class="d-flex gap-3">
                        <a class="link-dark"  data-toast data-toast-text="Bank Details Copied Successfully!" data-toast-gravity="bottom" data-toast-position="center" data-toast-duration="3000" data-toast-close="close" ><i class="ri-file-copy-line align-middle fs-14"></i> Copy</a>
                    </div>
                </div>
                <div class="card-body">

                    <div class="">
                            <p class="fw-medium mb-0">Vasantham Company</p>
                            <p class="fw-medium mb-0">SBI Bank</p>
                            <p class="fw-medium mb-0">1000089328</p>
                            <p class="fw-medium mb-0">Vembar</p>
                            <p class="fw-medium mb-0">IFAB0006</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection