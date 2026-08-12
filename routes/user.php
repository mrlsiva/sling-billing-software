<?php

use Illuminate\Support\Facades\Route;


Route::middleware(['is_url_valid'])->group(function () {

	Route::prefix('{company}')->group(function () {

		Route::prefix('customers')->group(function () {

			Route::post('/register', 'App\Http\Controllers\ecommerce\authController@register');
			Route::post('/login', 'App\Http\Controllers\ecommerce\authController@login');
		});

		Route::get('/categories', 'App\Http\Controllers\ecommerce\productController@categories');
		Route::get('/sub_categories', 'App\Http\Controllers\ecommerce\productController@sub_categories');
		Route::get('/products', 'App\Http\Controllers\ecommerce\productController@list');

		Route::middleware('auth:sanctum')->group(function () {

			Route::prefix('profile')->group(function () {

				Route::get('/view', 'App\Http\Controllers\ecommerce\authController@view');
				Route::post('/update', 'App\Http\Controllers\ecommerce\authController@update');
			});

			Route::prefix('orders')->group(function () {

				Route::post('/store', 'App\Http\Controllers\ecommerce\orderController@store');
				Route::get('/list', 'App\Http\Controllers\ecommerce\orderController@list');
				Route::get('/view/{id}', 'App\Http\Controllers\ecommerce\orderController@view');
			});

		});

	});

});

