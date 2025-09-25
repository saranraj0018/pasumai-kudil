<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
   public function index(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'total_amount' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 409,
                'message' => $validator->errors()->first(),
            ]);
        }
        try {
            $coupons = Coupon::where('status', 1)
                ->whereNull('expires_at')
                ->orWhereDate('expires_at', '>', now())
                ->get()
                ->map(function ($coupon) use ($request) {
                    $coupon_amount = self::getCouponDetails($coupon, $request['total_amount']) ?? 0;
                    return [
                       "coupon_id" => $coupon->id,
                            "coupon_code" => $coupon->coupon_code,
                            "coupon_discount" => $coupon->discount_value,
                            "coupon_type" => $coupon->discount_type == 1 ? "fixed" : "percentage",
                            "coupon_value" => $coupon->discount_value,
                            "coupon_start_date" => now()->format('Y-m-d'),
                            "coupon_end_date" => \Carbon\Carbon::parse($coupon->expires_at)->format('Y-m-d'),
                            "description" => $coupon->description ?? "null",
                            "coupon_image" => $coupon->image ? url('/storage/' . $coupon->image) : '',
                            "coupon_status" =>  $coupon_amount > 1,
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


    public static function getCouponDetails($coupon, $total_amount) {
        if (!$coupon) {
            return 0;
        }

        $discount = 0;
        $discount_value = $coupon->discount_value;

        if ($coupon->apply_for == 1 && in_array($coupon->discount_type, [1, 2])) {
            $min_price = $coupon->min_price;
            $max_price = $coupon->max_price;
            if ((!empty($min_price) && !empty($max_price) &&   $total_amount >= $min_price) && ($total_amount <= $max_price)) {

                return ($coupon->discount_type == 1)
                    ? ($total_amount * $discount_value / 100)
                    : (int)$discount_value;
            } elseif ((!empty($min_price) && empty($max_price) && $total_amount >= $min_price) || (!empty($max_price) && empty($min_price) && $total_amount <= $max_price)) {
                return ($coupon->discount_type == 1)
                    ? ($total_amount * $discount_value / 100)
                    : (int)$discount_value;
            }
        }

        if ($coupon->apply_for == 2 && in_array($coupon->discount_type, [1, 2])) {
            $order = Order::where('user_id', auth()->id())->where('status',4)->count();

            if (!empty($coupon->order_count) && $order + 1 == $coupon->order_count) {
                return ($coupon->discount_type == 1)
                    ? ($total_amount * $discount_value / 100)
                    : (int)$discount_value;
            }
        }

        return $discount;
    }
}
