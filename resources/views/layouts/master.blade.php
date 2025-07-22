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
</head>
<body>
	
	<div class="wrapper">
        <div class="main-nav">
            <!-- Sidebar Logo -->
            <div class="logo-box">
                <a href="#" class="logo-dark">
                        <img src="assets/images/logo-sm.png" class="logo-sm" alt="logo sm">
                        <img src="assets/images/logo-dark.png" class="logo-lg" alt="logo dark">
                </a>

                <a href="#" class="logo-light">
                        <img src="assets/images/logo-sm.png" class="logo-sm" alt="logo sm">
                        <img src="assets/images/logo-white.png" class="logo-lg" alt="logo light">
                </a>
            </div>

            <div class="h-100" data-simplebar>

                <ul class="navbar-nav" id="navbar-nav">

                        <li class="menu-item pt-2">
                            <a class="menu-link" href="#">
                                <span class="nav-icon">
                                    <i class="ri-dashboard-2-line"></i>
                                </span>
                                <span class="nav-text"> Dashboard </span>
                                <span class="badge bg-success badge-pill text-end">9+</span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a class="menu-link" href="#">
                                <span class="nav-icon">
                                    <i class="ri-shopping-cart-line"></i>
                                </span>
                                <span class="nav-text"> Orders </span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a class="menu-link menu-arrow" href="#sidebarProduct" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarProduct">
                                <span class="nav-icon">
                                    <i class="ri-shopping-basket-2-line"></i>
                                </span>
                                <span class="nav-text"> Products </span>
                            </a>
                            <div class="collapse" id="sidebarProduct">
                                <ul class="sub-menu-nav">
                                    <li class="sub-menu-item">
                                            <a class="sub-menu-link" href="#">Listing</a>
                                    </li>
                                    <li class="sub-menu-item">
                                            <a class="sub-menu-link" href="#">Add/Edit Product</a>
                                    </li>
                                </ul>
                            </div>
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
                                            <a class="sub-menu-link" href="#">Listing</a>
                                    </li>
                                    <li class="sub-menu-item">
                                            <a class="sub-menu-link" href="#">Add/Edit Categories</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li class="menu-item">
                            <a class="menu-link" href="#">
                                <span class="nav-icon">
                                    <i class="ri-group-2-line"></i>
                                </span>
                                <span class="nav-text"> Customers </span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a class="menu-link" href="#">
                                <span class="nav-icon">
                                    <i class="ri-restaurant-line"></i>
                                </span>
                                <span class="nav-text"> Menu Cards </span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a class="menu-link" href="#">
                                <span class="nav-icon">
                                    <i class="ri-mac-line"></i>
                                </span>
                                <span class="nav-text"> POS </span>
                            </a>
                        </li>

                </ul>
            </div>
        </div>
        <header class="topbar d-flex">
            <!-- Sidebar Logo -->
            <div class="logo-box">
                <a href="#" class="logo-dark">
                        <img src="assets/images/logo-sm.png" class="logo-sm" alt="logo sm">
                        <img src="assets/images/logo-dark.png" class="logo-lg" alt="logo dark">
                </a>

                <a href="#" class="logo-light">
                        <img src="assets/images/logo-sm.png" class="logo-sm" alt="logo sm">
                        <img src="{{asset('assets/images/sling-logo.png')}}" class="logo-lg" alt="logo light">
                </a>
            </div>

            <div class="container">
                <div class="navbar-header">

                        <!-- Menu Toggle Button (sm-hover) -->
                        <button type="button" class="btn btn-link d-flex button-sm-hover button-toggle-menu" aria-label="Show Full Sidebar">
                            <i class="ri-menu-2-line button-sm-hover-icon text-white"></i>
                        </button>

                        <div class="d-flex align-items-center gap-2">
                            <!-- App Search-->
                            <form class="app-search d-none d-md-block me-auto">
                                <div class="position-relative">
                                    <input type="search" class="form-control" placeholder="Start typing..." autocomplete="off" value="">
                                    <i class="ri-search-line search-widget-icon"></i>
                                </div>
                            </form>
                        </div>

                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <!-- Theme Color (Light/Dark) -->
                            <div class="topbar-item">
                                <button type="button" class="topbar-button" id="light-dark-mode">
                                    <i class="ri-moon-line fs-20 align-middle light-mode"></i>
                                    <i class="ri-sun-line fs-20 align-middle dark-mode"></i>
                                </button>
                            </div>

                            <!-- Notification -->
                            <div class="dropdown topbar-item">
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
                                            <!-- Item -->
                                            <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom text-wrap">
                                                <p class="mb-0"><span class="fw-medium">Olivia Bennett</span> mentioned you in a comment <span>"This update really improves the user experience! 🚀"</span></p>
                                            </a>
                                            <!-- Item -->
                                            <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                                <p class="mb-0 fw-semibold">Daniel Roberts</p>
                                                <p class="mb-0 text-wrap">
                                                    Just sent over the revised proposal. Let me know your thoughts.
                                                </p>
                                            </a>
                                            <!-- Item -->
                                            <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                                <p class="mb-0 fw-semibold">Rachel Green</p>
                                                <p class="mb-0 text-wrap">
                                                    Approved your request for the new project timeline. ✅
                                                </p>
                                            </a>
                                            <!-- Item -->
                                            <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                                <p class="mb-0 fw-semibold text-wrap">You have <b>8</b> new project updates awaiting review.</p>
                                            </a>
                                            <!-- Item -->
                                            <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                                <p class="mb-0 fw-semibold">Ethan Williams</p>
                                                <p class="mb-0 text-wrap">
                                                    Uploaded the latest marketing report for your review.
                                                </p>
                                            </a>

                                    </div>
                                </div>
                            </div>

                            <!-- User -->
                            <div class="dropdown topbar-item">
                                <a type="button" class="topbar-button p-0" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="d-flex align-items-center gap-2">
                                            <img class="rounded-circle" width="32" src="assets/images/users/avatar-1.jpg" alt="user-image">
                                            <span class="d-lg-flex flex-column gap-1 d-none">
                                                <h5 class="my-0 fs-13 text-uppercase text-reset fw-bold">Doris Lietz</h5>
                                            </span>
                                    </span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">

                                    <a class="dropdown-item" href="#">
                                            <i class="bx bx-user-circle fs-18 align-middle me-2"></i><span class="align-middle">My Account</span>
                                    </a>

                                   
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
                                    <a class="dropdown-item" href="#">
                                            <i class="bx bx-log-out fs-18 align-middle me-2"></i><span class="align-middle">Logout</span>
                                    </a>
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
            <footer class="footer">
                <div class="container">
                    <div class="row">
                        <div class="col-12 text-center">
                            <script>document.write(new Date().getFullYear())</script> &copy; Metor. All rights reserved by FoxPixel</a>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- ========== Footer End ========== -->
        </div>
        <!-- End Page Content -->
    </div>
	@yield('modal')

	@yield('script')
    <script src="{{ asset('assets/js/vendor.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
	<!-- Dashboard Js -->
    <script src="{{ asset('assets/js/pages/dashboard.js') }}"></script>
</body>
</html>