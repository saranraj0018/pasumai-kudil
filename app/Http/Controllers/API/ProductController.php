<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller {
    public function featuredProducts(Request $request) {
        $user = auth('api')->user();
        $likedProducts = json_decode($user->likedProducts ?? '[]', true);
        $cartQuantities = getCartQuantities();
        $featuredProducts = Product::with(['details','variants.unit'])
            ->orderBy('id', 'desc')
            ->where(function ($query) {
                $query->whereDate('expiry_date', '>=', now())
                    ->orWhereNull('expiry_date');
            })
            ->orderByDesc('id')
            ->get()
            ->flatMap(function ($product) use ($cartQuantities,$likedProducts) {
                return $product->variants
                    ->where('is_featured_product', 1)
                    ->map(function ($detail) use ($product,$likedProducts, $cartQuantities) {
                return [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->image ? url('/storage/' . $product->image) : null,
                    'offer_price'  => $detail ? $detail->sale_price : 0,
                    'normal_price' => $detail ? $detail->regular_price : 0,
                    'liked_status' => collect($likedProducts)->contains(function ($item) use ($product, $detail) {
                        return $item['product_id'] == $product->id
                            && $item['variant_id'] == $detail->id;
                    }),
                    "stock_count"    => $detail ? $detail->stock : 0,
                    'product_kg'   => $detail->weight . ' ' . optional($detail->unit)->short_name,
                    'variation_id'  => $detail ? $detail->id : null,
                    'quantity'     =>  $detail ? intValue($cartQuantities[$detail->id] ?? 0) : 0,
                    'is_featured_product' => $detail ? $detail->is_featured_product : 0,
                ];
            });
            })
            ->values()
            ->shuffle()
            ->toArray();


        return response()->json([
            'status'  => 200,
            'message' => 'Featured Products',
            'data'    => $featuredProducts,
        ]);
    }

    public function bestSeller(Request $request) {
        $user = auth('api')->user();
        $likedProducts = json_decode($user->likedProducts ?? '[]', true);
        $cartQuantities = getCartQuantities();
        $bestSellerProducts = Product::with(['details','variants.unit'])
            ->orderBy('id', 'desc')
            ->where(function ($query) {
                $query->whereDate('expiry_date', '>=', now())
                    ->orWhereNull('expiry_date');
            })
            ->get()
            ->flatMap(function ($product) use ($cartQuantities,$likedProducts) {
                return $product->variants
                    ->where('is_featured_product','!=', 1)
                    ->map(function ($detail) use ($product, $likedProducts,$cartQuantities) {
                return [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_image' => url('/storage/' . $product->image),
                    'offer_price'  => $detail ? $detail->sale_price : 0,
                    'normal_price' => $detail ? $detail->regular_price : 0,
                    'liked_status' => collect($likedProducts)->contains(function ($item) use ($product, $detail) {
                        return $item['product_id'] == $product->id
                            && $item['variant_id'] == $detail->id;
                    }),
                    "stock_count"    => $detail ? $detail->stock : 0,
                    'product_kg'   => $detail->weight . ' ' . optional($detail->unit)->short_name,
                    'variation_id'  => $detail ? $detail->id : null,
                    'quantity'     =>  $detail ? intValue($cartQuantities[$detail->id] ?? 0) : 0,
                    'is_featured_product' => $detail ? $detail->is_featured_product : 0,
                ];
                    });
            })
            ->values()
                    ->shuffle()
            ->toArray();

        return response()->json([
            'status'  => 200,
            'message' => 'Best Seller Products',
            'data'    => $bestSellerProducts,
        ]);
    }

    public function searchGrocery(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "product_name" => "required|string"
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), 419);
            }

            $products = Product::with(['details', 'variants.unit'])
                ->where('status', 1)
                ->where('name', 'like', '%' . $request->product_name . '%')
                ->where(function ($query) {
                    $query->whereDate('expiry_date', '>=', now())
                        ->orWhereNull('expiry_date');
                })
                ->get();

            $user = auth('api')->user();
            $likedProducts = json_decode($user->likedProducts ?? '[]', true);
            $cartQuantities = getCartQuantities();

            $data = $products
                ->flatMap(function ($product) use ($likedProducts, $cartQuantities) {

                    return $product->variants
                        ->map(function ($variant) use ($product, $likedProducts, $cartQuantities) {

                            return [
                                "product_id"          => $product->id,
                                "product_image"       => $product->image ? url('/storage/' . $product->image) : null,
                                "product_name"        => $product->name,
                                "offer_price"         => $variant->sale_price ?? 0,
                                "normal_price"        => $variant->regular_price ?? 0,
                                "stock_count"         => $variant->stock ?? 0,
                                'liked_status' => collect($likedProducts)->contains(function ($item) use ($product, $variant) {
                                    return $item['product_id'] == $product->id
                                        && $item['variant_id'] == $variant->id;
                                }),
                                "product_kg"          => $variant->weight . ' ' . optional($variant->unit)->short_name,
                                "variation_id"        => $variant->id,
                                "quantity"            => intValue($cartQuantities[$variant->id] ?? 0),
                                "is_featured_product" => $variant->is_featured_product,
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
                'status'  => 500,
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

            $product = Product::with('variants.unit')->where(function ($query) {
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
                    'size' => $v->weight . ' ' . $v->unit?->short_name,
                    'stock' => $v->stock,
                ])->toArray()   : [];
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
                        'product_gram'  =>  $currentVariant ? ($currentVariant->weight . ' ' . $currentVariant->unit?->short_name) : null,
                        "liked_status" => $likedStatus,
                        "product_size" => $productSizes,
                        "quantity" => $currentVariant ? intValue($cartQuantities[$currentVariant->id] ?? 0) : 0,
                        "image_text" => $product->description,
                        "cooking_idea" => $product->cooking_idea,
                        "benefits" => $product->benefits,

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
