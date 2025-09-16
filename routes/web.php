<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::redirect('/', 'admin/login');

require __DIR__.'/admin.php';
