<?php

use Illuminate\Support\Facades\Route;



Route::prefix('users/')->group(function () {

	Route::middleware(['is_url_valid'])->group(function () {

        Route::prefix('{company}')->group(function () {

			Route::get('/products', 'App\Http\Controllers\ecommerce\productController@list');

		});

	});

});