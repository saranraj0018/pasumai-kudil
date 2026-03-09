<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DeliveryPartner;
use App\Models\Hub;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Dashboard extends Controller
{
    public function index()
    {
        $this->data['grocerry_location'] = Hub::where([
            'type' =>  1,
            'status' => 1
        ])->first();
        $this->data['milk_hubs'] =  Hub::where([
            'type' => 2,
            'status' => 1
        ])->get();
        return view('admin.dashboard')->with($this->data);
    }

    public function dashboardData(Request $request)
    {
        $month = $request->month;
        $productQuery = Product::query();
        $orderQuery = Order::whereHas('payment', function ($q) {
            $q->where('status', 'PAID');
        });
        if ($month) {
            $productQuery->whereMonth('created_at', $month);
            $orderQuery->whereMonth('created_at', $month);
        }
        $totalProducts = $productQuery->count();
        $totalOrders   = $orderQuery->count();
        $totalUsers    = User::count();
        $activeUsers = UserSubscription::when($month, function ($q) use ($month) {
            $q->whereMonth('created_at', $month);
        })
        ->where('status',1)
        ->distinct('user_id')->count('user_id');

        $categories = Category::withCount([
            'orderDetails as total_sales' => function ($query) use ($month) {
                $query->whereHas('order', function ($order) use ($month) {
                    $order->where('status', 4)
                        ->whereHas('payment', function ($payment) {
                            $payment->where('status', 'PAID');
                        });
                    if ($month) {
                        $order->whereMonth('created_at', $month);
                    }
                });
            }
        ])->get();

        $categoryLabels = $categories->pluck('name');
        $categoryData   = $categories->pluck('total_sales');
        $ticketStatusMap =
            [
                1 => 'Open',
                2 => 'Closed',
                3 => 'Rejected'
            ];

        $ticketStatus = Ticket::when($month, function ($q) use ($month) {
            $q->whereMonth('created_at', $month);
        })
            ->select('status')
            ->get()
            ->groupBy('status')
            ->map(function ($items, $status) use ($ticketStatusMap) {
                return [
                    'label' => $ticketStatusMap[$status] ?? 'Unknown',
                    'count' => $items->count()
                ];
            })->values();
        $statusMap = [
            1 => 'Ordered',
            2 => 'On Hold',
            3 => 'Shipped',
            4 => 'Delivered',
            5 => 'Cancelled',
            6 => 'Refunded'
        ];
        $orderStatus = Order::when($month, function ($q) use ($month) {
            $q->whereMonth('created_at', $month);
        })
            ->select('status')
            ->get()
            ->groupBy('status')
            ->map(function ($items, $status) use ($statusMap) {
                return [
                    'label' => $statusMap[$status] ?? 'Unknown',
                    'count' => $items->count()
                ];
            })->values();
        $orderLabels  = $orderStatus->pluck('label');
        $orderData    = $orderStatus->pluck('count');
        $ticketLabels = $ticketStatus->pluck('label');
        $ticketData   = $ticketStatus->pluck('count');
        $deliveredAmount = Order::where('status', 4)
            ->whereHas('payment', function ($q) {
                $q->where('status', 'PAID');
            })
            ->when($month, function ($q) use ($month) {
                $q->whereMonth('created_at', $month);
            })
            ->sum('gross_amount');
        $monthlyRevenue = Order::where('status', 4)
            ->when($month, function ($q) use ($month) {
                $q->whereMonth('created_at', $month);
            })
            ->whereHas('payment', function ($q) {
                $q->where('status', 'PAID');
            })
            ->selectRaw('MONTH(created_at) as month, SUM(gross_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');
        $products = ProductDetail::when($month, function ($q) use ($month) {
            $q->whereMonth('created_at', $month);
        })->get();
        $totalStock = 0;
        foreach ($products as $product) {
            if (!empty($product->variants['options'])) {
                foreach ($product->variants['options'] as $option) {
                    $totalStock += $option['stock'] ?? 0;
                }
            } else {
                $totalStock += $product->stock ?? 0;
            }
        }
        $soldProducts = OrderDetail::with('order.payment')
            ->whereHas('order.payment', function ($q) {
                $q->where('status', 'PAID');
            })
            ->whereHas('order', function ($q) {
                $q->where('status', 4);
            })
            ->when($month, function ($q) use ($month) {
                $q->whereMonth('created_at', $month);
            })->sum('quantity');

        $percentage = $totalStock > 0
            ? round(($soldProducts / $totalStock) * 100)
            : 0;

        $subscriptionPlan = Subscription::when($month, function ($q) use ($month) {
            $q->whereMonth('created_at', $month);
        })->get();
        $plans = UserSubscription::with('get_subscription')
            ->select('subscription_id', DB::raw('COUNT(user_id) as users_count'))
            ->when($month, function ($q) use ($month) {
                $q->whereMonth('created_at', $month);
            })
            ->where('status', '1')
            ->groupBy('subscription_id')
            ->get();

        $subscriptionPlans = $plans->map(function ($plan) {
            return [
                'name' => $plan->get_subscription->plan_name ?? '',
                'amount' => $plan->get_subscription->plan_amount ?? 0,
                'duration' => $plan->get_subscription->plan_pack ?? '',
                'users' => $plan->users_count
            ];
        });
        $delivery_partner = DeliveryPartner::when($month, function ($q) use ($month) {
            $q->whereMonth('created_at', $month);
        })->count();
        return response()->json([
            'total_products' => $totalProducts,
            'total_orders'   => $totalOrders,
            'total_users'    => $totalUsers,
            'active_users'   => $activeUsers,
            'category_labels' => $categoryLabels,
            'category_data'   => $categoryData,
            'order_labels' => $orderLabels,
            'order_data'   => $orderData,
            'ticket_labels' => $ticketLabels,
            'ticket_data'   => $ticketData,
            'ordered_amount' => $deliveredAmount,
            'monthlyRevenue' => $monthlyRevenue,
            'totalStock' => $totalStock,
            'soldProducts' => $soldProducts,
            'percentage' =>  $percentage,
            'subscriptionPlan' => $subscriptionPlans,
            'delivery_partner' => $delivery_partner
        ]);
    }
}
