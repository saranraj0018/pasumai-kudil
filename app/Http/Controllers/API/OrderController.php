<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index() {
        $orders = Order::with('orderDetails')->whereHas('payment')->where('user_id', Auth::id())->select(
            'id',
            'order_id as orderId',
            'status as orderStatus',
            'gross_amount as orderAmount',
            'created_at as orderDate'
        )->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 200,
            'data' => $orders->map(function ($order) {
                return [
                    'order_id'      => $order->orderId,
                    'order_status'  => $order->orderStatus == 1 ? 'Ordered' : ($order->orderStatus == 2 ? 'On-Hold' : ($order->orderStatus == 3 ? 'Order Shipped' : ($order->orderStatus == 4 ? 'Order Delivery' : 'Cancelled'))),
                    'order_amount'  => number_format($order->orderAmount, 1),
                    'order_date'    => $order->orderDate,
                    'product_name'  => $order->orderDetails->pluck('product_name')->implode(', ') ?: 'No Product'
                ];
            })
        ]);
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
                'image'           => $item->product->image ?? $item->image,
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
                'order_status'   => $order->status == 1 ? 'Ordered' : ($order->status == 2 ? 'On-Hold' :
                    ($order->status == 3 ? 'Order Shipped' : ($order->status == 4 ? 'Order Delivery' : 'Cancelled'))),
                'orderAmount'    => number_format($order->gross_amount, 1),
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
