<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	@yield('title')

	@yield('style')
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="description" content="A fully responsive premium Bootstrap admin dashboard template for modern web applications." />
	<meta name="author" content="FoxPixel" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
	<link rel="stylesheet" href="{{ asset('assets/css/vendor.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/icons.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/app.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <script src="{{ asset('assets/js/config.min.js') }}"></script>

    <style type="text/css">
        .secret{
            display: none!important;
        }
    </style>

</head>
<body>
	
	<div class="wrapper">
        <div class="main-nav">
            @php
                $user = App\Models\User::where('slug_name',request()->segment(1))->first();
            @endphp
            <!-- Sidebar Logo -->
            <div class="logo-box">
                @if(request()->segment(1) === 'admin')
                    <a href="#" class="logo-dark">
                        <img src="{{asset('assets/images/sling-logo.png')}}" class="logo-sm" alt="logo sm">
                        <img src="{{asset('assets/images/sling-logo.png')}}" class="logo-lg" alt="logo dark">
                    </a>

                    <a href="#" class="logo-light">
                        <img src="{{asset('assets/images/sling-logo.png')}}" class="logo-sm" alt="logo sm">
                        <img src="{{asset('assets/images/sling-logo.png')}}" class="logo-lg" alt="logo light">
                    </a>
                    
                @else
                    <a href="#" class="logo-dark">
                        <img src="{{ asset('storage/' . $user->logo) }}" class="logo-sm" alt="logo sm">
                        <img src="{{ asset('storage/' . $user->logo) }}" class="logo-lg" alt="logo dark">
                    </a>

                    <a href="#" class="logo-light">
                        <img src="{{ asset('storage/' . $user->logo) }}" class="logo-sm" alt="logo sm">
                        <img src="{{ asset('storage/' . $user->logo) }}" class="logo-lg" alt="logo light">
                    </a>
                @endif
            </div>

            <div class="h-100" data-simplebar>
                @if(Auth::user()->hasRole('Super Admin'))
                    <ul class="navbar-nav" id="navbar-nav">

                        <li class="menu-item pt-2">
                            <a class="menu-link" href="{{route('admin.dashboard')}}">
                                <span class="nav-icon">
                                    <i class="ri-dashboard-2-line"></i>
                                </span>
                                <span class="nav-text"> Dashboard </span>
                                <!-- <span class="badge bg-success badge-pill text-end">9+</span> -->
                            </a>
                        </li>
                        <li class="menu-item {{(request()->is('admin/shops*')) ? 'active':''}}">
                            <a class="menu-link {{(request()->is('admin/shops*')) ? 'ative':''}}" href="{{route('admin.shop.index')}}">
                                <span class="nav-icon">
                                    <i class="ri-store-line"></i>
                                </span>
                                <span class="nav-text"> Shop </span>
                            </a>
                        </li>
                        
                    </ul>
                @endif
                @if(Auth::user()->hasRole('HO'))
                    <ul class="navbar-nav" id="navbar-nav">

                        <li class="menu-item">
                            <a class="menu-link" href="{{route('dashboard', ['company' => request()->route('company')])}}">
                                <span class="nav-icon">
                                    <i class="ri-dashboard-2-line"></i>
                                </span>
                                <span class="nav-text"> Dashboard </span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a class="menu-link menu-arrow" href="#sidebarCategories" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCategories">
                                <span class="nav-icon">
                                    <i class="ri-equalizer-2-line"></i>
                                </span>
                                <span class="nav-text"> Categories </span>
                            </a>
                            <div class="collapse" id="sidebarCategories">
                                <ul class="sub-menu-nav">
                                    <li class="sub-menu-item">
                                        <a class="sub-menu-link" href="{{route('category.index', ['company' => request()->route('company')])}}">Category</a>
                                    </li>
                                    <li class="sub-menu-item">
                                        <a class="sub-menu-link" href="{{route('sub_category.index', ['company' => request()->route('company')])}}">Sub Category</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li class="menu-item {{ request()->is(request()->route('company') . '/products*') ? 'active' : '' }}">
                            <a class="menu-link" href="{{route('product.index', ['company' => request()->route('company')])}}">
                                <span class="nav-icon">
                                    <i class="ri-shopping-basket-line"></i>
                                </span>
                                <span class="nav-text"> Products </span>
                            </a>
                        </li>

                        <li class="menu-item {{ request()->is(request()->route('company') . '/vendors*') ? 'active' : '' }}">
                            <a class="menu-link menu-arrow" href="#sidebarVendors" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarVendors">
                                <span class="nav-icon">
                                    <i class="ri-user-received-line"></i>
                                </span>
                                <span class="nav-text"> Vendors </span>
                            </a>
                            <div class="collapse {{  request()->is(request()->route('company') . '/vendors*') ? 'show' : '' }}" id="sidebarVendors">
                                <ul class="sub-menu-nav">
                                    <li class="sub-menu-item">
                                        <a class="sub-menu-link {{ ((request()->is(request()->route('company') . '/vendors/index')) || (request()->is(request()->route('company') . '/vendors/ledger/*'))) ? 'active' : '' }}" href="{{route('vendor.index', ['company' => request()->route('company')])}}">List</a>
                                    </li>
                                    <li class="sub-menu-item">
                                        <a class="sub-menu-link  {{  request()->is(request()->route('company') . '/vendors/purchase_orders*') ? 'active' : '' }}" href="{{route('vendor.purchase_order.index', ['company' => request()->route('company')])}}">Purchase Order</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li class="menu-item {{ request()->is(Auth::user()->slug_name . '/inventories/*') ? 'active' : '' }}">
                            <a class="menu-link menu-arrow" href="#sidebarProduct" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarProduct">
                                <span class="nav-icon">
                                    <i class="ri-hand-coin-fill"></i>
                                </span>
                                <span class="nav-text"> Inventory </span>
                            </a>
                            <div class="collapse {{ request()->is(Auth::user()->slug_name . '/inventories/*') ? 'show' : '' }}" id="sidebarProduct">
                                <ul class="sub-menu-nav">

                                    <li class="sub-menu-item">
                                        <a class="sub-menu-link {{ request()->is(Auth::user()->slug_name . '/inventories/stock/*') ? 'active' : '' }}" href="{{route('inventory.stock', ['company' => request()->route('company'),'shop' => Auth::user()->id,'branch' => 0])}}">Stock</a>
                                    </li>
                                    <li class="sub-menu-item">
                                        <a class="sub-menu-link {{ request()->is(Auth::user()->slug_name . '/inventories/transfer/*') ? 'active' : '' }}" href="{{route('inventory.transfer', ['company' => request()->route('company')])}}">Product Transfer</a>
                                    </li>

                                </ul>
                            </div>
                        </li>

                        <li class="menu-item {{ request()->is(Auth::user()->slug_name . '/orders/*') ? 'active' : '' }}">
                            <a class="menu-link" href="{{route('order.index', ['company' => request()->route('company'),'branch' => 0])}}">
                                <span class="nav-icon">
                                    <i class="ri-shopping-cart-line"></i>
                                </span>
                                <span class="nav-text"> Orders </span>
                            </a>
                        </li>

                        <li class="menu-item {{ request()->is(Auth::user()->slug_name . '/customers/*') ? 'active' : '' }}">
                            <a class="menu-link" href="{{route('customer.index', ['company' => request()->route('company')])}}">
                                <span class="nav-icon">
                                    <i class="ri-group-line"></i>
                                </span>
                                <span class="nav-text"> Customers </span>
                            </a>
                        </li>

                        <li class="menu-item {{ request()->is(Auth::user()->slug_name . '/settings/*') ? 'active' : '' }}">
                            <a class="menu-link menu-arrow" href="#sidebarSetting" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSetting">
                                <span class="nav-icon">
                                    <i class="ri-settings-3-line"></i>
                                </span>
                                <span class="nav-text"> Settings </span>
                            </a>
                            <div class="collapse {{ request()->is(Auth::user()->slug_name . '/settings/*') ? 'show' : '' }}" id="sidebarSetting">
                                <ul class="sub-menu-nav">
                                    <li class="sub-menu-item">
                                        <a class="sub-menu-link {{ request()->is(Auth::user()->slug_name . '/settings/taxes/index') ? 'active' : '' }}" href="{{route('setting.tax.index', ['company' => request()->route('company')])}}">Tax</a>
                                    </li>
                                    <li class="sub-menu-item">
                                        <a class="sub-menu-link {{ request()->is(Auth::user()->slug_name . '/settings/metrics/index') ? 'active' : '' }}" href="{{route('setting.metric.index', ['company' => request()->route('company')])}}">Metrics</a>
                                    </li>
                                    <li class="sub-menu-item">
                                        <a class="sub-menu-link {{ request()->is(Auth::user()->slug_name . '/settings/finances/index') ? 'active' : '' }}" href="{{route('setting.finance.index', ['company' => request()->route('company')])}}">Finances</a>
                                    </li>
                                    <li class="sub-menu-item">
                                        <a class="sub-menu-link {{ request()->is(Auth::user()->slug_name . '/settings/payments/index') ? 'active' : '' }}" href="{{route('setting.payment.index', ['company' => request()->route('company')])}}">Payment Method</a>
                                    </li>
                                    <li class="sub-menu-item">
                                        <a class="sub-menu-link {{ request()->is(Auth::user()->slug_name . '/settings/bill/index') ? 'active' : '' }}" href="{{route('setting.bill.index', ['company' => request()->route('company')])}}">Bill </a>
                                    </li>
                                    <!-- <li class="sub-menu-item">
                                        <a class="sub-menu-link {{ request()->is(Auth::user()->slug_name . '/settings/general/index') ? 'active' : '' }}" href="{{route('setting.general.index', ['company' => request()->route('company')])}}">General Settings</a>
                                    </li> -->
                                </ul>
                            </div>
                        </li>
                        

                    </ul>
                @endif

                @if(Auth::user()->hasRole('Branch'))
                    <ul class="navbar-nav" id="navbar-nav">

                        <li class="menu-item">
                            <a class="menu-link" href="{{route('branch.dashboard', ['company' => request()->route('company')])}}">
                                <span class="nav-icon">
                                    <i class="ri-dashboard-2-line"></i>
                                </span>
                                <span class="nav-text"> Dashboard </span>
                            </a>
                        </li>

                        <li class="menu-item {{ request()->is(Auth::user()->slug_name . '/branches/customers/*') ? 'active' : '' }}">
                            <a class="menu-link" href="{{route('branch.customer.index', ['company' => request()->route('company')])}}">
                                <span class="nav-icon">
                                    <i class="ri-group-2-line"></i>
                                </span>
                                <span class="nav-text"> Customers </span>
                            </a>
                        </li>

                        <li class="menu-item {{ request()->is(request()->route('company') . '/products*') ? 'active' : '' }}">
                            <a class="menu-link" href="{{route('branch.product.index', ['company' => request()->route('company')])}}">
                                <span class="nav-icon">
                                    <i class="ri-shopping-basket-line"></i>
                                </span>
                                <span class="nav-text"> Products </span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a class="menu-link" href="{{route('branch.billing.pos', ['company' => request()->route('company')])}}">
                                <span class="nav-icon">
                                    <i class="ri-shopping-cart-line"></i>
                                </span>
                                <span class="nav-text"> Billing </span>
                            </a>
                        </li>

                        <li class="menu-item {{ request()->is(Auth::user()->slug_name . '/branches/orders/*') ? 'active' : '' }}">
                            <a class="menu-link" href="{{route('branch.order.index', ['company' => request()->route('company')])}}">
                                <span class="nav-icon">
                                    <i class="ri-shopping-basket-line"></i>
                                </span>
                                <span class="nav-text"> Orders </span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a class="menu-link" href="{{route('branch.staff.index', ['company' => request()->route('company')])}}">
                                <span class="nav-icon">
                                    <i class="ri-group-line"></i>
                                </span>
                                <span class="nav-text"> Staff </span>
                            </a>
                        </li>

                        <!-- <li class="menu-item">
                            <a class="menu-link" href="{{route('branch.setting.index', ['company' => request()->route('company')])}}">
                                <span class="nav-icon">
                                    <i class="ri-settings-3-line"></i>
                                </span>
                                <span class="nav-text"> Settings </span>
                            </a>
                        </li> -->
                        

                    </ul>
                @endif
            </div>
        </div>
        <header class="topbar d-flex">
            
            <!-- Sidebar Logo -->
            <div class="logo-box">
                @if(request()->segment(1) === 'admin')
                    <a href="#" class="logo-dark">
                        <img src="{{asset('assets/images/sling-logo.png')}}" class="logo-sm" alt="logo sm">
                        <img src="{{asset('assets/images/sling-logo.png')}}" class="logo-lg" alt="logo dark">
                    </a>

                    <a href="#" class="logo-light">
                        <img src="{{asset('assets/images/sling-logo.png')}}" class="logo-sm" alt="logo sm">
                        <img src="{{asset('assets/images/sling-logo.png')}}" class="logo-lg" alt="logo light">
                    </a>
                    
                @else
                    <a href="#" class="logo-dark">
                        <img src="{{ asset('storage/' . $user->logo) }}" class="logo-sm" alt="logo sm">
                        <img src="{{ asset('storage/' . $user->logo) }}" class="logo-lg" alt="logo dark">
                    </a>

                    <a href="#" class="logo-light">
                        <img src="{{ asset('storage/' . $user->logo) }}" class="logo-sm" alt="logo sm">
                        <img src="{{ asset('storage/' . $user->logo) }}" class="logo-lg" alt="logo light">
                    </a>
                @endif
            </div>

            <div class="container">
                <div class="navbar-header">
                    <!-- Menu Toggle Button (sm-hover) -->
                    <button type="button" class="btn btn-link d-flex button-sm-hover button-toggle-menu" aria-label="Show Full Sidebar">
                        <i class="ri-menu-2-line button-sm-hover-icon text-white"></i>
                    </button>

                    <!-- <div class="d-flex align-items-center gap-2">
                        <form class="app-search d-none d-md-block me-auto">
                            <div class="position-relative">
                                <input type="search" class="form-control" placeholder="Start typing..." autocomplete="off" value="">
                                <i class="ri-search-line search-widget-icon"></i>
                            </div>
                        </form>
                    </div> -->

                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <!-- Theme Color (Light/Dark) -->
                        <div class="topbar-item">
                            <button type="button" class="topbar-button" id="light-dark-mode">
                                <i class="ri-moon-line fs-20 align-middle light-mode"></i>
                                    <i class="ri-sun-line fs-20 align-middle dark-mode"></i>
                            </button>
                        </div>

                        <!-- Notification -->
                        <!-- <div class="dropdown topbar-item">
                            <button type="button" class="topbar-button" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="topbar-badge border border-2 border-info rounded-pill">18<span class="visually-hidden">unread messages</span></span>
                            </button>
                            <div class="dropdown-menu pt-0 dropdown-lg dropdown-menu-end" aria-labelledby="page-header-notifications-dropdown">
                                <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="m-0 fs-16 fw-semibold"> Notifications</h6>
                                        </div>
                                    </div>
                                </div>
                                <div data-simplebar style="max-height: 280px;">
                                    <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom text-wrap">
                                        <p class="mb-0"><span class="fw-medium">Olivia Bennett</span> mentioned you in a comment <span>"This update really improves the user experience! 🚀"</span></p>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <p class="mb-0 fw-semibold">Daniel Roberts</p>
                                        <p class="mb-0 text-wrap">
                                            Just sent over the revised proposal. Let me know your thoughts.
                                        </p>
                                    </a>
                                </div>
                            </div>
                        </div> -->

                        <!-- User -->
                        <div class="dropdown topbar-item">
                            <a type="button" class="topbar-button p-0" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center gap-2">
                                    <img class="rounded-circle" width="32" src="{{asset('assets/images/users/avatar-1.jpg')}}" alt="user-image">
                                    <span class="d-lg-flex flex-column gap-1 d-none">
                                        <h5 class="my-0 fs-13 text-uppercase text-reset fw-bold">{{Auth::user()->name}}</h5>
                                    </span>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                @if(request()->segment(1) === 'admin')
                                    <a class="dropdown-item" href="{{route('admin.my_profile')}}">
                                        <i class="bx bx-user-circle fs-18 align-middle me-2"></i><span class="align-middle">My Account</span>
                                    </a>
                                @else
                                    <a class="dropdown-item" href="{{route('my_profile', ['company' => request()->route('company')])}}">
                                        <i class="bx bx-user-circle fs-18 align-middle me-2"></i><span class="align-middle">My Account</span>
                                    </a>
                                @endif
                                <a class="dropdown-item" href="#">
                                    <i class="bx bx-help-circle fs-18 align-middle me-2"></i><span class="align-middle">Help</span>
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="bx bx-photo-album fs-18 align-middle me-2"></i>
                                    <span class="align-middle">Photos</span>
                                    <span class="align-middle float-end badge badge-soft-danger">New</span>
                                </a>
                                <div class="dropdown-divider my-1"></div>
                                <a class="dropdown-item" href="#">
                                    <i class="bx bx-lock fs-18 align-middle me-2"></i><span class="align-middle">Lock screen</span>
                                </a>
                                @if (request()->segment(1) === 'admin')
                                    <a class="dropdown-item" href="{{route('admin.logout')}}">
                                        <i class="bx bx-log-out fs-18 align-middle me-2"></i><span class="align-middle">Logout</span>
                                    </a>
                                @else
                                    <a class="dropdown-item" href="{{route('logout', ['company' => request()->route('company')])}}">
                                        <i class="bx bx-log-out fs-18 align-middle me-2"></i><span class="align-middle">Logout</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- Start Content here -->
        <div class="page-container">
			<div class="page-content">
            @yield('body')
			</div>
            <!-- ========== Footer Start ========== -->
            <!-- <footer class="footer">
                <div class="container">
                    <div class="row">
                        <div class="col-12 text-center">
                            <script>document.write(new Date().getFullYear())</script> &copy; Sling. All rights reserved</a>
                        </div>
                    </div>
                </div>
            </footer> -->
            <!-- ========== Footer End ========== -->
        </div>
        <!-- End Page Content -->
    </div>
	@yield('modal')

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/vendor.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
	<!-- Dashboard Js -->
    <script src="{{ asset('assets/js/pages/dashboard.js') }}"></script>
	<!-- Page Js -->
    <script src="{{ asset('assets/js/pages/categories.js') }}"></script>
    <script>
        document.addEventListener("toast", function (e) {
            const d = e.detail;
            Toastify({
                text: d.text,
                gravity: d.gravity,
                position: d.position,
                className: d.className,
                duration: d.duration,
                close: true,
                style: d.style
            }).showToast();
        });
    </script>

    @if (session('toast_success'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const event = new CustomEvent("toast", {
                    detail: {
                        text: "{{ session('toast_success') }}",
                        gravity: "top",      // top / bottom
                        position: "right",   // left / center / right
                        className: "success", // success, error, info, etc. depending on your toast lib
                        duration: 10000,
                        close: "close",
                        style: "style"
                    }
                });
                document.dispatchEvent(event);
            });
        </script>
    @endif

    @if (session('toast_error'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const event = new CustomEvent("toast", {
                    detail: {
                        text: "{{ session('toast_error') }}",
                        gravity: "top",      // top / bottom
                        position: "right",   // left / center / right
                        className: "success", // success, error, info, etc. depending on your toast lib
                        duration: 10000,
                        close: "close",
                        style: "style"
                    }
                });
                document.dispatchEvent(event);
            });
        </script>
    @endif

    <script src="{{ asset('assets/js/pages/pos.js') }}"></script>
    @yield('script')
</body>
</html>