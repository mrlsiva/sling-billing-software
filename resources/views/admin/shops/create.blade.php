@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Shop Create</title>
@endsection

@section('body')
     <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h4 class="card-title">Add New Shop</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 mb-3">
                            <label for="category-name" class="form-label">Upload Shop Logo</label>
                            <div class="dropzone bg-light-subtle ">
                                <div class="fallback">
                                    <input name="file" type="file" multiple="multiple">
                                </div>
                                <div class="dz-message needsclick">
                                    <i class="bx bx-cloud-upload fs-48 text-primary"></i>
                                    <h3 class="mt-4">Drop your images here, or <span class="text-primary">click to browse</span></h3>
                                    <span class="text-muted fs-13">
                                        1600 x 1200 (4:3) recommended. PNG, JPG and GIF files are allowed
                                    </span>
                                </div>
                            </div>

                            <ul class="list-unstyled mb-0" id="dropzone-preview">
                                    <li class="mt-2" id="dropzone-preview-list">
                                        <!-- This is used as the file preview template -->
                                        <div class="border rounded">
                                            <div class="d-flex p-2">
                                                <div class="flex-shrink-0 me-3">
                                                        <div class="avatar-sm bg-light rounded">
                                                            <img data-dz-thumbnail class="img-fluid rounded d-block" src="#" alt="Dropzone-Image" />
                                                        </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                        <div class="pt-1">
                                                            <h5 class="fs-14 mb-1" data-dz-name>&</h5>
                                                            <p class="fs-13 text-muted mb-0" data-dz-size></p>
                                                            <strong class="error text-primary" data-dz-errormessage></strong>
                                                        </div>
                                                </div>
                                                <div class="flex-shrink-0 ms-3">
                                                        <button data-dz-remove class="btn btn-sm btn-primary">Delete</button>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                            </ul>
                        </div>
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="category-name" class="form-label">Shop Name</label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Name">
                                </div>
                            </form>
                        </div>
                        
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="category-name" class="form-label">Mobile Number 1</label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Name">
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="category-name" class="form-label">Mobile Number 2</label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Name">
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="category-name" class="form-label">Address</label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Name">
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="category-name" class="form-label">Email</label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Name">
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="category-name" class="form-label">Company GSTin</label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Name">
                                </div>
                            </form>
                        </div>
                        
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="product-stock" class="form-label">Primary Color</label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Color code">
                                </div>

                            </form>
                        </div>
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="product-stock" class="form-label">Secondary Color</label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Color code">
                                </div>

                            </form>
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
                       
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="category-name" class="form-label">Bank Name</label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Name">
                                </div>
                            </form>
                        </div>
                        
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="category-name" class="form-label">Enter A/C No</label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Name">
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="category-name" class="form-label">Confirm A/C No </label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Name">
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="category-name" class="form-label">Branch</label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Name">
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-4">
                            <form>
                                <div class="mb-3">
                                    <label for="category-name" class="form-label">IFSC Code</label>
                                    <input type="text" id="category-name" class="form-control" placeholder="Enter Name">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 mb-3 rounded">
                <div class="row justify-content-end g-2">
                    <div class="col-lg-3">
                            <a href="#!" class="btn btn-outline-secondary w-100"><i class="ri-save-line"></i> Save Change</a>
                    </div>
                    <div class="col-lg-2">
                            <a href="#!" class="btn btn-primary w-100"><i class="ri-close-circle-line"></i> Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection