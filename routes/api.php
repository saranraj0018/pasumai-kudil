<?php

use App\Http\Controllers\API\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\AddressController;
use App\Http\Controllers\API\Milk\ManageDeliveriesController;
use App\Http\Controllers\API\Milk\MilkAPIController;
use App\Http\Controllers\API\Milk\MilkHomeAPIController;
use App\Http\Controllers\API\Milk\MilkOrderAPIController;

Route::group(['prefix' => 'user'], function () {
    Route::post('/register', [AuthController::class, 'userRegister']);
    Route::post('/otp', [AuthController::class, 'VerifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);


    Route::middleware('verify.jwt')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        //home
        Route::get('/home', [HomeController::class, 'index']);
        Route::post('/check-location', [ProfileController::class, 'checkLocation']);

        //category
        Route::get('/category-list', [CategoryController::class, 'index']);
        Route::post('/category-products', [CategoryController::class, 'categoryProducts']);

        //products
        Route::post('/search-grocery', [ProductController::class, 'searchGrocery']);
        Route::get('/featured-products', [ProductController::class, 'featuredProducts']);
        Route::get('/best-seller-products', [ProductController::class, 'bestSeller']);
        Route::post('/product-details', [ProductController::class, 'productDetails']);

        //wishlist
        Route::get('/wishlist', [ProfileController::class, 'index']);
        Route::post('/wishlist/toggle', [ProfileController::class, 'toggleLikeStatus']);

        //profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::post('/edit-profile', [ProfileController::class, 'update']);

        //Notifications
        Route::get('/notification/list', [HomeController::class, 'notification']);
        Route::post('/notification/read-status', [HomeController::class, 'notificationReadStatus']);
        Route::post('/notification/delete', [HomeController::class, 'notificationDelete']);

       //coupons
       Route::post('/coupons', [CouponController::class, 'index']);
       Route::post('/coupon/delete', [CouponController::class, 'deleteCoupon']);

        //Address
        Route::get('/address/list', [AddressController::class, 'index']);
        Route::post('/address/save', [AddressController::class, 'save']);
        Route::post('/address/update', [AddressController::class, 'update']);
        Route::post('/address/set-default', [AddressController::class, 'setDefaultAddress']);
        Route::post('/address/delete', [AddressController::class, 'delete']);
        //orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/get-single-order', [OrderController::class, 'getSingleOrder']);
        //Cart
        Route::post('/add-to-cart', [CartController::class, 'addToCart']);
        Route::post('/cart', [CartController::class, 'getCart']);
        Route::post('/cart/remove', [CartController::class, 'removeFromCart']);
        Route::post('/create-order', [CartController::class, 'createOrder']);
        Route::post('/order/save', [CartController::class, 'saveOrder']);

        //milk
        Route::get('/milk-home-details', [MilkHomeAPIController::class, 'fetchHomeDetails']);
        Route::get('/get-wallet-details', [MilkAPIController::class, 'fetchWalletDetails']);
        Route::get('/get-subscription-details', [MilkAPIController::class, 'getSubscriptionDetails']);
        Route::post('/get-calender-details', [MilkAPIController::class, 'getCalendarDetails']);
        Route::post('/get-order-details', [MilkOrderAPIController::class, 'fetchOrderDetails']);
        Route::get('/get-manage-deliveries', [MilkOrderAPIController::class, 'getManageDeliveries']);
        Route::get('/get-user-plandetails', [MilkAPIController::class, 'getUserPlanDetails']);
        Route::post('/update-order', [MilkOrderAPIController::class, 'updateOrder']);
        Route::post('/cancel-subscription', [MilkHomeAPIController::class, 'cancelSubscription']);
        Route::post('/create-subscription', [MilkHomeAPIController::class, 'createSubscription']);
        Route::post('/subscription-plan', [MilkHomeAPIController::class, 'subscriptionPlan']);
//        Route::get('/manage-deliveries', [ManageDeliveriesController::class, 'manageDeliveries']);
        Route::post('/update-manage-deliveries', [ManageDeliveriesController::class, 'updateManageDeliveries']);
    });
});
