<?php

use Illuminate\Support\Facades\Route;

//Controller
use App\Http\Controllers\auth\loginController;

Route::get('/', function () {
    return view('home');
});

Route::post('/sign_in',[loginController::class, 'sign_in'])->name('sign_in');

if (request()->segment(1) === 'admin') 
{
    require __DIR__.'/admin.php';
} 
else 
{
    Route::middleware(['is_company_valid'])->group(function () {

        Route::prefix('{company}')->group(function () {

            Route::get('/', function () {
                return view('users.home');
            })->name('home');

            Route::get('/login', function () {
                return view('auth.login');
            })->name('login');

            Route::group(['middleware' => ['auth']], function () {

                Route::get('/dashboard', function () {
                    return view('users.dashboard');
                })->name('dashboard');

                Route::group(['middleware' => ['role:HO']], function () {
                });

                Route::group(['middleware' => ['role:Branch']], function () {
                });

            });

        });
    });
}