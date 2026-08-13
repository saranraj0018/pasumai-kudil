<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Hub;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        try {
            $user = User::find(auth('api')->id());
            $setting_user_id = \App\Models\Setting::where('data_key', 'milk_config_prefix')->first();
            $cacheKey = "inside_grocery_zone:user:{$user->id}";
            $shop_number = Hub::where('type', 1)->first();
            $isInside = Cache::get($cacheKey);
            if (!$user) {
                throw new \Exception('User not found', 404);
            }

            return response()->json([
                "status" => 200,
                "msg" => "success",
                "data" => [
                    [
                        "user_image"      => $user->image ? url('/storage/' . $user->image) : null,
                        "user_name"       => $user->name,
                        "mobile_number"   => $user->mobile_number,
                        "user_email"    => $user->email,
                    ]
                ],
                "inside_grocery_zone" =>  (bool) $isInside,
                'customer_id' => ($setting_user_id->data_value ?? '').$user->id ?? '',
                "shop_number" => $shop_number?->shop_contact_number ?? '',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => $th->getCode() ?: 500,
                "msg" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }

    /**
     * Edit Profile - POST
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "user_name"     => "required|string|max:255",
                "mobile_number" => "required|string|max:10",
                "user_email"    => "nullable|email|max:255",
                "user_image"    => "nullable",
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), 419);
            }

            $user = User::find(auth('api')->id());
            if (!$user) {
                throw new \Exception('User not found', 404);
            }

            $_image = $user->image;

            if ($request->user_image instanceof UploadedFile) {
                $_image = $request->user_image->storeAs(
                    'users',
                    now()->format('Y_m_d_His_') . $request->user_image->getClientOriginalName(),
                    'public'
                );
            } else if ($request->user_image) {
                $_image = $request->user_image;
            }

            $user->update([
                "name"   => $request->user_name,
                "mobile_number" => $request->mobile_number,
                "email"  => $request->user_email,
                "image"  => $_image,
            ]);

            return response()->json([
                "status" => 200,
                "msg"    => "profile update successfully",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => $th->getCode() ?: 500,
                "msg" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }

    public function index(Request $request)
    {
        $user = auth('api')->user();

        $cacheKey = "inside_grocery_zone:user:{$user->id}";
        $isInside = Cache::get($cacheKey);
        $cartQuantities = getCartQuantities();

        $likedProducts = json_decode($user->likedProducts ?? '[]', true);

        $productIds = collect($likedProducts)
            ->pluck('product_id')
            ->unique()
            ->toArray();

        $products = Product::with(['details', 'variants.unit'])
            ->whereIn('id', $productIds)
            ->where(function ($query) {
                $query->whereDate('expiry_date', '>=', now())
                    ->orWhereNull('expiry_date');
            })
            ->get();

        $data = $products->flatMap(function ($product) use ($likedProducts, $cartQuantities) {

            return $product->variants
                ->filter(function ($variant) use ($likedProducts, $product) {
                    return collect($likedProducts)->contains(function ($item) use ($product, $variant) {
                        return $item['product_id'] == $product->id
                            && $item['variant_id'] == $variant->id;
                    });
                })
                ->map(function ($variant) use ($product, $likedProducts, $cartQuantities) {

                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_image' => $product->image
                            ? url('/storage/' . $product->image)
                            : null,

                        'productorginalPrice' => $variant->regular_price,
                        'NegotiationPrice' => $variant->sale_price,

                        'liked_status' => true,

                        'product_kg' => $variant->weight . ' ' . optional($variant->unit)->short_name,

                        'stock_count' => $variant->stock,

                        'quantity' => intValue($cartQuantities[$variant->id] ?? 0),

                        'isFeaturedProduct' => $variant->is_featured_product ?? 0,

                        'variation_id' => $variant->id,
                    ];
                });

        })->values();

        return response()->json([
            'status' => 200,
            'message' => 'Wish List',
            'data' => $data,
            'inside_grocery_zone' => (bool) $isInside,
        ], 200);
    }

    public function toggleLikeStatus(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'product_id' => 'required|integer|exists:products,id',
                'variant_id' => 'required',
                'status'     => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 419,
                    'message' => $validator->errors()->first(),
                ], 419);
            }

            $user = auth('api')->user();

            $likedProducts = json_decode($user->likedProducts ?? '[]', true) ?: [];

            $exists = collect($likedProducts)->contains(function ($item) use ($request) {
                return $item['product_id'] == $request->product_id
                    && $item['variant_id'] == $request->variant_id;
            });

            if ($request->status) {

                if (!$exists) {
                    $likedProducts[] = [
                        'product_id' => $request->product_id,
                        'variant_id' => $request->variant_id,
                    ];
                }

                $message = "Wishlist Added Successfully";

            } else {

                $likedProducts = array_values(array_filter($likedProducts, function ($item) use ($request) {
                    return !(
                        $item['product_id'] == $request->product_id &&
                        $item['variant_id'] == $request->variant_id
                    );
                }));

                $message = "Wishlist Removed Successfully";
            }

            $user->update([
                'likedProducts' => json_encode($likedProducts)
            ]);

            return response()->json([
                'status' => 200,
                'msg' => $message,
            ]);

        } catch (\Throwable $th) {

            return response()->json([
                'status' => $th->getCode() ?: 500,
                'message' => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }


    public function checkLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }
    }
}
