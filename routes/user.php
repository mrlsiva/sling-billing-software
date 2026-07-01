<?php

use Illuminate\Support\Facades\Route;


Route::middleware(['is_url_valid'])->group(function () {

	Route::prefix('{company}')->group(function () {

		Route::prefix('customers')->group(function () {

			Route::post('/register', 'App\Http\Controllers\ecommerce\authController@register');
			Route::post('/login', 'App\Http\Controllers\ecommerce\authController@login');
		});

		Route::get('/products', 'App\Http\Controllers\ecommerce\productController@list');

	});

});

