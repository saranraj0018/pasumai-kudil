<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Milk\SubscriptionController;

 Route::middleware('verify.jwt')->group(function () {

  Route::get('/subscriptions', [SubscriptionController::class, 'index']);
  });
