<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Dashboard;
use App\Http\Controllers\Admin\Authenticate;







Route::prefix('admin')->group(function () {

    Route::view('/login', 'auth.login')->name('admin.login');
    Route::view('/register', 'auth.register')->name('admin.register');
    Route::post('/authenticate', [Authenticate::class, 'adminAuthenticate'])->name('admin.authenticate');
    Route::post('/register/update', [Authenticate::class, 'registerUpdate'])->name('admin.register.update');

    Route::middleware('admin')->group(function () {

        Route::get('/dashboard', [Dashboard::class, 'index'])->name('admin.dashboard');
        Route::get('/category', [])->name('view.category');
        Route::get('/logout', [Authenticate::class, 'logout'])->name('admin.logout');
    });
});





