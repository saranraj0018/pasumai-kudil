<?php

use Illuminate\Support\Facades\Cache;
use Razorpay\Api\Api;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

if (!function_exists('getCartQuantities')) {
    function getCartQuantities()
    {
        $userId  = auth('api')->id() ?? session()->getId();
        $cartKey = "cart_{$userId}";
        $cart    = Cache::get($cartKey, []);

        return collect($cart)->mapWithKeys(fn($item) => [
            $item['variant_id'] => $item['quantity']
        ]);
    }
}

    if (!function_exists('intValue')) {
        function intValue($value)
        {
            return intval($value);
        }
    }


if (!function_exists('razorPay')) {
    function razorPay()
    {
        return new class() {
            protected $api;

            public function __construct()
            {
                $this->api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
            }

            public function createOrder($amount, $currency = 'INR')
            {
                try {
                    $order = $this->api->order->create([
                        'amount' => $amount * 100,
                        'currency' => $currency,
                        'receipt' => 'order_' . uniqid(),
                        'payment_capture' => 1,
                    ]);

                    return $order;
                } catch (Exception $e) {
                    return ['error' => $e->getMessage()];
                }
            }
        };
    }
}


