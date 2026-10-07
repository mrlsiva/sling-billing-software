<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\loginController;

use App\Http\Controllers\admin\adminController;

use App\Http\Controllers\admin\notificationsController;

use App\Http\Controllers\admin\shopController;
use App\Http\Controllers\admin\shopSetupController;
use App\Http\Controllers\admin\branchController;
use App\Http\Controllers\admin\orderController;
use App\Http\Controllers\admin\ErrorLogController;

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
	    	Route::get('/export',[shopController::class, 'export'])->name('export');
	    	Route::post('/bulk_import',[shopController::class, 'bulkImport'])->name('bulk_import');
	    	Route::get('/bulk_import/result',[shopController::class, 'bulkImportResult'])->name('bulk_import_result');
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

	Route::prefix('shop-setup')->group(function () {
		Route::name('shop_setup.')->group(function () {

			Route::get('/',[shopSetupController::class, 'index'])->name('index');
			Route::get('/{id}',[shopSetupController::class, 'show'])->name('show');
			Route::post('/{id}/tax',[shopSetupController::class, 'storeTax'])->name('tax');
			Route::post('/{id}/category',[shopSetupController::class, 'storeCategory'])->name('category');
			Route::post('/{id}/sub-category',[shopSetupController::class, 'storeSubCategory'])->name('sub_category');
			Route::post('/{id}/product',[shopSetupController::class, 'storeProduct'])->name('product');
			Route::post('/{id}/staff',[shopSetupController::class, 'storeStaff'])->name('staff');
			Route::get('/{id}/demo',[shopSetupController::class, 'previewDemo'])->name('demo_preview');
			Route::post('/{id}/demo',[shopSetupController::class, 'loadDemo'])->name('demo');
			Route::post('/{id}/demo/draft',[shopSetupController::class, 'demoDraftAction'])->name('demo_draft');
			Route::get('/{id}/demo/catalog/template',[shopSetupController::class, 'demoCatalogTemplate'])->name('demo_catalog_template');
			Route::get('/{id}/demo/catalog/current',[shopSetupController::class, 'demoCatalogCurrent'])->name('demo_catalog_current');
			Route::post('/{id}/demo/catalog/import',[shopSetupController::class, 'demoCatalogImport'])->name('demo_catalog_import');
			Route::get('/{id}/categories/export',[shopSetupController::class, 'exportCategories'])->name('category_export');
			Route::get('/{id}/sub-categories/export',[shopSetupController::class, 'exportSubCategories'])->name('sub_category_export');
			Route::get('/{id}/products/export',[shopSetupController::class, 'exportProducts'])->name('product_export');
			Route::post('/{id}/categories/import',[shopSetupController::class, 'importCategories'])->name('category_import');
			Route::post('/{id}/sub-categories/import',[shopSetupController::class, 'importSubCategories'])->name('sub_category_import');
			Route::post('/{id}/products/import',[shopSetupController::class, 'importProducts'])->name('product_import');

		});
	});

	Route::prefix('orders')->group(function () {
		Route::name('order.')->group(function () {

			Route::get('/',[orderController::class, 'index'])->name('index');
			Route::get('/export',[orderController::class, 'export'])->name('export');
			Route::post('/bulk_import',[orderController::class, 'bulkImport'])->name('bulk_import');

		});
	});

	Route::prefix('error_logs')->group(function () {

		Route::name('error_log.')->group(function () {

			Route::get('/',[ErrorLogController::class, 'index'])->name('index');

		});

	});

	Route::get('/logout',[loginController::class, 'logout'])->name('logout');

});