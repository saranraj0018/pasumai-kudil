<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Dashboard;

Route::prefix('admin')->group(function () {

    Route::middleware('admin')->group(function () {
        Volt::route('login', 'auth.login')
            ->name('login');

        Volt::route('register', 'auth.register')
            ->name('register');
    });

    Route::get('/dashboard', Dashboard::class)->name('view.dashboard');
    Route::get('/category', \App\Livewire\Category::class)->name('view.category');
    Route::post('logout', App\Livewire\Actions\Logout::class)
        ->name('logout');
});





