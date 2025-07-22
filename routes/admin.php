<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\loginController;

Route::get('/', function () {
	return view('auth.login');
})->name('login');

Route::group(['middleware' => ['auth','role:Super Admin']], function () {

	Route::get('/dashboard', function () {
		return view('admin.dashboard');
	})->name('dashboard');

	Route::prefix('shops')->group(function () {
	    Route::name('shop.')->group(function () {
			Route::get('/', function () {
				return view('admin.shops.index');
			})->name('index');

			Route::get('/create', function () {
				return view('admin.shops.create');
			})->name('create');
		});
	});

	Route::get('/logout',[loginController::class, 'logout'])->name('logout');

});