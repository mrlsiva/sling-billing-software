<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\notificationController;

//Controller
use App\Http\Controllers\auth\loginController;
use App\Http\Controllers\auth\homeController;
use App\Http\Controllers\users\categoryController;
use App\Http\Controllers\users\subCategoryController;
use App\Http\Controllers\users\vendorController;
use App\Http\Controllers\users\purchaseOrderController;
use App\Http\Controllers\users\ledgerController;
use App\Http\Controllers\users\productController;
use App\Http\Controllers\users\inventoryController;
use App\Http\Controllers\users\dashboardController;
use App\Http\Controllers\users\posController;
use App\Http\Controllers\users\userController;
use App\Http\Controllers\users\taxController;
use App\Http\Controllers\users\metricController;
use App\Http\Controllers\users\financeController;
use App\Http\Controllers\users\paymentController;
use App\Http\Controllers\users\billController;
use App\Http\Controllers\users\generalController;
use App\Http\Controllers\users\staffsController;
use App\Http\Controllers\users\orderReportsController;
use App\Http\Controllers\users\billingsController;
use App\Http\Controllers\users\sizeController;
use App\Http\Controllers\users\colourController;


use App\Http\Controllers\branches\customerController;
use App\Http\Controllers\branches\stockController;
use App\Http\Controllers\branches\billingController;
use App\Http\Controllers\branches\settingController;
use App\Http\Controllers\branches\branchDashboardController;
use App\Http\Controllers\branches\orderController;
use App\Http\Controllers\branches\staffController;
use App\Http\Controllers\branches\orderReportController;

