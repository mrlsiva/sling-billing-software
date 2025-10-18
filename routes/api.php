<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', 'App\Http\Controllers\api\authController@login');

Route::middleware('auth:sanctum')->group(function () {

	//POS
	Route::get('/pos/product', 'App\Http\Controllers\api\posController@product');
	Route::get('/pos/{product}/get_product_detail', 'App\Http\Controllers\api\posController@get_product_detail');
	Route::get('/pos/customer', 'App\Http\Controllers\api\posController@customer');

	//General
	Route::get('gender', 'App\Http\Controllers\api\generalController@gender');
	Route::get('payment_list', 'App\Http\Controllers\api\generalController@payment_list');
	Route::get('finance', 'App\Http\Controllers\api\generalController@finance');
	Route::get('category', 'App\Http\Controllers\api\generalController@category');
	Route::get('{category}/sub_category', 'App\Http\Controllers\api\generalController@sub_category');
	Route::get('staff', 'App\Http\Controllers\api\generalController@staff');

	Route::post('logout', 'App\Http\Controllers\api\authController@logout');

});