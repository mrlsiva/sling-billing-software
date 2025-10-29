<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', 'App\Http\Controllers\api\authController@login');

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

	//Finance
	Route::get('/finances/list', 'App\Http\Controllers\api\financeController@list');
	Route::post('/finances/store', 'App\Http\Controllers\api\financeController@store');
	Route::get('/finances/{finance}/view', 'App\Http\Controllers\api\financeController@view');
	Route::get('/finances/{finance}/status', 'App\Http\Controllers\api\financeController@status');
	Route::post('/finances/update', 'App\Http\Controllers\api\financeController@update');

	//POS
	Route::get('/pos/product', 'App\Http\Controllers\api\posController@product');
	Route::get('/pos/{product}/get_product_detail', 'App\Http\Controllers\api\posController@get_product_detail');
	Route::get('/pos/customer', 'App\Http\Controllers\api\posController@customer');
	Route::post('/pos/store', 'App\Http\Controllers\api\posController@store');
	Route::post('/pos/pagination_setting', 'App\Http\Controllers\api\posController@pagination_setting');

	//Customer
	Route::get('/customers', 'App\Http\Controllers\api\customerController@customer');
	Route::get('/customers/{customer}/order', 'App\Http\Controllers\api\customerController@order');

	//Order
	Route::get('/orders', 'App\Http\Controllers\api\orderController@order');
	Route::get('/orders/{order}/view', 'App\Http\Controllers\api\orderController@view');


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