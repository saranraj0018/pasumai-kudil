<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller {
    public function index(Request $request) {
        $bannerList = Banner::where('type', 'main')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($banner) {
                return [
                    'banner_id' => $banner->id,
                    'banner_image' => url('/storage/' . $banner->image_url),
                ];
            });

        $subBannerList = Banner::where('type', 'sub')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($banner) {
                return [
                    'bannerId' => $banner->id,
                    'bannerImage' => url('/storage/' . $banner->image_url),
                ];
            });

        $categories = Category::orderBy('id')
            ->get()
            ->map(function ($category) {
                return [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'category_image' => url('/storage/' . $category->image),
                ];
            });

     $user = auth('api')->user();
    $likedProducts = (array) json_decode($user->likedProducts ?? '[]');
    $cartQuantities = getCartQuantities();

        $featuredProducts = Product::with('details')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($product) use ($likedProducts, $cartQuantities) {
                $details = $product->details;
                return [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_image' => url('/storage/' . $product->image),
                    'offer_price'  => $details ? $details->sale_price : 0,
                    'normal_price' => $details ? $details->regular_price : 0,
                    "stock_count"    => $details ? $details->stock : 0,
                    'liked_status'       => in_array($product->id, $likedProducts),
                    'product_kg'  => $details ? ($details->weight . ' ' . $details->weight_unit) : null,
                    'variation_id'  => $details ? $details->id : null,
                    'quantity'     =>  $details ? ($cartQuantities[$details->id] ?? 0) : 0,
                    'is_featured_product' => $details ? $details->is_featured_product : 0,
                ];
            })->filter(fn($product) => $product['is_featured_product'] == 1)
            ->values()
            ->toArray();

        $bestSellerProducts = Product::with('details')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($product) use ($likedProducts, $cartQuantities) {
                 $details = $product->details;
                return [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_image' => url('/storage/' . $product->image),
                    'offer_price'  => $details ? $details->sale_price : 0,
                    'normal_price' => $details ? $details->regular_price : 0,
                    'liked_status'       => in_array($product->id, $likedProducts),
                   "stock_count"    => $details ? $details->stock : 0,
                    'product_kg'  =>  $details ? ($details->weight . ' ' . $details->weight_unit) : null,
                    'variation_id'  => $details ? $details->id : null,
                    'quantity'     =>  $details ? ($cartQuantities[$details->id] ?? 0) : 0,
                    'is_featured_product' => $details ? $details->is_featured_product : 0,
                ];
            })->filter(fn($product) => $product['is_featured_product'] != 1)
            ->values()
            ->toArray();

        return response()->json([
            "status" => 200,
            "msg" => "success",
            "data" => [
                "Bannerlist"           => $bannerList,
                "category_data"        => $categories,
                "featured_product_data" => $featuredProducts,
                "SubBannerList"        => $subBannerList,
                "best_seller_data"     => $bestSellerProducts,
            ]
        ]);
    }
}
