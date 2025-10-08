<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderController extends Controller
{
  public function index()
{
    $userId = Auth::id();

    $orders = Order::with(['orderDetails', 'payment'])
        ->where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->select('id', 'order_id as orderId', 'status as orderStatus', 'net_amount as orderAmount', 'created_at as orderDate')
        ->get();

    if ($orders->isEmpty()) {
        return response()->json([
            'status' => 404,
            'message' => "No orders found for user ID {$userId}"
        ]);
    }

    return response()->json([
        'status' => 200,
        'data' => $orders->map(function ($order) {
            return [
                'order_id'      => $order->orderId,
                'order_status' => $this->getOrderStatusText($order->orderStatus),
                'order_amount'  => number_format($order->orderAmount, 1),
                'order_date'    => Carbon::parse($order->orderDate)->format('d M Y, h:i A'),
                'product_name'  => $order->orderDetails->pluck('product_name')->implode(', ') ?: 'No Product',
            ];
        }),
    ]);
}

private function getOrderStatusText($status)
    {
        return match ((int) $status) {
            1 => 'Ordered',
            2 => 'On Hold',
            3 => 'Shipped',
            4 => 'Delivered',
            5 => 'Cancelled',
            default => 'Unknown',
        };
    }

    public function getSingleOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string|exists:orders,order_id',
        ]);

        $order = Order::with(['orderDetails.product', 'orderDetails.variants', 'userAddress', 'payment'])
            ->where('order_id', $request->order_id)
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => 404,
                'message' => 'Order not found',
            ]);
        }

        $address = $order->userAddress;

        $elements = $order->orderDetails->map(function ($item) {
            return [
                'product_id'      => $item->product_id,
                'product_name'    => $item->quantity . ' x ' . ($item->product->name ?? $item->product_name),
                'product_image' => ($item->product->image ?? $item->image) ? url('/storage/' . ($item->product->image ?? $item->image)) : null,
                'originalPrice'   => number_format($item->variants->regular_price ?? 0, 2),
                'discountedPrice' => number_format($item->variants->sale_price ?? 0, 2),
                'description'     => $item->product->description ?? $item->description ?? 'No description',
                'quantity'        => $item->quantity ?? 0,
                'variation'       => $item->variants ? [
                    'id'     => $item->variants->id,
                    'weight' => $item->variants->weight . ' ' . $item->variants->weight_unit,
                    'price'  => $item->variants->sale_price ?? $item->variants->regular_price
                ] : null,
            ];
        });

        $product_names = $elements->pluck('product_name')->implode(', ');

        $fileName = 'invoice_' . $order->order_id . '.pdf';
        $url = asset("storage/invoices/{$fileName}");

        return response()->json([
            'status' => 200,
            'data'   => [
                'orderId'        => $order->order_id,
                'orderDate'      => Carbon::parse($order->created_at)->format('Y-m-d'),
                'order_status'   => $order->status,
                'orderAmount'    => number_format($order->net_amount, 1),
                'orderItems'     => $elements,
                'product_names'  => $product_names,
                'rating_status'  => $order->rating_status === 1,

                'deliveryAddress' => $address ? [
                    'id'          => $address->id,
                    'addressType' => $address->address_type ?? 'manual',
                    'address'     => $address->address ?? 'N/A',
                    'city'        => $address->city ?? '',
                    'state'       => $address->state ?? '',
                    'pincode'     => $address->pincode ?? '',
                    'landmark'    => $address->landmark ?? '',
                    'name'        => $address->name ?? '',
                    'phone'       => $address->phone_number ?? '',
                    'default'     => false,
                ] : null,

                'invoice_url'    => $url,
                'paymentMethod'  => $order->payment_method ?? 'cod',
                'paymentStatus'  => $order->payment_status ?? 'pending',

                'orderTracking'  => [
                    [
                        'status'  => 'Order Placed',
                        'date'    => Carbon::parse($order->created_at)->format('Y-m-d H:i:s'),
                        'isDone'  => true
                    ],
                    [
                        'status'  => 'Order Shipped',
                        'date'    => $order->shipped_at ? Carbon::parse($order->shipped_at)->format('Y-m-d H:i:s') : null,
                        'isDone'  => !empty($order->shipped_at)
                    ],
                    [
                        'status'  => 'Order Delivered',
                        'date'    => $order->delivered_at ? Carbon::parse($order->delivered_at)->format('Y-m-d H:i:s') : null,
                        'isDone'  => !empty($order->delivered_at)
                    ]
                ],
            ]
        ]);
    }

}
