<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller {
    public function featuredProducts(Request $request) {
        $cartQuantities = getCartQuantities();
        $featuredProducts = Product::with('details')
            ->orderBy('id', 'desc')
            ->where(function ($query) {
                $query->whereDate('expiry_date', '>=', now())
                    ->orWhereNull('expiry_date');
            })
            ->get()
            ->map(function ($product) use ($cartQuantities) {
                $details = $product->details;
                return [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->image ? url('/storage/' . $product->image) : null,
                    'offer_price'  => $details ? $details->sale_price : 0,
                    'normal_price' => $details ? $details->regular_price : 0,
                    'liked_status'=> in_array($product->id, (array)json_decode(auth('api')->user()->likedProducts) ?? []),
                    "stock_count"    => $details ? $details->stock : 0,
                    'product_kg'  => $details ? ($details->weight . ' ' . $details->weight_unit) : null,
                    'variation_id'  => $details ? $details->id : null,
                    'quantity'     =>  $details ? intValue($cartQuantities[$details->id] ?? 0) : 0,
                    'is_featured_product' => $details ? $details->is_featured_product : 0,
                ];
            })->filter(fn($product) => $product['is_featured_product'] == 1)
            ->values()
            ->toArray();


        return response()->json([
            'status'  => 200,
            'message' => 'Featured Products',
            'data'    => $featuredProducts,
        ]);
    }

    public function bestSeller(Request $request) {
        $cartQuantities = getCartQuantities();
        $bestSellerProducts = Product::with('details')
            ->orderBy('id', 'desc')
            ->where(function ($query) {
                $query->whereDate('expiry_date', '>=', now())
                    ->orWhereNull('expiry_date');
            })
            ->get()
            ->map(function ($product) use ($cartQuantities) {
                $details = $product->details;
                return [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_image' => url('/storage/' . $product->image),
                    'offer_price'  => $details ? $details->sale_price : 0,
                    'normal_price' => $details ? $details->regular_price : 0,
                    'liked_status'       => in_array($product->id, (array)json_decode(auth('api')->user()->likedProducts) ?? []),
                    "stock_count"    => $details ? $details->stock : 0,
                    'product_kg'  =>  $details ? ($details->weight . ' ' . $details->weight_unit) : null,
                    'variation_id'  => $details ? $details->id : null,
                    'quantity'     =>  $details ? intValue($cartQuantities[$details->id] ?? 0) : 0,
                    'is_featured_product' => $details ? $details->is_featured_product : 0,
                ];
            })->filter(fn($product) => $product['is_featured_product'] != 1)
            ->values()
            ->toArray();

        return response()->json([
            'status'  => 200,
            'message' => 'Best Seller Products',
            'data'    => $bestSellerProducts,
        ]);
    }

    public function searchGrocery(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                "product_name" => "required|string"
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), 419);
            }

            $query = Product::query()->with('details');
            $query->where('name', 'like', '%' . $request->product_name . '%');
            $query->where('status', 1);
            $products = $query->where(function ($query) {
                $query->whereDate('expiry_date', '>=', now())
                    ->orWhereNull('expiry_date');
            })->get();

            $user = auth('api')->user();
            $likedProducts = (array) json_decode($user?->likedProducts ?? '[]');
            $cartQuantities = getCartQuantities();

            return response()->json([
                'status' => 200,
                'mgs'    => $products->isEmpty() ? 'No products found' : 'Products fetched successfully',
                "data"   => $products->map(function ($product) use ($likedProducts, $cartQuantities) {
                    $details = $product->details;
                    return [
                        "product_id"           => $product->id,
                        "product_image"        => $product->image ? url('/storage/' . $product->image) : null,
                        "product_name"         => $product->name,
                       "offer_price"  => $product->details?->sale_price ?? 0,
                       "normal_price" => $product->details?->regular_price ?? 0,
                        "stock_count"    => $details ? $details->stock : 0,
                        "liked_status"         => in_array($product->id, $likedProducts),
                        'product_kg'  =>  $details ? ($details->weight . ' ' . $details->weight_unit) : null,
                        'variation_id'  => $details ? $details->id : null,
                        'quantity'     =>  $details ? intValue($cartQuantities[$details->id] ?? 0) : 0,
                    ];
                }),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getCode(),
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function productDetails(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|integer',
                'variation_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), 419);
            }

            $product = Product::with('variants')->where(function ($query) {
                $query->whereDate('expiry_date', '>=', now())
                    ->orWhereNull('expiry_date');
            })->find($request->product_id);

            if (!$product) {
                throw new \Exception('Product Not found', 404);
            }

            $variant = $product->variants->firstWhere('id', $request['variation_id']);

            if (empty($variant)) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Variant not found'
                ]);
            }

            $details = $product->details;
            $user = auth('api')->user();
            $likedStatus = false;
            if ($user) {
                $likedStatus = in_array(
                    $product->id,
                    (array) json_decode($user->likedProducts ?? '[]')
                );
            }
            $cartQuantities = getCartQuantities();
            $productSizes = $product->variants->isNotEmpty()
                ? $product->variants->map(fn($v) => [
                    'id' => $v->id,
                    'size' => $v->weight . ' ' . $v->weight_unit,
                    'stock' => $v->stock,
                ])->toArray()   : [];

            $currentVariant = $details;
            $currentVariant = $product->variants->firstWhere('id', $request->variation_id) ?? $product->variants->first();
            return response()->json([
                'status' => 200,
                'msg' => 'success',
                "data" => [
                    [
                        "product_id" => $product->id,
                        "product_image" => [
                            url('/storage/' . $product->image),
                            ...($product?->images?->map(fn($img) => url('/storage/' . $img->image_url)) ?? [])
                        ],
                        "product_name" => $product->name,
                        "stock_count"    => $currentVariant?->stock ?? 0,
                        "product_orginal_price" => $currentVariant?->regular_price ?? 0,
                        "product_offerprice" => $currentVariant?->sale_price ?? 0,
                        'product_gram'  =>  $currentVariant ? ($currentVariant->weight . ' ' . $currentVariant->weight_unit) : null,
                        "liked_status" => $likedStatus,
                        "product_size" => $productSizes,
                        "quantity" => $currentVariant ? intValue($cartQuantities[$currentVariant->id] ?? 0) : 0,
                        "image_text" => $product->description,
                        "cooking_idea" => $product->benefits
                    ]
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getCode() ?: 500,
                'msg' => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }
}
