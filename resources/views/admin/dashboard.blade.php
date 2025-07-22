@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Admin Login</title>
@endsection

@section('body')
    <div class="row">
            <div class="col-xl-6 col-lg-12">
                <div class="row">
                    <div class="col-lg-6 col-md-6">
                        <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="mb-3 card-title">Total Revenue</p>
                                            <h4 class="fw-bold text-primary d-flex align-items-center gap-2 mb-0">$35,428.09</h4>
                                        </div>
                                        <div>
                                            <div>
                                                    <i class="ri-money-dollar-circle-line fs-32 text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mt-5">
                                        <div class="col-12">
                                            <div id="sales_funnel" data-colors="#604ae3" class="apex-charts"></div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="mb-3 card-title">Total Orders</p>
                                            <h4 class="fw-bold d-flex align-items-center gap-2 mb-0">4526</h4>
                                        </div>
                                        <div>
                                            <div>
                                                    <i class="ri-restaurant-2-line fs-32 text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mt-5">
                                        <div class="col-12">
                                            <div id="order_funnel" data-colors="#604ae3" class="apex-charts"></div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="mb-3 card-title">Total Customers</p>
                                            <h4 class="fw-bold d-flex align-items-center gap-2 mb-0">5736</h4>
                                        </div>
                                        <div>
                                            <div>
                                                    <i class="ri-group-2-line fs-32 text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mt-5">
                                        <div class="col-12">
                                            <div id="customer_funnel" data-colors="#604ae3" class="apex-charts"></div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="mb-3 card-title">Cancelled Orders</p>
                                            <h4 class="fw-bold text-primary d-flex align-items-center gap-2 mb-0">1310</h4>
                                        </div>
                                        <div>
                                            <div>
                                                    <i class="ri-calendar-close-line fs-32 text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mt-5">
                                        <div class="col-12">
                                            <div id="cancel_funnel" data-colors="#604ae3" class="apex-charts"></div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                                <h4 class="card-title mb-0">Revenue Summary</h4>
                        </div>
                        <div class="dropdown">
                                <a href="#" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" aria-expanded="false">
                                    Monthly
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- item-->
                                    <a href="#!" class="dropdown-item">Week</a>
                                    <!-- item-->
                                    <a href="#!" class="dropdown-item">Months</a>
                                    <!-- item-->
                                    <a href="#!" class="dropdown-item">Years</a>
                                </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center g-0">
                                <div class="col-lg-6">
                                    <div id="order_summary" class="apex-charts"></div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="ps-lg-3">
                                        <h2 class="fw-bold text-primary mt-2 d-flex align-items-center gap-3">$56184.22 <span class="badge text-success bg-success-subtle fs-12"><i class="ri-arrow-up-line"></i>31.08%</span></h2>
                                        <p class="mb-2 fw-medium">From $600,765.00</p>
                                        <p class="mb-3 fw-medium">❋❋❋</p>
                                        <p>Restaurant has successfully completed a substantial number of orders, totaling 4,500 orders.</p>
                                        <a href="#!" class="btn btn-dark btn-sm mt-3">View Reports</a>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    <div class="row">
        <div class="col-xl-4 col-lg-6">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                            <h4 class="card-title mb-0">Daily Trending Menu</h4>
                    </div>
                    <div>
                            <a href="#!" class="text-dark btn btn-sm btn-link text-uppercase fw-semibold px-0">View Menu <i class="ri-arrow-right-line"></i></a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center gap-3 border-bottom pb-4">
                            <div class="rounded bg-warning-subtle avatar-lg d-flex align-items-center justify-content-center">
                                <img src="assets/images/food-icon/pic12.png" alt="" class="avatar-lg">
                            </div>
                            <div>
                                <a href="#!" class="text-dark fs-14 fw-medium">Italian Burata Pizza</a>
                                <p class="fs-14 my-1">$12.00</p>
                                <a href="#!" class="link-dark link-offset-2 text-decoration-underline fw-medium">View Details <i class="ri-arrow-right-up-line"></i></a>
                            </div>
                            <div class="ms-auto text-lg-end">
                                <p class="text-primary fs-16 fw-medium">#1</p>
                                <p class="mb-0 fw-medium">Orders: 22x</p>
                            </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3 border-bottom py-4">
                            <div class="rounded bg-warning-subtle avatar-lg d-flex align-items-center justify-content-center">
                                <img src="assets/images/food-icon/pic19.png" alt="" class="avatar-lg">
                            </div>
                            <div>
                                <a href="#!" class="text-dark fs-14 fw-medium">Veg Indian Thali</a>
                                <p class="fs-14 my-1">$14.00</p>
                                <a href="#!" class="link-dark link-offset-2 text-decoration-underline fw-medium">View Details <i class="ri-arrow-right-up-line"></i></a>
                            </div>
                            <div class="ms-auto text-lg-end">
                                <p class="text-primary fs-16 fw-medium">#2</p>
                                <p class="mb-0 fw-medium">Orders: 13x</p>
                            </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3 pt-4">
                            <div class="rounded bg-warning-subtle avatar-lg d-flex align-items-center justify-content-center">
                                <img src="assets/images/food-icon/pic17.png" alt="" class="avatar-lg">
                            </div>
                            <div>
                                <a href="#!" class="text-dark fs-14 fw-medium">Chocolate Lava Cake</a>
                                <p class="fs-14 my-1">$10.00</p>
                                <a href="#!" class="link-dark link-offset-2 text-decoration-underline fw-medium">View Details <i class="ri-arrow-right-up-line"></i></a>
                            </div>
                            <div class="ms-auto text-lg-end">
                                <p class="text-primary fs-16 fw-medium">#3</p>
                                <p class="mb-0 fw-medium">Orders: 10x</p>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                            <h4 class="card-title mb-0">Other Outlets</h4>
                    </div>
                    <div class="dropdown">
                            <a href="#" class="dropdown-toggle rounded arrow-none" data-bs-toggle="dropdown" aria-expanded="true">
                                <i class="ri-edit-box-line fs-20"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">Create New Outet</a>
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">New Areas</a>
                            </div>
                    </div>
                </div>
                <div style="height: 333px;" data-simplebar>
                    <div class="border-bottom p-3">
                            <div>
                                <h6 class="text-uppercase fw-bold">Miami - USA <span class="ms-auto fw-medium float-end"> <i class="ri-star-fill text-warning"></i> 4.3</span></h6>
                                <span class="fw-medium fs-18"><i class="ri-map-pin-range-line"></i></span>
                                <span class="fw-medium ms-1">101 Ocean Dr, Miami, FL 33139</span>
                                <div class="mt-1">
                                    <span class="fw-medium fs-18"><i class="ri-phone-line"></i></span>
                                    <a href="#!" class="fw-medium link-primary ms-1">+ 305-555-7890</a>
                                </div>
                            </div>
                    </div>
                    <div class="border-bottom p-3">
                            <div>
                                <h6 class="text-uppercase fw-bold">New York - USA <span class="ms-auto fw-medium float-end"> <i class="ri-star-fill text-warning"></i> 4.8</span></h6>
                                <span class="fw-medium fs-18"><i class="ri-map-pin-range-line"></i></span>
                                <span class="fw-medium ms-1">123 Broadway Ave, New York, NY 10001</span>
                                <div class="mt-1">
                                    <span class="fw-medium fs-18"><i class="ri-phone-line"></i></span>
                                    <a href="#!" class="fw-medium link-primary ms-1">+ 212-555-1234</a>
                                </div>
                            </div>
                    </div>
                    <div class="border-bottom p-3">
                            <div>
                                <h6 class="text-uppercase fw-bold">Los Angeles - USA <span class="ms-auto fw-medium float-end"> <i class="ri-star-fill text-warning"></i> 4.7</span></h6>
                                <span class="fw-medium fs-18"><i class="ri-map-pin-range-line"></i></span>
                                <span class="fw-medium ms-1">456 Sunset Blvd, Los Angeles, CA 90028</span>
                                <div class="mt-1">
                                    <span class="fw-medium fs-18"><i class="ri-phone-line"></i></span>
                                    <a href="#!" class="fw-medium link-primary ms-1">+ 323-555-6789</a>
                                </div>
                            </div>
                    </div>

                    <div class="border-bottom p-3">
                            <div>
                                <h6 class="text-uppercase fw-bold">Chicago - USA <span class="ms-auto fw-medium float-end"> <i class="ri-star-fill text-warning"></i> 4.6</span></h6>
                                <span class="fw-medium fs-18"><i class="ri-map-pin-range-line"></i></span>
                                <span class="fw-medium ms-1">101 Michigan Ave, Chicago, IL 60601</span>
                                <div class="mt-1">
                                    <span class="fw-medium fs-18"><i class="ri-phone-line"></i></span>
                                    <a href="#!" class="fw-medium link-primary ms-1">+ 312-555-9876</a>
                                </div>
                            </div>
                    </div>

                    <div class="border-bottom p-3">
                            <div>
                                <h6 class="text-uppercase fw-bold">Miami - USA <span class="ms-auto fw-medium float-end"> <i class="ri-star-fill text-warning"></i> 4.5</span></h6>
                                <span class="fw-medium fs-18"><i class="ri-map-pin-range-line"></i></span>
                                <span class="fw-medium ms-1">234 Ocean Dr, Miami, FL 33139</span>
                                <div class="mt-1">
                                    <span class="fw-medium fs-18"><i class="ri-phone-line"></i></span>
                                    <a href="#!" class="fw-medium link-primary ms-1">+ 305-555-4567</a>
                                </div>
                            </div>
                    </div>


                    <div class="border-bottom p-3">
                            <div>
                                <h6 class="text-uppercase fw-bold">London - UK
                                    <span class="ms-auto fw-medium float-end">
                                        <i class="ri-star-fill text-warning"></i> 4.6
                                    </span>
                                </h6>
                                <span class="fw-medium fs-18"><i class="ri-map-pin-range-line"></i></span>
                                <span class="fw-medium ms-1">221B Baker Street, London, NW1 6XE</span>
                                <div class="mt-1">
                                    <span class="fw-medium fs-18"><i class="ri-phone-line"></i></span>
                                    <a href="#!" class="fw-medium link-primary ms-1">+44 20 7946 0958</a>
                                </div>
                            </div>
                    </div>

                    <div class="border-bottom p-3">
                            <div>
                                <h6 class="text-uppercase fw-bold">Sydney - Australia
                                    <span class="ms-auto fw-medium float-end">
                                        <i class="ri-star-fill text-warning"></i> 4.9
                                    </span>
                                </h6>
                                <span class="fw-medium fs-18"><i class="ri-map-pin-range-line"></i></span>
                                <span class="fw-medium ms-1">123 George St, Sydney, NSW 2000</span>
                                <div class="mt-1">
                                    <span class="fw-medium fs-18"><i class="ri-phone-line"></i></span>
                                    <a href="#!" class="fw-medium link-primary ms-1">+61 2 5550 6789</a>
                                </div>
                            </div>
                    </div>


                    <div class="p-3">
                            <div>
                                <h6 class="text-uppercase fw-bold">Chicago - USA <span class="ms-auto fw-medium float-end"> <i class="ri-star-fill text-warning"></i> 4.6</span></h6>
                                <span class="fw-medium fs-18"><i class="ri-map-pin-range-line"></i></span>
                                <span class="fw-medium ms-1">789 Michigan Ave, Chicago, IL 60611</span>
                                <div class="mt-1">
                                    <span class="fw-medium fs-18"><i class="ri-phone-line"></i></span>
                                    <a href="#!" class="fw-medium link-primary ms-1">+ 312-555-4321</a>
                                </div>
                            </div>
                    </div>
                </div>
                <div class="card-footer border-top text-center p-3">
                    <a href="#!" class="link-primary text-decoration-underline fw-medium">Show More <i class="ri-arrow-right-up-line"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                            <h4 class="card-title mb-0">Delivered Status</h4>
                    </div>
                    <div class="dropdown">
                            <a href="#" class="dropdown-toggle text-dark btn btn-sm btn-link text-uppercase fw-semibold px-0" data-bs-toggle="dropdown" aria-expanded="false">
                                Daily
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                                <a href="#!" class="dropdown-item">Week</a>
                                <!-- item-->
                                <a href="#!" class="dropdown-item">Months</a>
                                <!-- item-->
                                <a href="#!" class="dropdown-item">Years</a>
                            </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Payment Via</th>
                                        <th>Status</th>
                                        <th>Amount ($)</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td>2025-01-29</td>
                                        <td>PayPal</td>
                                        <td><span class="badge badge-soft-success">Success</span></td>
                                        <td>150.75</td>
                                    </tr>

                                    <tr>
                                        <td>2025-01-28</td>
                                        <td>Bank Transfer</td>
                                        <td><span class="badge badge-soft-danger">Failed</span></td>
                                        <td>320.50</td>
                                    </tr>

                                    <tr>
                                        <td>2025-01-27</td>
                                        <td>Debit Card</td>
                                        <td><span class="badge badge-soft-warning">Pending</span></td>
                                        <td>98.00</td>
                                    </tr>

                                    <tr>
                                        <td>2025-01-26</td>
                                        <td>Credit Card</td>
                                        <td><span class="badge badge-soft-success">Success</span></td>
                                        <td>275.25</td>
                                    </tr>

                                    <tr>
                                        <td>2025-01-25</td>
                                        <td>Google Pay</td>
                                        <td><span class="badge badge-soft-danger">Failed</span></td>
                                        <td>180.00</td>
                                    </tr>

                                    <tr>
                                        <td>2025-01-24</td>
                                        <td>Apple Pay</td>
                                        <td><span class="badge badge-soft-success">Success</span></td>
                                        <td>500.00</td>
                                    </tr>

                                    <tr>
                                        <td>2025-01-23</td>
                                        <td>Bank Transfer</td>
                                        <td><span class="badge badge-soft-warning">Pending</span></td>
                                        <td>200.40</td>
                                    </tr>

                                    <tr>
                                        <td>2025-01-22</td>
                                        <td>Credit Card</td>
                                        <td><span class="badge badge-soft-success">Success</span></td>
                                        <td>350.00</td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
                </div>

                <div class="card-footer border-top text-center p-3">
                    <a href="#!" class="link-primary text-decoration-underline fw-medium">Show More <i class="ri-arrow-right-up-line"></i></a>
                </div>
            </div>
        </div>
    </div>
@endsection
