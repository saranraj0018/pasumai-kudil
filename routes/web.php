<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::redirect('/', 'admin/login');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');


require __DIR__.'/auth.php';
