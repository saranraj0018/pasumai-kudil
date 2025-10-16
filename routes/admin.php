<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Dashboard;
use App\Http\Controllers\Admin\Authenticate;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserlistController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\HubController;


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

        //categories
        Route::prefix('category')->controller(CategoryController::class)->group(function () {
            Route::get('/list', 'view')->name('view.category');
            Route::post('/save', 'save')->name('save.category');
            Route::post('/delete', 'destroy')->name('delete.category');
        });

        //banners
        Route::prefix('banner')->controller(BannerController::class)->group(function () {
            Route::get('/list', 'view')->name('view.banner');
            Route::post('/save', 'save')->name('save.banner');
            Route::post('/delete', 'destroy')->name('delete.banner');
        });

       //user list
        Route::get('/users', [UserlistController::class, 'index'])->name('view.users');

        //orders
        Route::prefix('orders')->controller(OrderController::class)->group(function () {
            Route::get('/list', 'view')->name('view.orders');
            Route::post('/update-status', 'updateStatus')->name('update.order.status');
        });

        // hub
        Route::prefix('hub')->controller(HubController::class)->group(function () {
            Route::get('/list', 'view')->name('list.hub');
        });

        //coupons
        Route::prefix('coupon')->controller(CouponController::class)->group(function () {
            Route::get('/list', 'view')->name('view.coupons');
            Route::post('/save', 'save')->name('save.coupon');
            Route::post('/delete', 'destroy')->name('delete.coupon');
        });

        Route::prefix('products')->controller(ProductsController::class)->group(function () {
            Route::get('/lists', 'productLists')->name('lists.products');
            Route::post('/save_product', 'saveProduct')->name('save_product.products');
            Route::post('/delete_product', 'deleteProduct')->name('delete_product.products');
            Route::get('/search_product', 'searchProduct')->name('search_product.products');
            Route::get('/edit_product', 'editProduct')->name('edit_product.products');
        });

        Route::prefix('users')->controller(UserlistController::class)->group(function () {
            Route::get('/lists', 'userLists')->name('lists.users');
            Route::get('/user-profile-view', 'userProfileView')->name('user_view.users');
            Route::get('/transaction-history', 'transactionHistory')->name('transaction_history.users');
            Route::post('/add_wallet', 'addWallet')->name('add_wallet.users');

        });

        Route::prefix('shipping')->controller(ShippingController::class)->group(function () {
            Route::get('/shipping', 'index')->name('lists.shipping');
            Route::post('/save-shipping', 'saveShipping')->name('save_shipping.shipping');
            Route::post('/delete-shipping', 'deleteShipping')->name('delete_shipping.shipping');
        });

        Route::get('/logout', [Authenticate::class, 'logout'])->name('admin.logout');
    });

    Route::prefix('milk')->controller(App\Http\Controllers\Admin\SubscriptionController::class)->group(function () {
         Route::get('/subscription', 'view')->name('view.milk.subscription');
         Route::post('/save', 'save')->name('save.milk.subscription');
         Route::post('/delete', 'destroy')->name('delete.milk.subscription');
    });
});