Route::get('/clear', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "Cleared!!!";
});

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

                Route::get('/my_profile',[loginController::class, 'my_profile'])->name('my_profile');

                Route::get('/notifications',[notificationController::class, 'notification'])->name('notification');
                
                Route::group(['middleware' => ['role:HO']], function () {

                    Route::get('/dashboard',[dashboardController::class, 'index'])->name('dashboard');

                    Route::prefix('categories')->group(function () {
                        Route::name('category.')->group(function () {

                            Route::get('/index',[categoryController::class, 'index'])->name('index');
                            Route::post('/store',[categoryController::class, 'store'])->name('store');
                            Route::get('/edit',[categoryController::class, 'edit'])->name('edit');
                            Route::post('/update',[categoryController::class, 'update'])->name('update');
                            Route::post('/status',[categoryController::class, 'status'])->name('status');
                            Route::get('/download',[categoryController::class, 'download'])->name('download');
                            Route::post('/bulk_upload',[categoryController::class, 'bulk_upload'])->name('bulk_upload');
                            
                        });
                    });

                    Route::prefix('sub_categories')->group(function () {
                        Route::name('sub_category.')->group(function () {

                            Route::get('/index',[subCategoryController::class, 'index'])->name('index');
                            Route::post('/store',[subCategoryController::class, 'store'])->name('store');
                            Route::get('/edit',[subCategoryController::class, 'edit'])->name('edit');
                            Route::post('/update',[subCategoryController::class, 'update'])->name('update');
                            Route::post('/status',[subCategoryController::class, 'status'])->name('status');
                            Route::get('/download',[subCategoryController::class, 'download'])->name('download');
                            Route::post('/bulk_upload',[subCategoryController::class, 'bulk_upload'])->name('bulk_upload');
                            
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
                            Route::get('/download',[productController::class, 'download'])->name('download');
                            Route::post('/bulk_upload',[productController::class, 'bulk_upload'])->name('bulk_upload');
                            
                        });
                    });

                    Route::prefix('vendors')->group(function () {
                        Route::name('vendor.')->group(function () {

                            Route::get('/index',[vendorController::class, 'index'])->name('index');
                            Route::post('/store',[vendorController::class, 'store'])->name('store');
                            Route::post('/update',[vendorController::class, 'update'])->name('update');
                            Route::post('/status',[vendorController::class, 'status'])->name('status');

                            Route::prefix('ledger')->group(function () {
                                Route::name('ledger.')->group(function () {

                                    Route::get('/{id}/index',[ledgerController::class, 'index'])->name('index');
                                    Route::get('get-vendor-payments/{vendor}', [ledgerController::class, 'getPayment'])->name('getPayment');

                                });
                            });

                            Route::prefix('payments')->group(function () {
                                Route::name('payment.')->group(function () {
                                    Route::post('/store',[ledgerController::class, 'payment'])->name('store');
                                });
                            });

                            Route::prefix('purchase_orders')->group(function () {
                                Route::name('purchase_order.')->group(function () {

                                    Route::get('/index',[purchaseOrderController::class, 'index'])->name('index');
                                    Route::get('/create',[purchaseOrderController::class, 'create'])->name('create');
                                    Route::get('/get_product',[purchaseOrderController::class, 'get_product'])->name('get_product');
                                    Route::get('/get_product_detail',[purchaseOrderController::class, 'get_product_detail'])->name('get_product_detail');
                                    Route::get('/get_stock_variations', [purchaseOrderController::class, 'get_stock_variations']);
                                    Route::post('/store',[purchaseOrderController::class, 'store'])->name('store');
                                    Route::post('/update',[purchaseOrderController::class, 'update'])->name('update');
                                    Route::get('/{id}/get_detail',[purchaseOrderController::class, 'get_detail'])->name('get_detail');
                                     Route::post('/refund',[purchaseOrderController::class, 'refund'])->name('refund');

                                });
                            });

                        });
                    });

                    Route::prefix('inventories')->group(function () {
                        Route::name('inventory.')->group(function () {

                            Route::prefix('stocks')->group(function () {
                                Route::name('stock')->group(function () {

                                    Route::get('/{shop}/{branch}',[inventoryController::class, 'stock']);
                                });
                            });

                            Route::prefix('transfer')->group(function () {
                                Route::name('transfer')->group(function () {

                                    Route::get('/',[inventoryController::class, 'transfer']);
                                    Route::get('/get_sub_category',[inventoryController::class, 'get_sub_category'])->name('.get_sub_category');
                                    Route::get('/get_product',[inventoryController::class, 'get_product'])->name('get_product');
                                    Route::get('/get_product_detail',[inventoryController::class, 'get_product_detail'])->name('.get_product_detail');
                                    Route::post('/store',[inventoryController::class, 'store'])->name('.store');

                                });
                            });
                            
                        });
                    });

                    Route::prefix('billing')->group(function () {
                        Route::name('billing.')->group(function () {
                            Route::get('/pos',[billingsController::class, 'billing'])->name('pos');
                            Route::get('/get_sub_category',[billingsController::class, 'get_sub_category'])->name('get_sub_category');
                            Route::get('/get_product',[billingsController::class, 'get_product'])->name('get_product');
                            Route::get('/get_product_detail',[billingsController::class, 'get_product_detail'])->name('get_product_detail');
                            Route::get('/get_variation_detail',[billingController::class, 'get_variation_detail'])->name('get_variation_detail');
                            Route::get('/suggest-customer-phone', [billingsController::class, 'suggestPhone'])->name('suggestPhone');
                            Route::get('/get_customer_detail',[billingsController::class, 'get_customer_detail'])->name('get_customer_detail');
                            Route::post('/customer_store',[billingsController::class, 'customer_store'])->name('customer_store');
                            Route::post('/store',[billingsController::class, 'store'])->name('store');
                            Route::get('/{id}/get_bill',[billingsController::class, 'get_bill'])->name('get_bill');
                            Route::get('/{id}/view_bill',[billingsController::class, 'view_bill'])->name('view_bill');
                            Route::get('/get_imei_product',[billingsController::class, 'get_imei_product'])->name('get_imei_product');
                        });
                    });

                    Route::prefix('orders')->group(function () {
                        Route::name('order.')->group(function () {

                            Route::get('/{branch}/index',[posController::class, 'index'])->name('index');
                            Route::get('/{id}/get_bill',[posController::class, 'get_bill'])->name('get_bill');
                            Route::get('/{id}/view_bill',[posController::class, 'view_bill'])->name('view_bill');
                        });
                    });

                    Route::prefix('customers')->group(function () {
                        Route::name('customer.')->group(function () {

                            Route::get('/index',[userController::class, 'index'])->name('index');
                            Route::post('/store',[userController::class, 'store'])->name('store');
                            Route::get('/{id}/order',[userController::class, 'order'])->name('order');
                            Route::get('/download',[userController::class, 'download'])->name('download');
                        });
                    });

                    Route::prefix('staffs')->group(function () {
                        Route::name('staff.')->group(function () {

                            Route::get('/index',[staffsController::class, 'index'])->name('index');
                            Route::post('/store',[staffsController::class, 'store'])->name('store');
                            Route::post('/status',[staffsController::class, 'status'])->name('status');
                            Route::post('/update',[staffsController::class, 'update'])->name('update');

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
                                    Route::post('/update',[paymentController::class, 'update'])->name('update');
                                });
                            });

                            Route::prefix('bill')->group(function () {
                                Route::name('bill.')->group(function () {
                                    Route::get('/index/{branch?}',[billController::class, 'index'])->name('index');
                                    Route::post('/store',[billController::class, 'store'])->name('store');
                                    Route::post('/set_bank_status',[billController::class, 'set_bank_status'])->name('set_bank_status');
                                });
                            });

                            Route::prefix('general')->group(function () {
                                Route::name('general.')->group(function () {
                                    Route::get('/index',[generalController::class, 'index'])->name('index');
                                });
                            });

                            Route::prefix('sizes')->group(function () {
                                Route::name('size.')->group(function () {
                                    Route::get('/index',[sizeController::class, 'index'])->name('index');
                                    Route::post('/store',[sizeController::class, 'store'])->name('store');
                                    Route::post('/status',[sizeController::class, 'status'])->name('status');
                                    Route::get('/edit',[sizeController::class, 'edit'])->name('edit');
                                    Route::post('/update',[sizeController::class, 'update'])->name('update');
                                });
                            });

                            Route::prefix('colours')->group(function () {
                                Route::name('colour.')->group(function () {
                                    Route::get('/index',[colourController::class, 'index'])->name('index');
                                    Route::post('/store',[colourController::class, 'store'])->name('store');
                                    Route::post('/status',[colourController::class, 'status'])->name('status');
                                    Route::get('/edit',[colourController::class, 'edit'])->name('edit');
                                    Route::post('/update',[colourController::class, 'update'])->name('update');
                                });
                            });

                        });
                    });

                    Route::prefix('reports')->group(function () {
                        Route::name('report.')->group(function () {

                            Route::prefix('orders')->group(function () {
                                Route::name('order')->group(function () {

                                    Route::get('/{branch}',[orderReportsController::class, 'order']);
                                    Route::get('/{branch}/download/pdf',[orderReportsController::class, 'download_pdf'])->name('.download_pdf');
                                    Route::get('/{branch}/download/excel',[orderReportsController::class, 'download_excel'])->name('.download_excel');
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
                                    Route::get('/download',[customerController::class, 'download'])->name('download');
                                    Route::post('/bulk_upload',[customerController::class, 'bulk_upload'])->name('bulk_upload');
                                    
                                });
                            });

                            Route::prefix('products')->group(function () {
                                Route::name('product.')->group(function () {

                                    Route::get('/index',[stockController::class, 'index'])->name('index');
                                    Route::get('/{product}/qrcode',[stockController::class, 'qrcode'])->name('qrcode');
                                    Route::get('/{id}/barcode',[stockController::class, 'barcode'])->name('barcode');
                                });
                            });

                            Route::prefix('stock_transfer')->group(function () {
                                Route::name('stock_transfer.')->group(function () {

                                    Route::get('/transfer',[stockController::class, 'transfer'])->name('transfer');
                                    Route::get('/get_sub_category',[stockController::class, 'get_sub_category'])->name('get_sub_category');
                                    Route::get('/get_product',[stockController::class, 'get_product'])->name('get_product');
                                    Route::get('/get_product_detail',[stockController::class, 'get_product_detail'])->name('get_product_detail');
                                    Route::post('/store',[stockController::class, 'store'])->name('store');
                                });
                            });

                            Route::prefix('billing')->group(function () {
                                Route::name('billing.')->group(function () {
                                    Route::get('/pos',[billingController::class, 'billing'])->name('pos');
                                    Route::get('/get_sub_category',[billingController::class, 'get_sub_category'])->name('get_sub_category');
                                    Route::get('/get_product',[billingController::class, 'get_product'])->name('get_product');
                                    Route::get('/get_product_detail',[billingController::class, 'get_product_detail'])->name('get_product_detail');
                                    Route::get('/get_variation_detail',[billingController::class, 'get_variation_detail'])->name('get_variation_detail');
                                    Route::get('/suggest-customer-phone', [billingController::class, 'suggestPhone'])->name('suggestPhone');
                                    Route::get('/get_customer_detail',[billingController::class, 'get_customer_detail'])->name('get_customer_detail');
                                    Route::post('/customer_store',[billingController::class, 'customer_store'])->name('customer_store');
                                    Route::post('/store',[billingController::class, 'store'])->name('store');
                                    Route::get('/{id}/get_bill',[billingController::class, 'get_bill'])->name('get_bill');
                                    Route::get('/{id}/view_bill',[billingController::class, 'view_bill'])->name('view_bill');
                                    Route::get('/get_imei_product',[billingController::class, 'get_imei_product'])->name('get_imei_product');
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
                                Route::name('setting.')->group(function () {

                                    Route::get('/',[settingController::class, 'index'])->name('index');
                                    Route::get('/store',[settingController::class, 'store'])->name('store');
                                });
                            });

                            Route::prefix('reports')->group(function () {
                                Route::name('report.')->group(function () {

                                    Route::prefix('orders')->group(function () {
                                        Route::name('order')->group(function () {

                                            Route::get('/',[orderReportController::class, 'order']);
                                            Route::get('/download/pdf',[orderReportController::class, 'download_pdf'])->name('.download_pdf');
                                            Route::get('/download/excel',[orderReportController::class, 'download_excel'])->name('.download_excel');
                                        });
                                    });
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