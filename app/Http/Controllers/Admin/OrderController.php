<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;


class OrderController extends Controller
{
    public function view(Request $request)
    {
        $orders = Order::with('user', 'userAddress', 'orderDetails')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('admin.orders.view', compact('orders'));
    }

    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required',
                'status' => 'required|integer',
                'date' => 'required|date',
            ]);

            $order = Order::where('order_id', $request->order_id)->first();

            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }

            $status = (int) $request->status;
            $date = $request->date;


            $order->shipped_at = null;
            $order->delivered_at = null;
            $order->cancelled_at = null;
            $order->refunded_at = null;

            switch ($status) {
                case 3:
                    $order->shipped_at = $date;
                    break;
                case 4:
                    $order->delivered_at = $date;
                    break;
                case 5:
                    $order->cancelled_at = $date;
                    break;
                case 6:
                    $order->refunded_at = $date;
                    break;
            }

            $order->status = $status;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
