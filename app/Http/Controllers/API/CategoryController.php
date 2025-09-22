<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;


class CategoryController extends Controller {
    public function index() {
        $categories = Category::select('id', 'name', 'image')
            ->where('status', 1)
            ->get()
            ->map(function ($category) {
                return [
                    'category_id'    => $category->id,
                    'category_name'  => $category->name,
                    'category_image' => $category->image
                        ? url('/storage/' . $category->image)
                        : null,
                ];
            });

        return response()->json([
            'status' => 200,
            'msg'    => 'Categories fetched successfully',
            'data'   => $categories,
        ]);
    }

    public function categoryProducts(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator->errors()->first(), 419);
            }

          $categoryId = $request->category_id;

            $cartQuantities = getCartQuantities();
           $likedProducts = (array) json_decode($user?->likedProducts ?? '[]');

            $products = \App\Models\Product::whereHas('details', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->with('details')
            ->get()
                ->map(function ($product) use ($cartQuantities, $likedProducts) {
                     $details = $product->details;
                    return [
                        "product_id"      => $product->id,
                        'category_id'    =>  $product->details->category_id ?? null,
                        "product_image" => $product->image ? url('/storage/' . $product->image) : null,
                        "offer_price"     => $details ? $details->sale_price : 0,
                        "normal_price"    => $details ? $details->regular_price : 0,
                         'product_kg'  => $details ? ($details->weight . ' ' . $details->weight_unit) : null,
                         'liked_status'         => in_array($product->id, $likedProducts),
                         'variation_id'  => $details ? $details->id : null,
                         'quantity'     =>  $details ? intValue($cartQuantities[$details->id] ?? 0) : 0,
                         "stock_count"    => $details ? $details->stock : 0,
                         "product_name"    => $product->name,];
                });

            return response()->json([
                'status' => 200,
                'msg'    => 'Category products fetched successfully',
                'data'   => $products
            ]);
      } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getCode(),
                'message' => $th->getMessage(),
            ], 500);
      }
    }
}
