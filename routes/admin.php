<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Dashboard;
use App\Http\Controllers\Admin\Authenticate;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserlistController;
use App\Http\Controllers\Admin\CouponController;

Route::prefix('admin')->group(function () {

    Route::view('/login', 'auth.login')->name('admin.login');
    Route::view('/register', 'auth.register')->name('admin.register');
    Route::post('/authenticate', [Authenticate::class, 'adminAuthenticate'])->name('admin.authenticate');
    Route::post('/register/update', [Authenticate::class, 'registerUpdate'])->name('admin.register.update');

    Route::middleware('admin')->group(function () {

        Route::get('/dashboard', [Dashboard::class, 'index'])->name('admin.dashboard');

        Route::prefix('category')->group(function () {
        Route::get('/list', [CategoryController::class,'view'])->name('view.category');

        });
       Route::get('/users', [UserlistController::class, 'index'])->name('view.users');
       Route::get('/coupons', [CouponController::class, 'index'])->name('view.coupons');
         Route::post('/coupons', [CouponController::class, 'store'])->name('store.coupons');
            Route::put('/coupons/{id}', [CouponController::class, 'update'])->name('update.coupons');
            Route::delete('/coupons/{id}', [CouponController::class, 'destroy'])->name('delete.coupons');
        Route::get('/logout', [Authenticate::class, 'logout'])->name('admin.logout');
    });
});





