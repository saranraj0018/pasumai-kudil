<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Dashboard;
use App\Http\Controllers\Admin\Authenticate;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductsController;

Route::prefix('admin')->group(function () {

    Route::middleware(['guest'])->as('admin.')->group(function () {
        Route::view('/login', 'auth.login')->name('login');
        Route::view('/register', 'auth.register')->name('register');

        Route::controller(Authenticate::class)->group(function () {
            Route::post('/authenticate', 'adminAuthenticate')->name('authenticate');
            Route::post('/register/update', 'registerUpdate')->name('register.update');
        });
    });

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [Dashboard::class, 'index'])->name('admin.dashboard');

        Route::prefix('category')->group(function () {
        Route::get('/list', [CategoryController::class,'view'])->name('view.category');
        Route::post('/save', [CategoryController::class,'save'])->name('save.category');
        });

        Route::prefix('products')->controller(ProductsController::class)->group(function () {
            Route::get('/', 'index')->name('products');
        });

        Route::get('/logout', [Authenticate::class, 'logout'])->name('admin.logout');
    });
});
