<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $orders = OrderDetail::with(['order', 'product'])
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('order_id')
            ->map(function ($items, $orderId) {
                $firstItem = $items->first();
                return [
                    'orderId'      => $firstItem->order->order_id,
                    'orderStatus' => $firstItem->order->status,
                    'orderAmount'  => $firstItem->order->gross_amount,
                    'orderDate'    => $firstItem->order->created_at,
                    'orderDetails' => $items
                ];
            })
            ->values();

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
                    'order_id'      => $order['orderId'],
                    'order_status' => $this->getOrderStatusText($order['orderStatus']),
                    'order_amount'  => number_format($order['orderAmount'], 1),
                    'order_date'    => Carbon::parse($order['orderDate'])->format('d M Y, h:i A'),
                    'orderDetails'  => $order['orderDetails']->map(function ($item) {
                        return [
                            'id'             => $item->id,
                            'order_id'       => $item->order_id ?? null,
                            'product_id'     => $item->product_id ?? null,
                            'category_id'    => $item->category_id ?? null,
                            'variant_id'     => $item->variant_id ?? null,
                            'product_name'   => $item->product_name ?? 'Unknown Product',
                            'quantity'       => $item->quantity ?? '',
                            'net_amount'     => $item->net_amount ?? 0,
                            'gst_type'       => $item->gst_type ?? 'N/A',
                            'gst_percentage' => $item->gst_percentage ?? 0,
                            'gst_amount'     => $item->gst_amount ?? 0,
                            'weight'         => $item->weight ?? 0,
                            'created_at'     => $item->created_at ?? null,
                            'updated_at'     => $item->updated_at ?? null,

                            'order' => $item->order ? [
                                'id'                  => $item->order->id ?? null,
                                'order_id'            => $item->order->order_id ?? null,
                                'user_id'             => $item->order->user_id ?? null,
                                'address_id'          => $item->order->address_id ?? null,
                                'phone'               => $item->order->phone ?? null,
                                'email'               => $item->order->email ?? null,
                                'status'              => $item->order->status ?? null,
                                'net_amount'          => $item->order->net_amount ?? null,
                                'shipping_amount'     => $item->order->shipping_amount ?? null,
                                'gross_amount'        => $item->order->gross_amount ?? null,
                                'gst_amount'          => $item->order->gst_amount ?? null,
                                'notes'               => $item->order->notes ?? null,
                                'rating_status'       => $item->order->rating_status ?? null,
                                'coupon_id'           => $item->order->coupon_id ?? null,
                                'coupon_amount'       => $item->order->coupon_amount ?? null,
                                'shipped_at'          => $item->order->shipped_at ?? null,
                                'delivered_at'        => $item->order->delivered_at ?? null,
                                'cancelled_at'        => $item->order->cancelled_at ?? null,
                                'refunded_at'         => $item->order->refunded_at ?? null,
                                'cancellation_reason' => $item->order->cancellation_reason ?? null,
                                'created_by'          => $item->order->created_by ?? null,
                                'updated_by'          => $item->order->updated_by ?? null,
                                'created_at'          => $item->order->created_at ?? null,
                                'updated_at'          => $item->order->updated_at ?? null,
                            ] : null,

                            'product' => $item->product ? [
                                'id'          => $item->product->id,
                                'name'        => $item->product->name ?? 'Unknown Product',
                                'image'       => ($item->product->image) ? url('/storage/' . ($item->product->image)) : null,
                                'status'      => $item->product->status ?? null,
                                'description' => $item->product->description ?? 'No description',
                                'benefits'    => $item->product->benefits ?? 'No benefits',
                                'created_at'  => $item->product->created_at ?? null,
                                'updated_at'  => $item->product->updated_at ?? null,
                                'expiry_date' => $item->product->expiry_date ?? null,
                            ] : null,
                        ];
                    })->values(),
                ];
            }),
        ]);
    }

    private function getOrderStatusText($status)
    {
        return match ((int) $status) {
            1 => 'Ordered',
            2 => 'On InProgress',
            3 => 'Order Shipped',
            4 => 'Order Delivered',
            5 => 'Order Cancelled',
            6 => 'Order Refunded',
            default => 'Unknown',
        };
    }

    public function getSingleOrder(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:order_details,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 419,
                'message' => $validator->errors()->first(),
            ], 409);
        }

        $orderDetail = OrderDetail::with(['order', 'product', 'order.userAddress', 'variants'])
            ->where('id', $request->order_id)
            ->first();

        if (!$orderDetail) {
            return response()->json([
                'status'  => 404,
                'message' => 'Order not found',
            ]);
        }

        $address = $orderDetail->order->userAddress;

        $elements =  [
                'product_id'      => $orderDetail->product_id,
                'product_name'    => $orderDetail->product->name ?? 'Unknown Product',
                'product_image' => ($orderDetail->product->image) ? url('/storage/' . ($orderDetail->product->image)): null,
                'originalPrice'   => number_format($orderDetail->variants->regular_price ?? 0, 2),
                'discountedPrice' => number_format($orderDetail->variants->sale_price ?? 0, 2),
                'description'     => $orderDetail->product->description ?? $orderDetail->description ?? 'No description',
                'quantity'        => $orderDetail->quantity ?? 0,
                'variation'       => $orderDetail->variants ? [
                    'id'     => $orderDetail->variants->id,
                    'weight' => $orderDetail->variants->weight . ' ' . $orderDetail->variants->weight_unit,
                    'price'  => $orderDetail->variants->sale_price ?? $orderDetail->variants->regular_price
                ] : null,
            ];

        $product_names = $orderDetail->product->name ?? 'Unknown Product';

        $fileName = 'invoice_' . $orderDetail->order->order_id . '.pdf';
        $url = asset("storage/invoices/{$fileName}");

        return response()->json([
            'status' => 200,
            'data'   => [
                'orderId'        => $orderDetail->order->order_id,
                'orderDate'      => Carbon::parse($orderDetail->order->created_at)->format('Y-m-d'),
                'order_status'   => $this->getOrderStatusText($orderDetail->order->status),
                'orderAmount'    => number_format($orderDetail->order->gross_amount, 1),
                'orderItems'     => $elements,
                'product_names'  => $product_names,
                'rating_status'  => $orderDetail->order->rating_status === 1,

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
                'paymentMethod'  => $orderDetail->order->payment_method ?? 'cod',
                'paymentStatus'  => $orderDetail->order->payment_status ?? 'pending',

                'orderTracking'  => [
                    [
                        'status'  => 'Order Placed',
                        'date'    => Carbon::parse($orderDetail->order->created_at)->format('Y-m-d H:i:s'),
                        'isDone'  => true
                    ],
                    [
                        'status'  => 'Order Shipped',
                        'date'    => $orderDetail->order->shipped_at ? Carbon::parse($orderDetail->order->shipped_at)->format('Y-m-d H:i:s') : null,
                        'isDone'  => !empty($orderDetail->order->shipped_at)
                    ],
                    [
                        'status'  => 'Order Delivered',
                        'date'    => $orderDetail->order->delivered_at ? Carbon::parse($orderDetail->order->delivered_at)->format('Y-m-d H:i:s') : null,
                        'isDone'  => !empty($orderDetail->order->delivered_at)
                    ],
                    [
                        'status'  => 'Order Cancelled',
                        'date'    => $orderDetail->order->cancelled_at ? Carbon::parse($orderDetail->order->cancelled_at)->format('Y-m-d H:i:s') : null,
                        'isDone'  => !empty($orderDetail->order->cancelled_at)
                    ],
                    [
                        'status'  => 'Order Refunded',
                        'date'    => $orderDetail->order->refunded_at ? Carbon::parse($orderDetail->order->refunded_at)->format('Y-m-d H:i:s') : null,
                        'isDone'  => !empty($orderDetail->order->refunded_at)
                    ],
                ],
            ]
        ]);
    }
}
