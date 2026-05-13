<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', 'App\Http\Controllers\api\authController@login');

// Admin Login (separate — does not require slug_name)
Route::post('admin/login', 'App\Http\Controllers\api\admin\authAdminController@login');

Route::middleware('auth:sanctum')->group(function () {

	//Category
	Route::get('/categories/list', 'App\Http\Controllers\api\categoryController@list');
	Route::post('/categories/store', 'App\Http\Controllers\api\categoryController@store');
	Route::get('/categories/{category}/view', 'App\Http\Controllers\api\categoryController@view');
	Route::get('/categories/{category}/status', 'App\Http\Controllers\api\categoryController@status');
	Route::post('/categories/update', 'App\Http\Controllers\api\categoryController@update');

	//Sub Category
	Route::get('/sub_categories/list', 'App\Http\Controllers\api\subCategoryController@list');
	Route::post('/sub_categories/store', 'App\Http\Controllers\api\subCategoryController@store');
	Route::get('/sub_categories/{sub_category}/view', 'App\Http\Controllers\api\subCategoryController@view');
	Route::get('/sub_categories/{sub_category}/status', 'App\Http\Controllers\api\subCategoryController@status');
	Route::post('/sub_categories/update', 'App\Http\Controllers\api\subCategoryController@update');

	//Tax
	Route::get('/taxes/list', 'App\Http\Controllers\api\taxController@list');
	Route::post('/taxes/store', 'App\Http\Controllers\api\taxController@store');
	Route::get('/taxes/{tax}/view', 'App\Http\Controllers\api\taxController@view');
	Route::get('/taxes/{tax}/status', 'App\Http\Controllers\api\taxController@status');
	Route::post('/taxes/update', 'App\Http\Controllers\api\taxController@update');

	//Metric
	Route::get('/metrics/list', 'App\Http\Controllers\api\metricController@list');
	Route::post('/metrics/store', 'App\Http\Controllers\api\metricController@store');
	Route::get('/metrics/{metric}/view', 'App\Http\Controllers\api\metricController@view');
	Route::get('/metrics/{metric}/status', 'App\Http\Controllers\api\metricController@status');
	Route::post('/metrics/update', 'App\Http\Controllers\api\metricController@update');

	//Product
	Route::get('/products/list', 'App\Http\Controllers\api\productController@list');
	Route::post('/products/store', 'App\Http\Controllers\api\productController@store');
	Route::get('/products/{product}/view', 'App\Http\Controllers\api\productController@view');
	Route::get('/products/{product}/status', 'App\Http\Controllers\api\productController@status');
	Route::post('/products/update', 'App\Http\Controllers\api\productController@update');

	//Finance
	Route::get('/finances/list', 'App\Http\Controllers\api\financeController@list');
	Route::post('/finances/store', 'App\Http\Controllers\api\financeController@store');
	Route::get('/finances/{finance}/view', 'App\Http\Controllers\api\financeController@view');
	Route::get('/finances/{finance}/status', 'App\Http\Controllers\api\financeController@status');
	Route::post('/finances/update', 'App\Http\Controllers\api\financeController@update');

	//Payment
	Route::get('/payments/list', 'App\Http\Controllers\api\paymentController@list');
	Route::get('/payments/{payment}/update', 'App\Http\Controllers\api\paymentController@update');

	//Staff
	Route::get('/staffs/list', 'App\Http\Controllers\api\staffController@list');
	Route::post('/staffs/store', 'App\Http\Controllers\api\staffController@store');
	Route::get('/staffs/{staff}/view', 'App\Http\Controllers\api\staffController@view');
	Route::get('/staffs/{staff}/status', 'App\Http\Controllers\api\staffController@status');
	Route::post('/staffs/update', 'App\Http\Controllers\api\staffController@update');

	//Bill Setting
	Route::get('/bills/{branch}/list', 'App\Http\Controllers\api\billController@list');
	Route::post('/bills/store', 'App\Http\Controllers\api\billController@store');


	//POS
	Route::get('/pos/product', 'App\Http\Controllers\api\posController@product');
	Route::get('/pos/{product}/get_product_detail', 'App\Http\Controllers\api\posController@get_product_detail');
	Route::get('/pos/customer', 'App\Http\Controllers\api\posController@customer');
	Route::post('/pos/store', 'App\Http\Controllers\api\posController@store');
	Route::post('/pos/pagination_setting', 'App\Http\Controllers\api\posController@pagination_setting');

	//Customer
	Route::get('/customers', 'App\Http\Controllers\api\customerController@customer');
	Route::post('/customers/store', 'App\Http\Controllers\api\customerController@store');
	Route::get('/customers/{customer}/view', 'App\Http\Controllers\api\customerController@view');
	Route::post('/customers/update', 'App\Http\Controllers\api\customerController@update');
	Route::get('/customers/{customer}/order', 'App\Http\Controllers\api\customerController@order');

	//Order
	Route::get('/orders', 'App\Http\Controllers\api\orderController@order');
	Route::get('/orders/{order}/view', 'App\Http\Controllers\api\orderController@view');

	//Vendor
	Route::get('/vendors/list', 'App\Http\Controllers\api\vendorController@list');
	Route::post('/vendors/store', 'App\Http\Controllers\api\vendorController@store');
	Route::get('/vendors/{vendor}/view', 'App\Http\Controllers\api\vendorController@view');
	Route::get('/vendors/{vendor}/status', 'App\Http\Controllers\api\vendorController@status');
	Route::post('/vendors/update', 'App\Http\Controllers\api\vendorController@update');

	//Vendor Ledger
	Route::get('/vendors/{vendor}/ledger', 'App\Http\Controllers\api\vendorLedgerController@index');
	Route::get('/vendors/{vendor}/payments', 'App\Http\Controllers\api\vendorLedgerController@get_payments');
	Route::post('/vendors/payments/store', 'App\Http\Controllers\api\vendorLedgerController@store_payment');

	//Purchase Orders
	Route::get('/purchase_orders', 'App\Http\Controllers\api\purchaseOrderController@list');
	Route::get('/purchase_orders/create_data', 'App\Http\Controllers\api\purchaseOrderController@create_data');
	Route::get('/purchase_orders/get_categories', 'App\Http\Controllers\api\purchaseOrderController@get_categories');
	Route::get('/purchase_orders/get_product', 'App\Http\Controllers\api\purchaseOrderController@get_product');
	Route::get('/purchase_orders/get_product_detail', 'App\Http\Controllers\api\purchaseOrderController@get_product_detail');
	Route::get('/purchase_orders/get_stock_variations', 'App\Http\Controllers\api\purchaseOrderController@get_stock_variations');
	Route::get('/purchase_orders/get_product_stock', 'App\Http\Controllers\api\purchaseOrderController@get_product_stock');
	Route::post('/purchase_orders/store', 'App\Http\Controllers\api\purchaseOrderController@store');
	Route::post('/purchase_orders/update', 'App\Http\Controllers\api\purchaseOrderController@update');
	Route::get('/purchase_orders/{id}/detail', 'App\Http\Controllers\api\purchaseOrderController@detail');
	Route::post('/purchase_orders/refund', 'App\Http\Controllers\api\purchaseOrderController@refund');

	//Sizes
	Route::get('/sizes/list', 'App\Http\Controllers\api\sizeController@list');
	Route::post('/sizes/store', 'App\Http\Controllers\api\sizeController@store');
	Route::get('/sizes/{size}/view', 'App\Http\Controllers\api\sizeController@view');
	Route::get('/sizes/{size}/status', 'App\Http\Controllers\api\sizeController@status');
	Route::post('/sizes/update', 'App\Http\Controllers\api\sizeController@update');

	//Colours
	Route::get('/colours/list', 'App\Http\Controllers\api\colourController@list');
	Route::post('/colours/store', 'App\Http\Controllers\api\colourController@store');
	Route::get('/colours/{colour}/view', 'App\Http\Controllers\api\colourController@view');
	Route::get('/colours/{colour}/status', 'App\Http\Controllers\api\colourController@status');
	Route::post('/colours/update', 'App\Http\Controllers\api\colourController@update');

	//Inventory - HO Stock & Transfer
	Route::get('/inventory/stock', 'App\Http\Controllers\api\inventoryController@stock');
	Route::get('/inventory/stock/{stock}/variations', 'App\Http\Controllers\api\inventoryController@get_stock_variation');
	Route::get('/inventory/transfer', 'App\Http\Controllers\api\inventoryController@transfer');
	Route::get('/inventory/transfer/{id}/bill', 'App\Http\Controllers\api\inventoryController@get_transfer_bill');
	Route::get('/inventory/get_sub_category', 'App\Http\Controllers\api\inventoryController@get_sub_category');
	Route::get('/inventory/get_product', 'App\Http\Controllers\api\inventoryController@get_product');
	Route::get('/inventory/get_product_detail', 'App\Http\Controllers\api\inventoryController@get_product_detail');
	Route::post('/inventory/transfer/store', 'App\Http\Controllers\api\inventoryController@store');

	//Branch Stock & Transfer
	Route::get('/branch/stock', 'App\Http\Controllers\api\branchStockController@index');
	Route::get('/branch/stock/{stock}/variations', 'App\Http\Controllers\api\branchStockController@get_stock_variation');
	Route::get('/branch/transfer', 'App\Http\Controllers\api\branchStockController@transfer_list');
	Route::get('/branch/transfer/{id}/bill', 'App\Http\Controllers\api\branchStockController@get_transfer_bill');
	Route::get('/branch/get_sub_category', 'App\Http\Controllers\api\branchStockController@get_sub_category');
	Route::get('/branch/get_product', 'App\Http\Controllers\api\branchStockController@get_product');
	Route::get('/branch/get_product_detail', 'App\Http\Controllers\api\branchStockController@get_product_detail');
	Route::post('/branch/transfer/store', 'App\Http\Controllers\api\branchStockController@store');

	//GST Billing - HO
	Route::get('/gst_bills', 'App\Http\Controllers\api\gstBillingController@index');
	Route::get('/gst_bills/create_data', 'App\Http\Controllers\api\gstBillingController@create_data');
	Route::get('/gst_bills/get_sub_category', 'App\Http\Controllers\api\gstBillingController@get_sub_category');
	Route::get('/gst_bills/get_product', 'App\Http\Controllers\api\gstBillingController@get_product');
	Route::post('/gst_bills/store', 'App\Http\Controllers\api\gstBillingController@store');
	Route::get('/gst_bills/{id}/view', 'App\Http\Controllers\api\gstBillingController@view_bill');
	Route::post('/gst_bills/bulk_upload', 'App\Http\Controllers\api\gstBillingController@bulk_upload');

	//GST Billing - Branch
	Route::get('/branch/gst_bills', 'App\Http\Controllers\api\branchGstBillController@index');
	Route::get('/branch/gst_bills/create_data', 'App\Http\Controllers\api\branchGstBillController@create_data');
	Route::get('/branch/gst_bills/get_sub_category', 'App\Http\Controllers\api\branchGstBillController@get_sub_category');
	Route::get('/branch/gst_bills/get_product', 'App\Http\Controllers\api\branchGstBillController@get_product');
	Route::post('/branch/gst_bills/store', 'App\Http\Controllers\api\branchGstBillController@store');
	Route::get('/branch/gst_bills/{id}/view', 'App\Http\Controllers\api\branchGstBillController@view_bill');
	Route::post('/branch/gst_bills/bulk_upload', 'App\Http\Controllers\api\branchGstBillController@bulk_upload');

	//Credits - HO
	Route::get('/ho/credits', 'App\Http\Controllers\api\creditsController@credit');
	Route::get('/ho/credits/{id}/payments', 'App\Http\Controllers\api\creditsController@getCreditPayments');
	Route::post('/ho/credits/payments/store', 'App\Http\Controllers\api\creditsController@store');

	//Credits - Branch
	Route::get('/branch/credits', 'App\Http\Controllers\api\branchCreditController@credit');
	Route::get('/branch/credits/{id}/payments', 'App\Http\Controllers\api\branchCreditController@getCreditPayments');
	Route::post('/branch/credits/payments/store', 'App\Http\Controllers\api\branchCreditController@store');

	//Dashboard - HO
	Route::get('/dashboard', 'App\Http\Controllers\api\dashboardController@index');

	//Dashboard - Branch
	Route::get('/branch/dashboard', 'App\Http\Controllers\api\branchDashboardController@index');

	//Branch Billing (POS)
	Route::get('/branch/billing', 'App\Http\Controllers\api\branchBillingController@billing');
	Route::get('/branch/billing/get_sub_category', 'App\Http\Controllers\api\branchBillingController@get_sub_category');
	Route::get('/branch/billing/get_imei_product', 'App\Http\Controllers\api\branchBillingController@get_imei_product');
	Route::get('/branch/billing/get_product', 'App\Http\Controllers\api\branchBillingController@get_product');
	Route::get('/branch/billing/get_product_detail', 'App\Http\Controllers\api\branchBillingController@get_product_detail');
	Route::get('/branch/billing/get_variation_detail', 'App\Http\Controllers\api\branchBillingController@get_variation_detail');
	Route::get('/branch/billing/suggest_phone', 'App\Http\Controllers\api\branchBillingController@suggest_phone');
	Route::get('/branch/billing/get_customer_detail', 'App\Http\Controllers\api\branchBillingController@get_customer_detail');
	Route::post('/branch/billing/customer_store', 'App\Http\Controllers\api\branchBillingController@customer_store');
	Route::post('/branch/billing/store', 'App\Http\Controllers\api\branchBillingController@store');
	Route::get('/branch/billing/{id}/get_bill', 'App\Http\Controllers\api\branchBillingController@get_bill');

	//Branch Orders & Refunds
	Route::get('/branch/orders', 'App\Http\Controllers\api\branchOrderController@index');
	Route::get('/branch/orders/{id}/refund_data', 'App\Http\Controllers\api\branchOrderController@refund_data');
	Route::post('/branch/orders/refund', 'App\Http\Controllers\api\branchOrderController@refunded');

	//Reports - HO
	Route::get('/reports/daily', 'App\Http\Controllers\api\reportsController@daily');
	Route::get('/reports/orders', 'App\Http\Controllers\api\reportsController@orders');
	Route::get('/reports/sales', 'App\Http\Controllers\api\reportsController@sales');
	Route::get('/reports/purchase', 'App\Http\Controllers\api\reportsController@purchase');
	Route::get('/reports/transfer', 'App\Http\Controllers\api\reportsController@transfer');

	//Reports - Branch
	Route::get('/branch/reports/daily', 'App\Http\Controllers\api\branchReportsController@daily');
	Route::get('/branch/reports/orders', 'App\Http\Controllers\api\branchReportsController@orders');
	Route::get('/branch/reports/sales', 'App\Http\Controllers\api\branchReportsController@sales');
	Route::get('/branch/reports/transfer', 'App\Http\Controllers\api\branchReportsController@transfer');

	//Profile
	Route::get('/profile', 'App\Http\Controllers\api\profileController@my_profile');

	//Notification
	Route::get('notifications/{type?}', 'App\Http\Controllers\api\notificationController@notification');


	//General
	Route::get('genders', 'App\Http\Controllers\api\generalController@gender');
	Route::get('payment_list', 'App\Http\Controllers\api\generalController@payment_list');
	Route::get('finances', 'App\Http\Controllers\api\generalController@finance');
	Route::get('categories', 'App\Http\Controllers\api\generalController@category');
	Route::get('{category}/sub_categories', 'App\Http\Controllers\api\generalController@sub_category');
	Route::get('staffs', 'App\Http\Controllers\api\generalController@staff');
	Route::get('branches', 'App\Http\Controllers\api\generalController@branch'); //For HO

	Route::post('logout', 'App\Http\Controllers\api\authController@logout');

});

