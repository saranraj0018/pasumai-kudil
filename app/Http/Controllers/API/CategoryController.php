<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller {
    public function index() {
        $user = auth('api')->user();
        $cacheKey = "inside_grocery_zone:user:{$user->id}";
        $isInside = Cache::get($cacheKey);
        $categories = Category::select('id', 'name', 'image')
            ->where('status', 1)
            ->get()
            ->map(function ($category){
                return [
                    'category_id'    => $category->id,
                    'category_name'  => strtoupper($category->name),
                    'category_image' => $category->image
                        ? url('/storage/' . $category->image)
                        : null,
                ];
            });

        return response()->json([
            'status' => 200,
            'msg'    => 'Categories fetched successfully',
            'data'   => $categories,
            'inside_grocery_zone' => (bool) $isInside

        ]);
    }

    public function categoryProducts(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 419,
                    'message' => $validator->errors()->first(),
                ], 409);
            }

          $categoryId = $request->category_id;

            $cartQuantities = getCartQuantities();
           $likedProducts = (array) json_decode($user?->likedProducts ?? '[]');

            $products = \App\Models\Product::whereHas('details', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->with(['details','variants.unit'])
            ->where(function ($query) {
                $query->whereDate('expiry_date', '>=', now())
                    ->orWhereNull('expiry_date');
             })
            ->get();
            $data = $products
                ->flatMap(function ($product) use ($likedProducts, $cartQuantities) {

                    return $product->variants
                        ->where('is_featured_product', '!=', 1)
                        ->map(function ($variant) use ($product, $likedProducts, $cartQuantities) {
                    return [
                        "product_id"      => $product->id,
                        'category_id'    =>  $product->details->category_id ?? null,
                        "product_image" => $product->image ? url('/storage/' . $product->image) : null,
                        "offer_price"         => $variant->sale_price ?? 0,
                        "normal_price"        => $variant->regular_price ?? 0,
                        "product_kg"          => $variant->weight . ' ' . optional($variant->unit)->short_name,
                         'liked_status'         => in_array($product->id, $likedProducts),
                        "variation_id"        => $variant->id,
                        "quantity"            => intValue($cartQuantities[$variant->id] ?? 0),
                        "stock_count"         => $variant->stock ?? 0,
                         "product_name"    => $product->name,
                    ];
                        });
                })
                ->values();
            return response()->json([
                'status' => 200,
                'msg'    => $data->isEmpty()
                    ? 'No products found'
                    : 'Products fetched successfully',
                'data'   => $data,
            ], 200);
      } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getCode(),
                'message' => $th->getMessage(),
            ], 500);
      }
    }
}
