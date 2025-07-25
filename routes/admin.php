<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\loginController;

use App\Http\Controllers\admin\shopController;

Route::get('/', function () {
	return view('auth.login');
})->name('login');

Route::group(['middleware' => ['auth','role:Super Admin']], function () {

	Route::get('/dashboard', function () {
		return view('admin.dashboard');
	})->name('dashboard');

	Route::prefix('shops')->group(function () {
	    Route::name('shop.')->group(function () {

	    	Route::get('/',[shopController::class, 'index'])->name('index');
	    	Route::get('/create',[shopController::class, 'create'])->name('create');
	    	Route::post('/store',[shopController::class, 'store'])->name('store');
	    	Route::get('/view',[shopController::class, 'view'])->name('view');
	    	Route::get('/edit',[shopController::class, 'edit'])->name('edit');
	    	Route::post('/update',[shopController::class, 'update'])->name('update');
	    	
		});
	});

	Route::get('/logout',[loginController::class, 'logout'])->name('logout');

});