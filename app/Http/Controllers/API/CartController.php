<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'variant_id' => 'required|integer',
            'quantity' => 'required|integer', // Ensuring only positive integers
        ]);

        $userId = auth()->id() ?? session()->getId(); // Use user ID or session ID
        $cartKey = "cart_{$userId}"; // Unique cart cache key

        $cart = Cache::get($cartKey, []);

        // Check if the product with the same variant exists
        $index = collect($cart)->search(fn($item) =>
            $item['product_id'] == $request->product_id &&
            $item['variant_id'] == $request->variant_id
        );

        if ($index !== false) {
            // Ensure the new quantity does not go negative
            $newQuantity = $cart[$index]['quantity'] + $request->quantity;

            if ($newQuantity <= 0) {
                unset($cart[$index]); // Remove item if quantity is zero or negative
            } else {
                $cart[$index]['quantity'] = $newQuantity;
            }
        } else {
            if ($request->quantity > 0) {
                $cart[] = [
                    'product_id' => $request->product_id,
                    'variant_id' => $request->variant_id,
                    'quantity' => $request->quantity,
                ];
            }
        }

        Cache::put($cartKey, array_values($cart), now()->addDays(7)); // Store in cache for 7 days

        return response()->json([
            'message' => $request->quantity > 0 ? 'Product added to cart successfully!' : 'Product remove successfully!',
            'status' => 200
        ]);
    }

public function removeFromCart(Request $request)
{
    $request->validate([
        'product_id' => 'required|integer',
        'variant_id' => 'required|integer',
    ]);

    $userId = auth('api')->id() ?? session()->getId();
    $cartKey = "cart_{$userId}";

    $cart = Cache::get($cartKey, []);

    $cart = array_values(array_filter($cart, function ($item) use ($request) {
        return !(
            $item['product_id'] == $request->product_id &&
            $item['variant_id'] == $request->variant_id
        );
    }));

    Cache::put($cartKey, $cart, now()->addDays(7));

    return response()->json([
        'status'  => 200,
        'message' => 'Product removed from cart successfully!',
        'cart'    => $cart
    ]);
   }


    public function getCart(Request $request)
    {
        $userId = auth()->id() ?? session()->getId();
        $cartKey = "cart_{$userId}";

        //  Cache::forget($cartKey);
        $cart_list = Cache::get($cartKey, []);


        if (empty($cart_list)) {
            Cache::forget('coupon_id_' . Auth::id());
        }


        $productData = [];
        $totalItems = $coupon_discount = $subtotal = $total_weight = $total_tax = 0 ;

        foreach ($cart_list as $item) {

            $total_tax = 0;
            $product = Product::with('details')->find($item['product_id']);

            if (!empty($product) && !empty($product->details?->id) ) {
                $variant = $product->details;

                $originalPrice = $variant['regular_price'] ?? 0;
                $discountedPrice = $variant['sale_price'] ?? $originalPrice;
                $subtotal += $discountedPrice * $item['quantity'];
                $totalItems += $item['quantity'];
                $total_weight += $variant['weight'] * $item['quantity'] ?? 0;

                $total_tax += !empty($product->details->tax_type == 2) && !empty($product->details->tax_percentage) ? $subtotal * $product->details->tax_percentage/100 : 0;

                // Weight Calculation Logic
                $weightValue = isset($variant['weight']) ? floatval($variant['weight']) : 0;
                $weightUnit = isset($variant['weight_unit']) ? strtolower($variant['weight_unit']) : '';


                if ($weightUnit === "g" && $weightValue >= 1000) {
                    $weight = ($weightValue / 1000) . "kg"; // Convert grams to kg
                }  elseif ($weightUnit === "ml" && $weightValue >= 1000) {
                    $weight = ($weightValue / 1000) . "L"; // Convert ml to liters
                } else {
                    $weight = $weightValue . ' ' . $weightUnit; // Keep other units unchanged
                }

                $productData[] = [
                    "id" => $product->id,
                    "name" => $product->name,
                    "image" => !empty($product->image) ? url('/storage/'.$product->image) : '',
                    "originalPrice" => !empty($variant['regular_price']) ? $variant['regular_price'] * $item['quantity'] : '',
                    "discountedPrice" => !empty($variant['sale_price']) ? $variant['sale_price'] * $item['quantity'] : $variant['regular_price'] * $item['quantity'],
                    "variation" => $variant ? [
                        "id" => $variant['id'] ?? 0,
                        "value" => $variant['weight'] ?? 0,
                        "unit" => $variant['weight_unit'] ?? '0',
                        "price" => $variant['sale_price'] * $item['quantity'],
                        "weight" => $weight,
                    ] : null,
                    "quantity" => intValue($item['quantity']),
                ];
            }
        }
        $coupon_id = !empty($request['coupon_id']) ? $request['coupon_id'] : Cache::get('coupon_id_' . Auth::id(), null);
        $coupon = null;
        if (!empty($coupon_id)) {
            Cache::put('coupon_id_' . Auth::id(), $coupon_id, now()->addMinutes(60));
            $coupon = Coupon::where('status',1)->where(function ($query) {
                $query->whereNull('expires_at') // Include if expires_at is NULL
                ->orWhereDate('expires_at', '>', now()); // Include if expires_at is in the future
            })->find($coupon_id);
            $coupon_discount = self::getCouponDetails($coupon,$subtotal);
        }
        // Determine if delivery is free (e.g., free for orders above 500)
        $finalDeliveryCharge = 0;


        $totalAmount = $subtotal - $coupon_discount + $finalDeliveryCharge + $total_tax;
        $billSummary = [
            "totalItems" => $totalItems,
            "itemsAmount" => $subtotal ,
            "discount" => $coupon_discount,
            "deliveryCharge" => (int)$finalDeliveryCharge,
            "SGST" => !empty($total_tax) ? number_format($total_tax/2 ,2) : '0.0',
            "IGST" => !empty($total_tax) ? number_format($total_tax/2 ,2) : '0.0',
            "platformFee" => 0,
            "totalAmount" => number_format($totalAmount, 1),
        ];
        $address = Address::where("created_by",auth()->id())->where('is_default',1)->get();
        return response()->json([
            'status' => 200,
            "productData" => $productData,
            "billSummary" => $billSummary,
            "couponDetail" => $coupon,
            "addressDetails" => $address,
        ]);
    }


//    public static function calculateShipping(int $amount, float $weight) {
//
//        if(self::where('config_name', 'free_delivery')->first()->config_value == '1' &&
//            intval(self::where('config_name', 'free_delivery_amount')->first()->config_value) <= $amount) {
//            return 0;
//        } else {
//            if ($weight < 1 ){
//                return  self::where('config_name', 'minimum_delivery_amount')
//                    ->first()
//                    ->config_value ?? 0;
//            }
//            return intval(self::where('config_name', '=', 'delivery_fees')->first()->config_value) * $weight;
//        }
//    }

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
