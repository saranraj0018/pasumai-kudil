<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
   public function addToCart(Request $request)
{
    $request->validate([
        'product_id' => 'required|integer',
        'variant_id' => 'required|integer',
        'quantity'   => 'required|integer|min:1',
    ]);

    $userId  = auth('api')->id() ?? session()->getId();
    $cartKey = "cart_{$userId}";

    $cart = Cache::get($cartKey, []);

    $index = collect($cart)->search(fn($item) =>
        $item['product_id'] == $request->product_id &&
        $item['variant_id'] == $request->variant_id
    );

    if ($index !== false) {
        $cart[$index]['quantity'] += $request->quantity;

        if ($cart[$index]['quantity'] <= 0) {
            unset($cart[$index]);
            $cart = array_values($cart);
        }
    } else {
        if ($request->quantity > 0) {
            $cart[] = [
                'product_id' => $request->product_id,
                'variant_id' => $request->variant_id,
                'quantity'   => $request->quantity,
            ];
        }
    }

    Cache::put($cartKey, $cart, now()->addDays(7));

    return response()->json([
        'message' => 'Cart updated successfully!',
        'cart'    => $cart,
        'status'  => 200
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

}
