<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\CartController;



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

     //Cart
     Route::post('/add-to-cart', [CartController::class, 'addToCart']);
     Route::post('/cart/remove', [CartController::class, 'removeFromCart']);
});



});


