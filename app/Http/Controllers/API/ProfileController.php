<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;

class ProfileController extends Controller
{
     public function show(Request $request)
    {
        try {
            $user = User::find(auth('api')->id());

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
                ]
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

        return response()->json([
            'status' => 200,
            'message' => 'Wish List',
            "data" => Product::whereIn('id', (array)json_decode($user->likedProducts) ?: [])
                ->get()->map(function ($product) use ($user) {
                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_image' => $product->image ? url('/storage/' . $product->image) : null,
                        'productorginalPrice' => $product->details->regular_price,
                        'NegotiationPrice' => $product->details->sale_price,
                        'liked_status' => in_array($product->id, (array) json_decode($user->likedProducts) ?? []),
                        'product_kg' => $product?->details?->weight ? ($product->details->weight . ' ' . $product->details->weight_unit) : null,
                        'stock_count' => $product->details->stock,
                        'variation_id' => $product->details?->id ?? null,
                    ];
                })
        ], 200);
    }

    public function toggleLikeStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|integer',
                'status' => 'required'
            ]);

           if ($validator->fails()) {
          return response()->json([
          'status' => 419,
          'message' => $validator->errors()->first(),
          ], 419);
            }

            $product = Product::find($request->product_id);
           if (!$product) {
              throw new ModelNotFoundException('Product Not Found', 404);
          }

        $user = User::find(auth("api")->user()->id);
        if (!$user) {
            throw new \Exception('User not authenticated', 401);
        }

         $likedProducts = (array) json_decode($user->likedProducts ?? '[]');

         $like = $request->status;

            if (!empty($like) && !in_array($request->product_id, $likedProducts)) {
                $likedProducts[] = $request->product_id;
                $message = "Wishlist Added Successfully";
            } else {
            $likedProducts = array_filter($likedProducts, fn($id) => $id != $product->id);
            $likedProducts = array_values($likedProducts);
             $message = "Wishlist Removed Successfully";
        }

        $user->update(['likedProducts' => json_encode($likedProducts)]);

            return response()->json([
                'status' => 200,
                'msg' => $message,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getCode() ?: 500,
                'message' => $th->getMessage()
            ], $th->getCode() ?: 500);
        }
    }
}
