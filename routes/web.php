<?php

use Illuminate\Support\Facades\Route;

//Controller
use App\Http\Controllers\auth\loginController;
use App\Http\Controllers\users\categoryController;
use App\Http\Controllers\users\subCategoryController;
use App\Http\Controllers\users\productController;
use App\Http\Controllers\users\inventoryController;
use App\Http\Controllers\users\customerController;


Route::get('/', function () {
    return view('home');
});

if (request()->segment(1) === 'admin') 
{
    require __DIR__.'/admin.php';
} 
else 
{
    Route::middleware(['is_company_valid'])->group(function () {

        Route::prefix('{company}')->group(function () {

            Route::post('/sign_in',[loginController::class, 'sign_in'])->name('sign_in');

            Route::get('/', function () {
                return view('users.home');
            })->name('home');

            Route::get('/login', function () {
                return view('auth.login');
            })->name('login');

            Route::group(['middleware' => ['auth']], function () {

                Route::group(['middleware' => ['role:HO']], function () {

                    Route::get('/dashboard', function () {
                        return view('users.dashboard');
                    })->name('dashboard');

                    Route::prefix('categories')->group(function () {
                        Route::name('category.')->group(function () {

                            Route::get('/index',[categoryController::class, 'index'])->name('index');
                            Route::post('/store',[categoryController::class, 'store'])->name('store');
                            Route::get('/edit',[categoryController::class, 'edit'])->name('edit');
                            Route::post('/update',[categoryController::class, 'update'])->name('update');
                            Route::post('/status',[categoryController::class, 'status'])->name('status');
                            
                        });
                    });

                    Route::prefix('sub_categories')->group(function () {
                        Route::name('sub_category.')->group(function () {

                            Route::get('/index',[subCategoryController::class, 'index'])->name('index');
                            Route::post('/store',[subCategoryController::class, 'store'])->name('store');
                            Route::get('/edit',[subCategoryController::class, 'edit'])->name('edit');
                            Route::post('/update',[subCategoryController::class, 'update'])->name('update');
                            Route::post('/status',[subCategoryController::class, 'status'])->name('status');
                            
                        });
                    });

                    Route::prefix('products')->group(function () {
                        Route::name('product.')->group(function () {

                            Route::get('/index',[productController::class, 'index'])->name('index');
                            Route::get('/create',[productController::class, 'create'])->name('create');
                            Route::post('/store',[productController::class, 'store'])->name('store');
                            Route::get('/view',[productController::class, 'view'])->name('view');
                            Route::get('{id}/edit',[productController::class, 'edit'])->name('edit');
                            Route::post('/update',[productController::class, 'update'])->name('update');
                            Route::post('/status',[productController::class, 'status'])->name('status');
                            Route::get('/get_sub_category',[productController::class, 'get_sub_category'])->name('get_sub_category');
                            
                        });
                    });

                    Route::prefix('inventories')->group(function () {
                        Route::name('inventory.')->group(function () {

                            Route::get('/index',[inventoryController::class, 'index'])->name('index');
                            Route::get('/create',[inventoryController::class, 'create'])->name('create');
                            Route::get('/view',[inventoryController::class, 'view'])->name('view');
                            Route::get('/edit',[inventoryController::class, 'edit'])->name('edit');
                            
                        });
                    });

                    Route::prefix('customers')->group(function () {
                        Route::name('customer.')->group(function () {

                            Route::get('/index',[customerController::class, 'index'])->name('index');
                            Route::get('/create',[customerController::class, 'create'])->name('create');
                            Route::get('/view',[customerController::class, 'view'])->name('view');
                            Route::get('/edit',[customerController::class, 'edit'])->name('edit');
                            
                        });
                    });

                });

                Route::group(['middleware' => ['role:Branch']], function () {

                    Route::prefix('branches')->group(function () {
                        Route::name('branch.')->group(function () {

                            Route::get('/dashboard', function () {
                                return view('branches.dashboard');
                            })->name('dashboard');

                            Route::prefix('customers')->group(function () {
                                Route::name('customer.')->group(function () {

                                    Route::get('/index',[customerController::class, 'index'])->name('index');
                                    Route::post('/store',[customerController::class, 'store'])->name('store');
                                    Route::get('/view',[customerController::class, 'view'])->name('view');
                                    Route::get('/edit',[customerController::class, 'edit'])->name('edit');
                                    
                                });
                            });


                            Route::get('/billing', function () {
                                return view('branches.billing');
                            })->name('billing');
                        });
                    });
                });

                

                Route::get('/logout',[loginController::class, 'logout'])->name('logout');

            });

        });
    });
}