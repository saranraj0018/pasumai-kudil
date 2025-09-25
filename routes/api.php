<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\AddressController;



Route::group(['prefix' => 'user'], function () {

    Route::post('/register', [AuthController::class, 'userRegister']);
    Route::post('/otp', [AuthController::class, 'VerifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);


   Route::middleware('verify.jwt')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    //home
     Route::get('/home', [HomeController::class,'index']);

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
     Route::post('/notification/read-status', [HomeController::class,'notificationReadStatus']);
     Route::post('/notification/delete', [HomeController::class,'notificationDelete']);

     //coupons
     Route::post('/coupons', [CouponController::class, 'index']);

     //Address
        Route::get('/address/list', [AddressController::class, 'index']);
        Route::post('/address/save', [AddressController::class, 'save']);
        Route::post('/address/update', [AddressController::class, 'update']);
        Route::post('/address/set-default', [AddressController::class, 'setDefaultAddress']);
        Route::post('/address/delete', [AddressController::class, 'delete']);


     //Cart
     Route::post('/add-to-cart', [CartController::class, 'addToCart']);
     Route::post('/cart', [CartController::class, 'getCart']);
     Route::post('/cart/remove', [CartController::class, 'removeFromCart']);
});



});


