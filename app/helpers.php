<?php

use Illuminate\Support\Facades\Cache;

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


