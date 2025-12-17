<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller {
    public function index(Request $request) {
        $bannerList = Banner::where('type', 'GroceryMain')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($banner) {
                return [
                    'banner_id' => $banner->id,
                    'banner_image' => request()->isSecure()
                        ? secure_url('storage/' . $banner->image_url)
                        : url('storage/' . $banner->image_url),
                ];
            });

        $subBannerList = Banner::where('type', 'GrocerySub')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($banner) {
                return [
                    'bannerId' => $banner->id,
                    'bannerImage' => url('/storage/' . $banner->image_url),
                ];
            });

        $categories = Category::orderBy('id')
            ->where('status', 1)
            ->take(4)
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
                    'quantity'     =>  $details ? intValue($cartQuantities[$details->id] ?? 0) : 0,
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
                    'quantity'     =>  $details ? intValue($cartQuantities[$details->id] ?? 0) : 0,
                    'is_featured_product' => $details ? $details->is_featured_product : 0,
                ];
            })->filter(fn($product) => $product['is_featured_product'] != 1)
            ->values()
            ->toArray();
        $cacheKey = "inside_grocery_zone:user:{$user->id}";
        $isInside = Cache::get($cacheKey);
        return response()->json([
            "status" => 200,
            "msg" => "success",
            "data" => [
                "Bannerlist"           => $bannerList,
                "category_data"        => $categories,
                "featured_product_data" => $featuredProducts,
                "SubBannerList"        => $subBannerList,
                "best_seller_data"     => $bestSellerProducts,
            ],
            "inside_grocery_zone" =>  (bool) $isInside
        ]);
    }


    public function notification()
{
    if (!Auth::check()) {
        return response()->json([
            'success' => false,
            'message' => 'You need to login first'
        ]);
    }

    $notification = Notification::where('user_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($list) {
            return [
                'notification_id' => $list->id,
                 'notification_image' => url('/storage/logo.jpg'),
                'notification_title' => $list->title,
                'notification_description' => $list->description,
                'created_date' => $list->created_at,
                'read_status' => $list->status === 1,
            ];
        });

    return response()->json([
        'status' => 200,
        'message' => 'Notifications List',
        'data' => $notification->values()
    ]);
}

public function notificationReadStatus(Request $request)
{
    if (!Auth::check()) {
        return response()->json([
            'success' => false,
            'message' => 'You need to login first'
        ]);
    }

    // Validation
    $validator = Validator::make($request->all(), [
        'notification_id' => 'nullable|array|min:1',
        'user_id' => 'nullable|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 409,
            'message' => $validator->errors()->first(),
        ], 409);
    }

    if ($request->filled('notification_id')) {
        Notification::where('user_id', Auth::id())
            ->whereIn('id', $request->notification_id)
            ->update(['status' => 1]);

        return response()->json([
            'status' => 200,
            'message' => 'Selected Notifications Marked as Read',
        ]);
    }

    Notification::where('user_id', Auth::id())->update(['status' => 1]);

    return response()->json([
        'status' => 200,
        'message' => 'All Notifications Marked as Read',
    ]);
}


 public function notificationDelete(Request $request)
 {
    if (!Auth::check()) {
        return response()->json([
            'success' => false,
            'message' => 'You need to login first'
        ]);
    }

    $validator = Validator::make($request->all(), [
        'notification_id' => 'required|array|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 409,
            'message' => $validator->errors()->first(),
        ], 409);
    }

    Notification::where('user_id', Auth::id())
        ->whereIn('id', $request->notification_id)
        ->delete();

    return response()->json([
        'status' => 200,
        'message' => 'Notification Deleted Successfully',
    ]);
}

}
