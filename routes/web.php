<?php

use Illuminate\Support\Facades\Route;

//Controller
use App\Http\Controllers\auth\loginController;
use App\Http\Controllers\auth\homeController;
use App\Http\Controllers\users\categoryController;
use App\Http\Controllers\users\subCategoryController;
use App\Http\Controllers\users\vendorController;
use App\Http\Controllers\users\productController;
use App\Http\Controllers\users\inventoryController;
use App\Http\Controllers\users\dashboardController;
use App\Http\Controllers\users\posController;
use App\Http\Controllers\users\userController;
use App\Http\Controllers\users\taxController;
use App\Http\Controllers\users\metricController;
use App\Http\Controllers\users\financeController;
use App\Http\Controllers\users\paymentController;


use App\Http\Controllers\branches\customerController;
use App\Http\Controllers\branches\stockController;
use App\Http\Controllers\branches\billingController;
use App\Http\Controllers\branches\settingController;
use App\Http\Controllers\branches\branchDashboardController;
use App\Http\Controllers\branches\orderController;
use App\Http\Controllers\branches\staffController;


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

            Route::get('/',[homeController::class, 'home'])->name('home');

            Route::get('/login', function () {
                return view('auth.login');
            })->name('login');

            Route::group(['middleware' => ['auth']], function () {

                Route::group(['middleware' => ['role:HO']], function () {

                    Route::get('/dashboard',[dashboardController::class, 'index'])->name('dashboard');

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

                    Route::prefix('vendors')->group(function () {
                        Route::name('vendor.')->group(function () {

                            Route::get('/index',[vendorController::class, 'index'])->name('index');
                            Route::post('/store',[vendorController::class, 'store'])->name('store');
                            Route::post('/update',[vendorController::class, 'update'])->name('update');
                            Route::post('/status',[vendorController::class, 'status'])->name('status');

                        });
                    });

                    Route::prefix('inventories')->group(function () {
                        Route::name('inventory.')->group(function () {

                            Route::get('/{shop}/{branch}/transfer',[inventoryController::class, 'transfer'])->name('transfer');
                            Route::get('/get_sub_category',[inventoryController::class, 'get_sub_category'])->name('get_sub_category');
                            Route::get('/get_product',[inventoryController::class, 'get_product'])->name('get_product');
                            Route::get('/get_product_detail',[inventoryController::class, 'get_product_detail'])->name('get_product_detail');
                            Route::post('/transfer',[inventoryController::class, 'transfered'])->name('transfered');
                            
                        });
                    });

                    Route::prefix('orders')->group(function () {
                        Route::name('order.')->group(function () {

                            Route::get('/{branch}/index',[posController::class, 'index'])->name('index');
                            Route::get('/{id}/get_bill',[posController::class, 'get_bill'])->name('get_bill');
                        });
                    });

                    Route::prefix('customers')->group(function () {
                        Route::name('customer.')->group(function () {

                            Route::get('/index',[userController::class, 'index'])->name('index');
                            Route::get('/{id}/order',[userController::class, 'order'])->name('order');
                        });
                    });

                    Route::prefix('settings')->group(function () {
                        Route::name('setting.')->group(function () {

                            Route::prefix('taxes')->group(function () {
                                Route::name('tax.')->group(function () {

                                    Route::get('/index',[taxController::class, 'index'])->name('index');
                                    Route::post('/store',[taxController::class, 'store'])->name('store');
                                    Route::post('/status',[taxController::class, 'status'])->name('status');
                                    Route::get('/edit',[taxController::class, 'edit'])->name('edit');
                                    Route::post('/update',[taxController::class, 'update'])->name('update');
                                });
                            });

                            Route::prefix('metrics')->group(function () {
                                Route::name('metric.')->group(function () {

                                    Route::get('/index',[metricController::class, 'index'])->name('index');
                                    Route::post('/store',[metricController::class, 'store'])->name('store');
                                    Route::post('/status',[metricController::class, 'status'])->name('status');
                                    Route::get('/edit',[metricController::class, 'edit'])->name('edit');
                                    Route::post('/update',[metricController::class, 'update'])->name('update');
                                });
                            });

                            Route::prefix('finances')->group(function () {
                                Route::name('finance.')->group(function () {

                                    Route::get('/index',[financeController::class, 'index'])->name('index');
                                    Route::post('/store',[financeController::class, 'store'])->name('store');
                                    Route::post('/status',[financeController::class, 'status'])->name('status');
                                    Route::get('/edit',[financeController::class, 'edit'])->name('edit');
                                    Route::post('/update',[financeController::class, 'update'])->name('update');
                                });
                            });

                            Route::prefix('payments')->group(function () {
                                Route::name('payment.')->group(function () {

                                    Route::get('/index',[paymentController::class, 'index'])->name('index');
                                    Route::post('/store',[paymentController::class, 'store'])->name('store');
                                });
                            });


                        });
                    });

                });

                Route::group(['middleware' => ['role:Branch']], function () {

                    Route::prefix('branches')->group(function () {
                        Route::name('branch.')->group(function () {

                            Route::get('/dashboard',[branchDashboardController::class, 'index'])->name('dashboard');

                            Route::prefix('customers')->group(function () {
                                Route::name('customer.')->group(function () {

                                    Route::get('/index',[customerController::class, 'index'])->name('index');
                                    Route::post('/store',[customerController::class, 'store'])->name('store');
                                    Route::get('/view',[customerController::class, 'view'])->name('view');
                                    Route::get('/{id}/edit',[customerController::class, 'edit'])->name('edit');
                                    Route::post('/update',[customerController::class, 'update'])->name('update');
                                    Route::get('/{id}/order',[customerController::class, 'order'])->name('order');
                                    
                                });
                            });

                            Route::prefix('products')->group(function () {
                                Route::name('product.')->group(function () {

                                    Route::get('/index',[stockController::class, 'index'])->name('index');
                                });
                            });

                            Route::prefix('billing')->group(function () {
                                Route::name('billing.')->group(function () {
                                    Route::get('/pos',[billingController::class, 'billing'])->name('pos');
                                    Route::get('/get_sub_category',[billingController::class, 'get_sub_category'])->name('get_sub_category');
                                    Route::get('/get_product',[billingController::class, 'get_product'])->name('get_product');
                                    Route::get('/get_product_detail',[billingController::class, 'get_product_detail'])->name('get_product_detail');
                                    Route::get('/suggest-customer-phone', [billingController::class, 'suggestPhone'])->name('suggestPhone');
                                    Route::get('/get_customer_detail',[billingController::class, 'get_customer_detail'])->name('get_customer_detail');
                                    Route::post('/customer_store',[billingController::class, 'customer_store'])->name('customer_store');
                                    Route::post('/store',[billingController::class, 'store'])->name('store');
                                    Route::get('/{id}/get_bill',[billingController::class, 'get_bill'])->name('get_bill');
                                });
                            });

                            Route::prefix('orders')->group(function () {
                                Route::name('order.')->group(function () {

                                    Route::get('/index',[orderController::class, 'index'])->name('index');
                                    Route::get('/{id}/refund',[orderController::class, 'refund'])->name('refund');
                                    Route::post('/refund',[orderController::class, 'refunded'])->name('refunded');
                                });
                            });

                            Route::prefix('staffs')->group(function () {
                                Route::name('staff.')->group(function () {

                                    Route::get('/index',[staffController::class, 'index'])->name('index');
                                    Route::get('/create',[staffController::class, 'create'])->name('create');
                                    Route::post('/store',[staffController::class, 'store'])->name('store');
                                    Route::post('/status',[staffController::class, 'status'])->name('status');
                                    Route::post('/update',[staffController::class, 'update'])->name('update');
                                });
                            });

                            Route::prefix('settings')->group(function () {
                                Route::name('setting')->group(function () {

                                    Route::get('/',[settingController::class, 'index']);
                                });
                            });

                        });
                    });
                });

                

                Route::get('/logout',[loginController::class, 'logout'])->name('logout');

            });

        });
    });
}