@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Product Create</title>
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <p class="card-title">All Product</p>
                </div>
                <a class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal"
                    data-bs-target="#categoryAdd" href=""> <i class="ri-swap-box-fill me-2"></i>Product Transfer</a>
            </div>
            <div class="card-body pt-2 ">
                <ul class="nav nav-tabs nav-justified">
                    <li class="nav-item">
                        <a href="#homeTabsJustified" data-bs-toggle="tab" aria-expanded="false" class="nav-link active">
                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
                            <span class="d-none d-sm-block"><i class="ri-shopping-cart-line me-2"></i>HO</span>

                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#messagesTabsJustified" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <span class="d-block d-sm-none"><i class="bx bx-envelope"></i></span>
                            <span class="d-none d-sm-block"><i class="ri-shopping-cart-line me-2"></i>Branch 1</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#profileTabsJustified" data-bs-toggle="tab" aria-expanded="true" class="nav-link ">
                            <span class="d-block d-sm-none"><i class="bx bx-user"></i></span>
                            <span class="d-none d-sm-block"><i class="ri-shopping-cart-line me-2"></i>Branch 2</span>
                        </a>
                    </li>

                </ul>
                <div class="tab-content pt-2 text-muted">
                    <div class="tab-pane show active" id="homeTabsJustified">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Price (₹)</th>
                                        <th>Stock at</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr>
                                        <td>
                                            -
                                        </td>
                                        <td>

                                            <img src=" " class="logo-dark me-1" alt="Product" height="30">

                                        </td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>
                                            HO
                                        </td>

                                        <td>

                                            <span class="badge bg-soft-success text-success">Active</span>

                                        </td>
                                        <td>
                                            <div class="d-flex gap-3">
                                                <a href="" class="link-dark"><i
                                                        class="ri-edit-line align-middle fs-20"></i></a>
                                            </div>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="messagesTabsJustified">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Price (₹)</th>
                                        <th>Stock at</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr>
                                        <td>
                                            -
                                        </td>
                                        <td>

                                            <img src=" " class="logo-dark me-1" alt="Product" height="30">

                                        </td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>
                                            Branch 1
                                        </td>

                                        <td>

                                            <span class="badge bg-soft-success text-success">Active</span>

                                        </td>
                                        <td>
                                            <div class="d-flex gap-3">
                                                <a href="" class="link-dark"><i
                                                        class="ri-edit-line align-middle fs-20"></i></a>
                                            </div>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="profileTabsJustified">

                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Price (₹)</th>
                                        <th>Stock at</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr>
                                        <td>
                                            -
                                        </td>
                                        <td>

                                            <img src=" " class="logo-dark me-1" alt="Product" height="30">

                                        </td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>
                                            Branch 2
                                        </td>

                                        <td>

                                            <span class="badge bg-soft-success text-success">Active</span>

                                        </td>
                                        <td>
                                            <div class="d-flex gap-3">
                                                <a href="" class="link-dark"><i
                                                        class="ri-edit-line align-middle fs-20"></i></a>
                                            </div>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="categoryAdd" tabindex="-1" aria-labelledby="categoryAdd" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenteredScrollableTitle">Transfer Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="row" action="" method="post" enctype="multipart/form-data">

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label text-muted">Select Branch</label>
                                <select class="form-control" data-choices name="category" id="category">
                                    <option value=""> Select </option>
                                    <option value="1">Branch-1</option>
                                    <option value="1">Branch-2</option>
                                    <option value="1">Branch-3</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label text-muted">Select Category</label>
                                <select class="form-control" data-choices name="category" id="category">
                                    <option value=""> Select </option>
                                    <option value="1">Category-1</option>
                                    <option value="1">Category-2</option>
                                    <option value="1">Category-3</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label text-muted">Select sub
                                    Category</label>
                                <select class="form-control" data-choices name="category" id="category">
                                    <option value=""> Select </option>
                                    <option value="1">Category-1</option>
                                    <option value="1">Category-2</option>
                                    <option value="1">Category-3</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label text-muted">Select Product</label>
                                <select class="form-control" data-choices name="product" id="product">
                                    <option value=""> Select </option>
                                    <option value="1">product-1</option>
                                    <option value="1">product-2</option>
                                    <option value="1">product-3</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label text-muted">Select Unit</label>
                                <select class="form-control" data-choices name="product" id="product">
                                    <option value=""> Select </option>
                                    <option value="1">PCS</option>
                                    <option value="1">KG</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label text-muted">Available</label>
                                <input readonly type="text" id="category" name="category" class="form-control" placeholder="0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label text-muted">Select Quantity</label>
                                <input type="text" id="category" name="category" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Clear</button>
                    <button type="submit" class="btn btn-primary">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{asset('assets/js/users/category.js')}}"></script>
@endsection