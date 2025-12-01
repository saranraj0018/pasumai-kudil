<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Setting;
use App\Models\Shipping;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
            $product = Product::with('details')->find($item['product_id']);

            if (!empty($product) && !empty($product->details?->id) ) {
                $variant = $product->details;

                $originalPrice = $variant['regular_price'] ?? 0;
                $discountedPrice = $variant['sale_price'] ?? $originalPrice;
                $subtotal += $discountedPrice * $item['quantity'];
                $totalItems += $item['quantity'];
                $total_weight += $variant['weight'] * $item['quantity'] ?? 0;

                $total_tax += !empty($product->details->tax_type == 2) && !empty($product->details->tax_percentage) ? $subtotal * $product->details->tax_percentage / 100 : 0;
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

        if (empty($coupon_id)) {
            $setting = Setting::where('data_key', 'temp_coupon_' . Auth::id())
                ->where('expires_at', '>=', now())
                ->first();
            $coupon_id = $setting->data_value ?? 0;
        }

        $coupon = null;
        $coupon_status = true;
        if (!empty($coupon_id)) {
            $key = 'coupon_id_' . Auth::id();
            $coupon = Coupon::where('status',1)->where(function ($query) {
                $query->whereNull('expires_at') // Include if expires_at is NULL
                ->orWhereDate('expires_at', '>', now()); // Include if expires_at is in the future
            })->find($coupon_id);
           $user_coupon = Setting::where('data_key', 'temp_coupon_' . Auth::id())->where('expires_at', '>=', now())
               ->where('data_value', $coupon->id)->first();
            if (!empty($coupon) && Cache::has($key) ) {
                Cache::get($key);
            } elseif (!empty($coupon->apply_for) && $coupon->apply_for == 2 && empty($user_coupon)) {
                $check_apply_coupon = self::getCouponDetails($coupon,$subtotal);
                if (empty($check_apply_coupon)) {
                    $coupon_status = false;
                } else {
                    $cache_coupon = new Setting();
                    $cache_coupon->data_key = 'temp_coupon_' . Auth::id();
                    $cache_coupon->data_value = $coupon_id;
                    $cache_coupon->expires_at = now()->addMinutes(5);
                    $cache_coupon->save();
                }

            } elseif (!empty($coupon->apply_for) && $coupon->apply_for == 1){
                Cache::put($key, $coupon_id, now()->addMinutes(5));
            }

            $coupon_discount = self::getCouponDetails($coupon,$subtotal);

        }
        $address = Address::where('created_by', Auth::id())->where('is_default',1)->first();

        $address_id = $address->id ?? 0;
        $finalDeliveryCharge = 0;
        if (!empty($address_id)) {
            $finalDeliveryCharge = self::calculateShipping($address_id);
        }


        $totalAmount = ($subtotal + $finalDeliveryCharge + $total_tax) - $coupon_discount; ;
        $billSummary = [
            "totalItems" => $totalItems,
            "itemsAmount" => number_format($subtotal,1) ,
            "discount" => number_format($coupon_discount,1),
            "deliveryCharge" => number_format($finalDeliveryCharge,1),
            "SGST" => !empty($total_tax) ? number_format($total_tax/2 ,2) : '0.0',
            "IGST" => !empty($total_tax) ? number_format($total_tax/2 ,2) : '0.0',
            "totalAmount" => number_format($totalAmount, 1),
        ];
        $address = Address::where("created_by",auth()->id())->where('is_default',1)->first();
        return response()->json([
            'status' => 200,
            "productData" => $productData,
            "billSummary" => $billSummary,
            "couponDetail" =>  $coupon,
            "addressDetails" => $address,
            "coupon_status" => $coupon_status,
        ]);
    }

    public static function getCouponDetails($coupon, $total_amount) {

        if (empty($coupon)) {
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


        if ($coupon->apply_for == 2 && $coupon->order_type == 2) {
            if (!empty($min_price) && $total_amount < $min_price || !empty($max_price) && $total_amount > $max_price) {
                return $discount;
            }

           $total = Order::where('status',4)->count();
            if ($coupon->order_count == $total + 1) {
                return ($coupon->discount_type == 1)
                    ? ($total_amount * $discount_value / 100)
                    : (int)$discount_value;
            }

        }

        if ($coupon->apply_for == 2 && in_array($coupon->discount_type, [1, 2]) && $coupon->order_type == 1) {
            $today = now()->format('Y-m-d');
            $used_coupons = Order::where('coupon_id',$coupon->id)->where('status',4)->whereDate('created_at', $today)->count() ?? 0;

            $user_used_today = Order::where('coupon_id', $coupon->id)
                ->where('user_id', Auth::id())
                ->whereDate('created_at', $today)
                ->where('status', 4)
                ->exists();

            if ($user_used_today) {
                return $discount;
            }

            if (!empty($min_price) && $total_amount < $min_price || !empty($max_price) && $total_amount > $max_price) {
                return $discount;
            }


            $applied_count = Setting::where('data_value',$coupon->id)
                ->where('expires_at', '>=', now())
                ->count(); # 1 -> b

            $user_count = Setting::where('data_value',$coupon->id)
                ->where('expires_at', '>=', now())
                ->where('data_key', 'temp_coupon_' . Auth::id())
                ->first(); # 1 -> b

            $order = $used_coupons + $applied_count;
            if (!empty($coupon->order_count) && $coupon->order_count > $order) {
                return ($coupon->discount_type == 1)
                    ? ($total_amount * $discount_value / 100)
                    : (int)$discount_value;
            } elseif ($order >= $coupon->order_count && !empty($user_count)) {
                return ($coupon->discount_type == 1)
                    ? ($total_amount * $discount_value / 100)
                    : (int)$discount_value;
            }
        }

        return $discount;
    }

    public function createOrder(Request $request)
    {
            $validator = Validator::make($request->all(), [
                "orderAmount" => 'required|numeric',
                "paymentMode" => 'required|string',
                "addressId" => 'required|string',
            ]);

            #check if successful
            if ($validator->fails())
                return response()->json([
                    'status' => 500,
                    'message' => $validator->errors()->first(),
                ], 500);
            $razorPayOrder = razorPay()->createOrder($request['orderAmount']);
            Cache::put('order_id_'.Auth::id(), $razorPayOrder->id, now()->addMinutes(30));

            Cache::put('address_id_'.Auth::id(), $request['addressId'],now()->addMinutes(30));
            return response()->json([
                'status' => 200,
                'message' => 'Order created successfully!',
                'orderId' => $razorPayOrder->id,
            ], 200);

    }

    public function saveOrder(Request $request) {

        try {
        $validator = Validator::make($request->all(), [
            "transactionId" => "required",
            "orderId" => "required"
        ]);

        if ($validator->fails())
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        DB::beginTransaction();
        if($request['orderId'] != Cache::get('order_id_'.Auth::id()))
            throw new \Exception('Order Not found', 404);

            $address_id = Address::where('created_by', Auth::id())->where('is_default',1)->first()->id;
            if ($address_id != Cache::get('address_id_'.Auth::id()))
                throw new \Exception('Address Not found', 404);

            $coupon_amount = 0;

            if (!empty($request['coupon_id']) && !empty($request['total_amount'])) {

            $coupon = Coupon::find($request['coupon_id']);
            $coupon_amount = self::getCouponDetails($coupon, $request['total_amount']);
        }
            $shipping = 0;
            if (!empty($address_id)) {
                $shipping = self::calculateShipping($address_id);
            }
        $orderDetails = collect(Cache::get("cart_".auth()->id(), []))->map(function($item) use($coupon_amount, $shipping) {

            $product  = Product::with('details')->find($item['product_id']);


            $variant = $product->details;
            if ($variant->id != intval($item['variant_id'])) {
                return response()->json([
                    'status' => 409,
                    'message' => 'Variant not found for this product',
                ]);
            }

            $variant = ProductDetail::find($item['variant_id']);
            if ($variant) {
                $variant->stock = max(0, $variant->stock - intval($item['quantity']));
                $variant->save();
            }

            return [
                ...$item,
                'category_id' => $product->details->category_id,
                'product_name' => $product->name,
                'product_id' => intval($item["product_id"]),
                'net_amount' =>  !empty($variant->sale_price) ? intval($variant->sale_price) * intval($item['quantity']) :
                    intval($variant->regular_price) * intval($item['quantity']),
                'gst_type' => $product->details->tax_type,
                'gst_percentage' => $product->details->tax_percentage,
                'gst_amount' =>  !empty($product->details->sale_price) && $product->details->tax_type == 2 && !empty($product->details->tax_percentage)
                    ? (($product->details->sale_price * $product->details->tax_percentage) / 100)  *  (intval($item['quantity']))
                    : (!empty($product->details->regular_price) && $product->details->tax_type == 2 && !empty($product->details->tax_percentage)
                        ? (($product->details->regular_price * $product->details->tax_percentage) / 100) *  (intval($item['quantity']))
                        : 0),
                'weight' => $variant->value * $item['quantity'],
            ];
        });


        $order = \App\Models\Order::create([
            'order_id' => $request['orderId'],
            'user_id' => auth()->id(),
            'address_id' => Cache::get('address_id_'.Auth::id()),
            'phone' => auth()->user()->mobile_number,
            'email' => auth()->user()->email,
            'status' => 1,
            'net_amount' => $orderDetails->sum('net_amount'),
            'gst_amount' => $orderDetails->sum('gst_amount'),
            'gross_amount' => ($orderDetails->sum('net_amount') + $shipping) - $coupon_amount,
            'shipping_amount' => $shipping,
            'notes' => 'Creating new Order',
            'coupon_id' => !empty($coupon) ? $coupon?->id : null,
            'coupon_amount' => $coupon_amount,
            'created_by' => auth()->id()
        ]);

        //$variant = collect(json_decode($product->details->varients,true))->firstWhere('id', $item['variant_id']);
        $orderDetails->map(function($detail) use($order) {

            $order->orderDetails()->create($detail);
        });


        DB::commit();

        $order->payment()->create([
            "razorpay_payment_id" => $request->transactionId,
            "amount" => $order->gross_amount,
            "currency" => "INR",
            "method" => "UPI",
            "email" => auth()->user()->email,
            "phone" => auth()->user()->mobile_number
        ]);

            $userId = auth()->id();
            Cache::forget("cart_{$userId}");
            Cache::forget("coupon_id_{$userId}");
            Cache::forget('address_id_'.Auth::id());
            Cache::forget('order_id_'.Auth::id());

            return response()->json([
                'status' => 200,
                'message' => 'Translation Completed!'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => $th->getCode(),
                'message' => $th->getMessage(),
            ],  500);
        }

    }

    public static function calculateShipping(int $address_id)
    {
        if (empty($address_id)) {
            return 0;
        }

        $address_details = Address::find($address_id);
        if (empty($address_details)) {
            return 0;
        }

        // Get vendor details (assuming one vendor for now)
        $vendor = Shipping::where('status',1)->first();
        if (empty($vendor)) {
            return 0;
        }

        $vendor_lat = $vendor->latitude;
        $vendor_long = $vendor->longitude;
        $address_lat = $address_details->latitude;
        $address_long = $address_details->longitude;
        $free_shipping = $vendor->free_shipping ?? 0;
        $extra_charge = $vendor->extra_km ?? 0;

        $distance = self::getDistanceFromGoogleMaps($vendor_lat, $vendor_long, $address_lat, $address_long);

        if ($distance > 0 && $distance <= $free_shipping) {
            $shipping_cost = 0; // free within 3 km
        } else {
            $extra_distance = $distance - $free_shipping;
            $shipping_cost = $extra_distance * $extra_charge; // ₹50 per km after 3 km
        }

        return round($shipping_cost, 2);
    }

    private static function getDistanceFromGoogleMaps($lat1, $lon1, $lat2, $lon2)
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json";
        $params = [
            'origins' => "$lat1,$lon1",
            'destinations' => "$lat2,$lon2",
            'key' => $apiKey,
            'units' => 'metric',
        ];

        $response = Http::get($url, $params);

        if ($response->successful()) {
            $data = $response->json();

            if (
                isset($data['rows'][0]['elements'][0]['distance']['value'])
            ) {
                // Convert meters to kilometers
                return $data['rows'][0]['elements'][0]['distance']['value'] / 1000;
            }
        }

        return 0; // fallback if API fails
    }

}