// ─── ADMIN API (role_id = 1 / Super Admin) ───────────────────────────────────
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

	Route::post('logout', 'App\Http\Controllers\api\admin\authAdminController@logout');

	// Dashboard & Profile
	Route::get('dashboard', 'App\Http\Controllers\api\admin\adminDashboardController@dashboard');
	Route::get('profile',   'App\Http\Controllers\api\admin\adminDashboardController@profile');

	// Shops
	Route::get('shops',                  'App\Http\Controllers\api\admin\shopApiController@index');
	Route::get('shops/create_data',      'App\Http\Controllers\api\admin\shopApiController@create_data');
	Route::post('shops/store',           'App\Http\Controllers\api\admin\shopApiController@store');
	Route::get('shops/{id}/view',        'App\Http\Controllers\api\admin\shopApiController@view');
	Route::get('shops/{id}/edit',        'App\Http\Controllers\api\admin\shopApiController@edit');
	Route::post('shops/update',          'App\Http\Controllers\api\admin\shopApiController@update');
	Route::get('shops/{id}/lock',        'App\Http\Controllers\api\admin\shopApiController@lock');
	Route::get('shops/{id}/delete',      'App\Http\Controllers\api\admin\shopApiController@delete');

	// Branches
	Route::get('branches/{shop_id}/create_data', 'App\Http\Controllers\api\admin\branchApiController@create_data');
	Route::post('branches/store',                'App\Http\Controllers\api\admin\branchApiController@store');
	Route::get('branches/{id}/view',             'App\Http\Controllers\api\admin\branchApiController@view');
	Route::get('branches/{id}/edit',             'App\Http\Controllers\api\admin\branchApiController@edit');
	Route::post('branches/update',               'App\Http\Controllers\api\admin\branchApiController@update');
	Route::get('branches/{id}/lock',             'App\Http\Controllers\api\admin\branchApiController@lock');
	Route::get('branches/{id}/delete',           'App\Http\Controllers\api\admin\branchApiController@delete');

});