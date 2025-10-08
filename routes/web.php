<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', 'admin/login');

require __DIR__ . '/admin.php';

require __DIR__ . '/milk.php';
