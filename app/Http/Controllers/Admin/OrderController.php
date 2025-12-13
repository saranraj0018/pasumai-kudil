<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Events\NewNotification;
use App\Services\FirebaseService;
use App\Http\Controllers\Controller;
use App\Models\Notification;

class OrderController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

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
            // Validate request
            $request->validate([
                'order_id' => 'required',
                'status' => 'required|integer',
                'date' => 'required|date',
                'id' => 'required',
            ]);

            $order = Order::with('user')->find($request->id);
            $user = $order->user; // Use relationship

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $status = (int) $request->status;
            $date = $request->date;

            // Reset all timestamps
            $order->shipped_at = null;
            $order->delivered_at = null;
            $order->cancelled_at = null;
            $order->refunded_at = null;

            // Handle statuses and send notifications
            switch ($status) {
                case 3:
                    $order->shipped_at = $date;
                    $this->sendOrderNotification($user, $order, 'shipped', 'Order Shipped');
                    break;

                case 4:
                    $order->delivered_at = $date;
                    $this->sendOrderNotification($user, $order, 'delivered', 'Order Delivered');
                    break;

                case 5:
                    $order->cancelled_at = $date;
                    $this->sendOrderNotification($user, $order, 'cancelled', 'Order Cancelled');
                    break;

                case 6:
                    $order->refunded_at = $date;
                    $this->sendOrderNotification($user, $order, 'refunded', 'Order Refunded');
                    break;
            }

            // Update order status
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

    /**
     * Helper function to send FCM notification
     */
    protected function sendOrderNotification($user, $order, $statusName, $title)
    {

        if ($user->fcm_token) {

            $notification = new Notification();
            $notification->user_id = $user->id;
            $notification->title = $title;
            $notification->description = "Your order #{$order->order_id} status is now {$statusName}";
            $notification->type = 1;
            $notification->role = 2;
            $notification->save();

            $this->firebase->sendNotification(
                $user->fcm_token,
                $title,
                "Your order #{$order->order_id} status is now {$statusName}",
                [
                    'order_id' => $order->order_id,
                    'status' => $statusName,
                    'type' => 1
                ]
            );
        }
    }
}
