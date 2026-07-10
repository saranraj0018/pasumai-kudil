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
use Carbon\Carbon;
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

        // Earliest year with any activity across the tables this dashboard filters.
        $earliestDataYear = collect([
            Order::min('created_at'),
            Product::min('created_at'),
            User::min('created_at'),
            UserSubscription::min('created_at'),
            Ticket::min('created_at'),
            DeliveryPartner::min('created_at'),
        ])->filter()->map(fn($date) => Carbon::parse($date)->year)->min();

        // Always show at least the last 5 years in the dropdown, even if no
        // data exists yet that far back — and reach further back automatically
        // if real data is older than that.
        $currentYear = now()->year;
        $fallbackStartYear = $currentYear - 105;
        $startYear = $earliestDataYear
            ? min($earliestDataYear, $fallbackStartYear)
            : $fallbackStartYear;

        $this->data['available_years'] = collect(range($startYear, $currentYear))->values();

        return view('admin.dashboard')->with($this->data);
    }

    public function dashboardData(Request $request)
    {
        $month = $request->month;
        $year  = $request->year;

        $applyPeriod = function ($query, $column = 'created_at') use ($month, $year) {
            if ($month) {
                $query->whereMonth($column, $month);
            }
            if ($year) {
                $query->whereYear($column, $year);
            }
            return $query;
        };

        $productQuery = Product::query();
        $orderQuery = Order::whereHas('payment', function ($q) {
            $q->where('status', 'PAID');
        });
        $applyPeriod($productQuery);
        $applyPeriod($orderQuery);

        $totalProducts = $productQuery->count();
        $totalOrders   = $orderQuery->count();
        $totalUsers    = $applyPeriod(User::query())->count();

        $milkHubs = $applyPeriod(
            Hub::where(['type' => 2, 'status' => 1])
        )->get(['id', 'name', 'address']);

        $activeUsers = UserSubscription::when(true, function ($q) use ($applyPeriod) {
            $applyPeriod($q);
        })
            ->where('status', 1)
            ->distinct('user_id')->count('user_id');

        $categories = Category::withCount([
            'orderDetails as total_sales' => function ($query) use ($month, $year) {
                $query->whereHas('order', function ($order) use ($month, $year) {
                    $order->where('status', 4)
                        ->whereHas('payment', function ($payment) {
                            $payment->where('status', 'PAID');
                        });
                    if ($month) {
                        $order->whereMonth('created_at', $month);
                    }
                    if ($year) {
                        $order->whereYear('created_at', $year);
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

        $ticketStatus = Ticket::when(true, function ($q) use ($applyPeriod) {
            $applyPeriod($q);
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

        $orderStatus = Order::when(true, function ($q) use ($applyPeriod) {
            $applyPeriod($q);
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
            ->when(true, function ($q) use ($applyPeriod) {
                $applyPeriod($q);
            })
            ->sum('gross_amount');

        // Monthly revenue chart: if a specific year is picked, scope to that year
        // so the 12-month line reflects that year only. If no year is picked,
        // the month filter (if any) still narrows it, matching prior behaviour.
        $monthlyRevenue = Order::where('status', 4)
            ->when($month, function ($q) use ($month) {
                $q->whereMonth('created_at', $month);
            })
            ->when($year, function ($q) use ($year) {
                $q->whereYear('created_at', $year);
            })
            ->whereHas('payment', function ($q) {
                $q->where('status', 'PAID');
            })
            ->selectRaw('MONTH(created_at) as month, SUM(gross_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $deliveredOrderIds = Order::where('status', 4)
            ->whereHas('payment', function ($q) {
                $q->where('status', 'PAID');
            })
            ->when(true, function ($q) use ($applyPeriod) {
                $applyPeriod($q);
            })
            ->pluck('id');

        $profitData = OrderDetail::whereIn('order_details.order_id', $deliveredOrderIds)
            ->join('product_details', function ($join) {
                $join->on(DB::raw('CAST(order_details.variant_id AS UNSIGNED)'), '=', 'product_details.id');
            })
            ->selectRaw('
                SUM(product_details.sale_price * order_details.quantity) as total_revenue,
                SUM(product_details.purchase_price * order_details.quantity) as total_cost,
                SUM((product_details.sale_price - product_details.purchase_price) * order_details.quantity) as total_profit
            ')
            ->first();

        $revenue     = round($profitData->total_revenue ?? 0, 2);
        $cost        = round($profitData->total_cost ?? 0, 2);
        $totalProfit = round($profitData->total_profit ?? 0, 2);

        $plans = UserSubscription::with('get_subscription')
            ->select('subscription_id', DB::raw('COUNT(user_id) as users_count'))
            ->when(true, function ($q) use ($applyPeriod) {
                $applyPeriod($q);
            })
            ->where('status', '1')
            ->groupBy('subscription_id')
            ->get();

        $subscriptionPlans = $plans->map(function ($plan) {
            $subscription = $plan->get_subscription;
            $planType     = strtolower((string) ($subscription->plan_type ?? ''));

            if ($planType === 'customize') {
                // Customize plans don't use a single plan_duration value —
                // the real day tiers live in the delivery_days JSON column,
                // e.g. [{"days":15,"amount":750},{"days":10,"amount":500}]
                $deliveryDays = $subscription->delivery_days ?? [];
                if (is_string($deliveryDays)) {
                    $deliveryDays = json_decode($deliveryDays, true) ?: [];
                }

                $days = collect($deliveryDays)
                    ->pluck('days')
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();

                $duration     = $days->isNotEmpty() ? $days->implode(', ') : '—';
                $durationUnit = $days->isNotEmpty() ? 'Days' : '';
            } else {
                // All other plan types (e.g. Best Value) store a plain month count.
                $duration     = $subscription->plan_duration ?? '';
                $durationUnit = $duration !== '' ? 'Months' : '';
            }

            return [
                'name'          => $subscription->plan_name ?? '',
                'amount'        => $subscription->plan_amount ?? 0,
                'duration'      => $duration,
                'duration_unit' => $durationUnit,
                'users'         => $plan->users_count
            ];
        });

        $delivery_partner = DeliveryPartner::when(true, function ($q) use ($applyPeriod) {
            $applyPeriod($q);
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
            'subscriptionPlan' => $subscriptionPlans,
            'delivery_partner' => $delivery_partner,
            'total_profit' => $totalProfit,
            'milk_hubs' => $milkHubs
        ]);
    }
}
