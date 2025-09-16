<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
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
                      // "coupon_id"        => $coupon->id,
                        "coupon_code"      => $coupon->coupon_code,
                        "discount_value"   => $coupon->discount_value,
                        "discount_type"    => $coupon->discount_type,
                         "description"     => $coupon->description,
                        "apply_for"       => $coupon->apply_for,
                        "max_price"       => $coupon->max_price ?? 0,
                        "min_price"       => $coupon->min_price ?? 0,
                        "order_count"     => $coupon->order_count ?? 0,
                       "expires_at"      => $coupon->expires_at
                                               ? Carbon::parse($coupon->expires_at)->format('Y-m-d')
                                               : null,
                       // "couponStartDate" => now()->format('Y-m-d'),
                        // "couponEndDate" => \Carbon\Carbon::parse($coupon->expires_at)->format('Y-m-d'),
                      //  "coupon_status"    => $coupon->status ? "active" : "disabled",
                       // "coupon_image"     => $coupon->image ? asset('storage/' . $coupon->image) : null,
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
}
