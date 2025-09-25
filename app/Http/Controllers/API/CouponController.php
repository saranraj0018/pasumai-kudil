<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CouponController extends Controller
{
   public function index(Request $request)
    {
        try {
            $coupons = Coupon::where('status', 1)
                ->where('expires_at', '>=', now())
                ->get()
                ->map(function ($coupon) {
                    return [
                       "coupon_id" => $coupon->id,
                            "coupon_code" => $coupon->coupon_code,
                            "coupon_discount" => $coupon->discount_value,
                            "coupon_type" => $coupon->discount_type == 1 ? "fixed" : "percentage",
                            "coupon_value" => $coupon->discount_value,
                            "coupon_start_date" => now()->format('Y-m-d'),
                            "coupon_end_date" => \Carbon\Carbon::parse($coupon->expires_at)->format('Y-m-d'),
                            "coupon_status" => $coupon->status == 1 ? "active" : "disabled",
                            "description" => $coupon->description ?? "null",
                            "coupon_image" => $coupon->image ? url('/storage/' . $coupon->image) : '',
                    ];
                });

            return response()->json([
                'status'  => 200,
                'message' => 'Coupons fetched successfully',
                'data'    => $coupons,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

     /**
     * Delete a Coupon
     */
    public function deleteCoupon(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'coupon_id' => 'required|exists:coupons,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 419,
                    'message' => $validator->errors()->first(),
                ], 419);
            }

            Cache::forget('coupon_id_' . Auth::id());

            return response()->json([
                'status' => 200,
                'message' => 'Coupon Removed',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getCode(),
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
