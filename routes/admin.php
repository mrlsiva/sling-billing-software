<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\loginController;

use App\Http\Controllers\admin\adminController;

use App\Http\Controllers\admin\notificationsController;

use App\Http\Controllers\admin\shopController;
use App\Http\Controllers\admin\branchController;

Route::get('/', function () {
	return view('auth.login');
})->name('login');

Route::post('/sign_in',[loginController::class, 'sign_in'])->name('sign_in');

Route::group(['middleware' => ['auth','role:Super Admin']], function () {

	Route::get('/dashboard',[adminController::class, 'dashboard'])->name('dashboard');

	Route::get('/my_profile',[loginController::class, 'my_profile'])->name('my_profile');

	Route::get('/notification',[notificationsController::class, 'notification'])->name('notification');

	Route::prefix('shops')->group(function () {
	    Route::name('shop.')->group(function () {

	    	Route::get('/',[shopController::class, 'index'])->name('index');
	    	Route::get('/create',[shopController::class, 'create'])->name('create');
	    	Route::post('/store',[shopController::class, 'store'])->name('store');
	    	Route::get('/{id}/view',[shopController::class, 'view'])->name('view');
	    	Route::get('/{id}/edit',[shopController::class, 'edit'])->name('edit');
	    	Route::post('/update',[shopController::class, 'update'])->name('update');
	    	Route::get('/{id}/lock',[shopController::class, 'lock'])->name('lock');
	    	Route::get('/{id}/delete',[shopController::class, 'delete'])->name('delete');
	    	
		});
	});

	Route::prefix('branches')->group(function () {
	    Route::name('branch.')->group(function () {

	    	Route::get('/{id}/create',[branchController::class, 'create'])->name('create');
	    	Route::post('/store',[branchController::class, 'store'])->name('store');
	    	Route::get('/{id}/view',[branchController::class, 'view'])->name('view');
	    	Route::get('/{id}/edit',[branchController::class, 'edit'])->name('edit');
	    	Route::post('/update',[branchController::class, 'update'])->name('update');
	    	Route::get('/{id}/lock',[branchController::class, 'lock'])->name('lock');
	    	Route::get('/{id}/delete',[branchController::class, 'delete'])->name('delete');

	    });
	});

	Route::get('/logout',[loginController::class, 'logout'])->name('logout');

});