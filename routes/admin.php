<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
	return view('auth.login');
})->name('login');

Route::group(['middleware' => ['auth','role:Super Admin']], function () {

	Route::get('/dashboard', function () {
		return view('admin.dashboard');
	})->name('dashboard');

});