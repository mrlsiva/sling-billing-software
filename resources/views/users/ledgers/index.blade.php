@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Product Transfer</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"
    rel="stylesheet" />
@endsection

@section('body')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <a href="#!" class="btn btn-sm"> <i class="bx bx-arrow-back me-1"></i></a>
                    <p class="card-title mb-0">AAA Agency</p>
                </div>
                <a href="#!" class="btn btn-sm btn-success"> <i class="bx bx-plus me-1"></i>New Entry </a>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-xl-12">
        <div class="card">
            <div class="card-body ">
                <form>
                    <div class="row align-items-center">
                        <div class="col-3">
                            <h4 class="mt-3">Choose date range</h4>
                        </div>
                        <div class="col-3">
                            <label for="fromDate" class="col-form-label mb-1 pb-1">From:</label>
                            <input type="text" id="fromDate" name="fromDate" class="form-control datepicker" />
                        </div>
                        <div class="col-3">
                            <label for="toDate" class="col-form-label mb-1 pb-1">To:</label>
                            <input type="text" id="toDate" name="toDate" class="form-control datepicker" />
                        </div>
                        <div class="col-3">
                            <label for="toDate" class="col-form-label mb-1 pb-1">&nbsp</label>

                            <button type="submit" class="btn btn-primary w-100">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card">
            <div class="card-body ">
                <div class="d-flex align-items-center gap-3">
                    <img src="assets/images/food-icon/sup-2.png" alt="" class="img-fluid">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">Rs. 10,00,000</p>
                        <p class="card-title mb-0">Total Purchased</p>
                    </div>
                    <div class="ms-auto">
                        <a href="#!"
                            class="btn btn-primary avatar-sm rounded-circle d-flex align-items-center justify-content-center"><i
                                class="ri-eye-line align-middle fs-16 text-white"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card">
            <div class="card-body ">
                <div class="d-flex align-items-center gap-3">
                    <img src="assets/images/food-icon/sup-3.png" alt="" class="img-fluid">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">Rs. 8,00,000</p>
                        <p class="card-title mb-0">Total Billed Paid</p>
                    </div>
                    <div class="ms-auto">
                        <a href="#!"
                            class="btn btn-primary avatar-sm rounded-circle d-flex align-items-center justify-content-center"><i
                                class="ri-eye-line align-middle fs-16 text-white"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="card">
            <div class="card-body ">
                <div class="d-flex align-items-center gap-3">
                    <img src="assets/images/food-icon/sup-4.png" alt="" class="img-fluid">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">2,00,000</p>
                        <p class="card-title mb-0">Total Balance</p>
                    </div>
                    <div class="ms-auto">
                        <a href="#!"
                            class="btn btn-primary avatar-sm rounded-circle d-flex align-items-center justify-content-center"><i
                                class="ri-eye-line align-middle fs-16 text-white"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h5 class="card-title mb-0">Ledger</h5>
                    <div class="search-bar ms-auto">
                        <span style="top: 5px;"><i class="bx bx-search"></i></span>
                        <input type="search" class="form-control form-control-sm" id="search" placeholder="Search...">
                    </div>

                </div> <!-- end row -->
            </div>
            <div>
                <div class="table-responsive table-centered">
                    <table class="table table-striped text-nowrap mb-0">
                        <thead class="text-uppercase fs-12">
                            <tr>
                                <th class="border-0 py-2 text-dark">Invoice</th>
                                <th class="border-0 py-2 text-dark">Purchase Date</th>
                                <th class="border-0 py-2 text-dark">Due Date</th>
                                <th class="border-0 py-2 text-dark">Bill Amount</th>
                                <th class="border-0 py-2 text-dark">Paid Amount</th>
                                <th class="border-0 py-2 text-dark">Discount</th>
                                <th class="border-0 py-2 text-dark">Payment Status</th>
                                <th class="border-0 py-2 text-dark">Via</th>
                                <th class="border-0 py-2 text-dark">Action</th>
                            </tr>
                        </thead> <!-- end thead-->
                        <tbody>
                            <tr>
                                <td>
                                    <a class="fw-medium">#IN9023</a>
                                </td>
                                <td>15 Mar, 2025 <small>10:30 AM</small></td>
                                <td>22 Mar, 2025</td>
                                <td>Rs. 1,250.75</td>
                                <td>Rs. 1,250.75</td>
                                <td>Rs. 1,250.75</td>
                                <td>
                                    <span class="badge badge-soft-warning">Unpaid</span>
                                </td>
                                <td>Credit Card</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-soft-secondary me-1"><i
                                            class="bx bx-edit fs-16"></i></button>

                                </td>
                            </tr>
                            <tr>
                                <td colspan="9">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">Updated On</th>
                                                <th scope="col">Previous Amout</th>
                                                <th scope="col">Updated Amout</th>
                                                <th scope="col">Reson</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>3-12-2026</td>
                                                <td>12000</td>
                                                <td> 120000</td>
                                                <td>Lorem Ipsam </td>
                                            </tr>
                                            <tr>
                                                <td>3-12-2026</td>
                                                <td>12000</td>
                                                <td> 120000</td>
                                                <td>Lorem Ipsam </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a class="fw-medium">#IN3147</a>
                                </td>

                                <td>07 Feb, 2025 <small>02:45 PM</small></td>
                                <td>15 Feb, 2025</td>
                                <td>Rs. 1,250.75</td>
                                <td>Rs. 1,250.75</td>
                                <td>Rs. 1,250.75</td>

                                <td>
                                    <span class="badge badge-soft-danger">Overdue</span>
                                </td>
                                <td>PayPal</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-soft-secondary me-1"><i
                                            class="bx bx-edit fs-16"></i></button>

                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a class="fw-medium">#IN7654</a>
                                </td>

                                <td>28 Jan, 2025 <small>11:10 AM</small></td>
                                <td>05 Feb, 2025</td>
                                <td>Rs. 1,250.75</td>
                                <td>Rs. 1,250.75</td>
                                <td>Rs. 1,250.75</td>

                                <td>
                                    <span class="badge badge-soft-success">Paid</span>
                                </td>
                                <td>Wire Transfer</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-soft-secondary me-1"><i
                                            class="bx bx-edit fs-16"></i></button>

                                </td>
                            </tr>

                        </tbody> <!-- end tbody -->
                    </table> <!-- end table -->
                </div> <!-- table responsive -->
                <div
                    class="align-items-center justify-content-between row g-0 text-center text-sm-start p-3 border-top">
                    <div class="col-sm">
                        <div class="text-muted">
                            Showing <span class="fw-semibold">10</span> of <span class="fw-semibold">52</span> invoices
                        </div>
                    </div>
                    <div class="col-sm-auto mt-3 mt-sm-0">
                        <ul class="pagination justify-content-end mb-0">
                            <li class="page-item"><a class="page-link" href="javascript:void(0);"><i
                                        class="ri-arrow-left-s-line"></i></a></li>
                            <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);"><i
                                        class="ri-arrow-right-s-line"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div> <!-- end card body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
</div>
@endsection
@section('script')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Bootstrap Datepicker JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    });
</script>
@endsection