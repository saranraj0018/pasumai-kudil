<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Dashboard;
use App\Http\Controllers\Admin\Authenticate;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserlistController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DeliveryListController;
use App\Http\Controllers\Admin\DeliveryPartnerController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\HubController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\Settings\RolesAndPermissionsController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\TodayDeliveryController;
use App\Http\Controllers\NotificationController;

Route::prefix('admin')->group(function () {

    Route::middleware(['guest'])->as('admin.')->group(function () {
        Route::view('/login', 'auth.login')->name('login');
        Route::view('/register', 'auth.register')->name('register');

        Route::controller(Authenticate::class)->group(function () {
            Route::post('/authenticate', 'adminAuthenticate')->name('authenticate');
            Route::post('/register/update', 'registerUpdate')->name('register.update');
        });
    });

    Route::middleware('auth:admin')->group(function () {
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
            Route::post('/city/save', 'citySave')->name('update.hub.status');
            Route::delete('/delete', 'destroy')->name('delete.hub');
        });

        Route::prefix('map')->controller(HubController::class)->group(function () {
            Route::get('/view', 'showMap')->name('show.map');
            Route::get('/get-city-coordinates', 'getCityCoordinates')->name('area.Coordinates');
            Route::post('/save-area', 'store')->name('area.store');
        });

        //coupons
        Route::prefix('coupon')->controller(CouponController::class)->group(function () {
            Route::get('/list', 'view')->name('view.coupons');
            Route::post('/save', 'save')->name('save.coupon');
            Route::post('/delete', 'destroy')->name('delete.coupon');
        });

        // Products
        Route::prefix('products')->controller(ProductsController::class)->group(function () {
            Route::get('/lists', 'productLists')->name('lists.products');
            Route::post('/save_product', 'saveProduct')->name('save_product.products');
            Route::post('/delete_product', 'deleteProduct')->name('delete_product.products');
            Route::get('/search_product', 'searchProduct')->name('search_product.products');
            Route::get('/edit_product', 'editProduct')->name('edit_product.products');
        });

        //users
        Route::prefix('users')->controller(UserlistController::class)->group(function () {
            Route::get('/lists', 'userLists')->name('lists.users');
            Route::get('/user-profile-view', 'userProfileView')->name('user_view.users');
            Route::get('/transaction-history', 'transactionHistory')->name('transaction_history.users');
            Route::post('/add_wallet', 'addWallet')->name('add_wallet.users');
            Route::post('/save_user', 'saveUser')->name('save_user.users');
            Route::get('/get_subscription', 'getCustomSubscription')->name('get_subscription.users');
            Route::post('/add_user_account', 'addUserAccount')->name('add_user_account.users');
            Route::post('/subscription_cancel', 'cancelSubscription')->name('subscription_cancel.users');
            Route::post('/modify_subscription', 'modifySubscription')->name('modify_subscription.users');
            Route::post('/remove-previous-wallet', 'removePreviousWallet')->name('remove_previous_wallet.users');
            Route::post('/revoke', 'revokeSubscriptionDay')->name('revoke.users');
        });

        // Shipping
        Route::prefix('shipping')->controller(ShippingController::class)->group(function () {
            Route::get('/shipping', 'index')->name('lists.shipping');
            Route::post('/save-shipping', 'saveShipping')->name('save_shipping.shipping');
            Route::post('/delete-shipping', 'deleteShipping')->name('delete_shipping.shipping');
        });

        Route::prefix('delivery_partner')->controller(DeliveryPartnerController::class)->group(function () {
            Route::get('/delivery-partner', 'index')->name('lists.delivery_partner');
            Route::post('/save-delivery-partner', 'saveDeliveryPartner')->name('save_delivery_partner');
            Route::post('/delete-delivery-partner', 'deleteDeliveryPartner')->name('delete_delivery_partner');
        });

        Route::prefix('delivery_list')->controller(DeliveryListController::class)->group(function () {
            Route::get('/delivery-list', 'index')->name('lists.delivery_list');
            Route::post('/status-save', 'statusSave')->name('save.delivery_status_save');
            Route::post('/change-delivery-boy', 'changeDeliveryBoy')->name('change_delivery_boy.users');
        });

        Route::prefix('today_delivery')->controller(TodayDeliveryController::class)->group(function () {
            Route::get('/today-delivery-list', 'index')->name('lists.today_delivery_list');
            Route::post('/stock-maintain-save', 'stockSave')->name('stock_maintain_save');
        });

        Route::prefix('milk')->controller(App\Http\Controllers\Admin\SubscriptionController::class)->group(function () {
            Route::get('/subscription', 'view')->name('view.milk.subscription');
            Route::post('/save', 'save')->name('save.milk.subscription');
            Route::post('/delete', 'destroy')->name('delete.milk.subscription');
        });

        Route::get('/logout', [Authenticate::class, 'logout'])->name('admin.logout');
        Route::post('/user_logout', [Authenticate::class, 'user_logout'])->name('admin.user_logout');
        //ticket lists
        Route::get('/ticket-lists', [TicketController::class, 'index'])->name('ticket_lists');
        Route::post('/ticket-save', [TicketController::class, 'saveTicket'])->name('ticket_save');
        // web.php
        Route::get('/roles-and-permission', [RolesAndPermissionsController::class, 'roleAbilities'])->name('roles_and_permission');
        Route::post('roles-and-permission-save', [RolesAndPermissionsController::class, 'updateRoleAbilities'])->name('roles_and_permission_save');

        //create_role
        Route::get('roles-list', [RolesController::class, 'index'])->name('roles_list');
        Route::post('roles-save', [RolesController::class, 'roleSave'])->name('roles_save');

        //notification
        Route::get('/notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    });

});
