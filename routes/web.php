<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

if (request()->segment(1) === 'admin') 
{
    require __DIR__.'/admin.php';
} 
else 
{

    Route::prefix('{company}')->group(function () {

        Route::get('/', function () {
            return view('users.home');
        });

        Route::get('/login', function () {
            return view('auth.login');
        });

        Route::get('/dashboard', function () {
            return view('users.dashboard');
        })->name('dashboard');

    });
}