<?php

namespace App\Http\Controllers\API\Milk;

use App\Http\Controllers\Controller;
use App\Models\DailyDelivery;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\UserSubscription;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MilkAPIController extends Controller
{
    public function fetchWalletDetails(Request $request)
    {
        try {
            $user = auth()->user();
            $wallet = Wallet::where('user_id', $user->id)->first();
            if (!$wallet) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Wallet not found for this user.',
                    'response' =>   [
                        'wallet_balance' =>0.0,
                        'validity' =>  null,
                        'subscription' => false,
                        'recent_activity' => [],
                        'createdAt' =>null,
                    ],
                ], 200);
            }
            // Check active subscription
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 1)
                ->latest()
                ->first();

            $subscriptionActive = $subscription ? true : false;
            $validity = $subscription ? $subscription->end_date : null;
            // Fetch recent wallet activities (latest 10)
            $recentActivities = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($activity) {
                    return [
                        'activity_id' => $activity->id,
                        'method' => $activity->type, // credit or debit
                        'date' => $activity->created_at->format('d/m/Y'),
                        'time' => $activity->created_at->format('h:i A'),
                        'amount' => $activity->amount,
                        'description' => $activity->description,
                    ];
                });
            // Prepare response data
            $response = [
                'wallet_balance' => (float) $wallet->balance,
                'validity' => $validity ? Carbon::parse($validity)->format('d/m/Y') : null,
                'subscription' => $subscriptionActive,
                'recent_activity' => $recentActivities,
                'createdAt' => $wallet->created_at->format('d/m/Y'),
            ];

            return response()->json([
                'status' => 200,
                'message' => 'Fetch wallet details successfully.',
                'response' => $response,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSubscriptionDetails()
    {
        try {
            // Fetch all available plans
            $plans = Subscription::select(
                'id as plan_id',
                'plan_amount as plan_amount',
                'plan_pack',
                'plan_type',
                'plan_duration',
                'plan_details',
                'quantity',
                'pack',
                'delivery_days',
            )->get();

            // Format plan details
            $planDetails = $plans->map(function ($plan) {
                return [
                    "plan_id" => $plan->plan_id,
                    "plan_amount" => (float) $plan->plan_amount,
                    "plan_pack" => $plan->plan_pack,
                    "plan_type" => $plan->plan_type,
                    "plan_duration" => $plan->plan_duration,
                    "plan_details" => $plan->plan_details,
                    "quantity" => json_decode($plan->quantity, true) ?? [],
                    "pack" => $plan->pack  ?? [],
                    "delivery_days" => json_decode($plan->delivery_days, true) ?? [],
                ];
            });

            // Success response
            return response()->json([
                "status" => 200,
                "message" => "fetch subscription details successfully.",
                "response" => [
                    "plan_details" => $planDetails,

                ]
            ], 200);
        } catch (\Exception $e) {
            // Error handling
            return response()->json([
                "status" => 500,
                "message" => "Something went wrong.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function getCalendarDetails(Request $request)
    {
        try {
            $userId = auth()->id();
            $monthName = ucfirst(strtolower($request->input('month')));
            if (!$monthName) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Month field is required.'
                ]);
            }
            // Convert month name to number
            $monthNumber = Carbon::parse("1 $monthName")->month;

            $year = Carbon::now()->year;
            // Get active subscription
            $subscription = UserSubscription::with('get_subscription')->where('user_id', $userId)
                ->where('status', 1)
                ->latest()
                ->first();

            $quantity = $subscription->get_subscription->quantity ?? 0;
            $pack = $subscription->get_subscription->pack ?? '';
            if (!$subscription) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No active subscription found for this user.'
                ]);
            }
            // Generate full schedule dates within the subscription period
            $scheduleDates = collect(
                Carbon::parse($subscription->start_date)
                    ->daysUntil(Carbon::parse($subscription->end_date))
            )
                ->filter(fn($d) => $d->month == $monthNumber && $d->year == $year)
                ->map(fn($d) => $d->format('d M Y'))
                ->values()
                ->toArray();
            // Get completed deliveries from DailyDelivery table
            $deliveries = DailyDelivery::where('user_id', $userId)
                ->whereMonth('delivery_date', $monthNumber)
                ->whereYear('delivery_date', $year)
                ->get();
            $completedDates = $deliveries->where('delivery_status', 'delivered')
                ->pluck('delivery_date')
                ->map(fn($d) => Carbon::parse($d)->format('d M Y'))
                ->toArray();

            // Extract cancelled dates (from subscription JSON)
            $cancelledData = json_decode($subscription->cancelled_date, true);
            $cancelledDates = [];

            if (!empty($cancelledData) && is_array($cancelledData)) {
                foreach ($cancelledData as $cancelled) {
                    if (!empty($cancelled['start_date']) && !empty($cancelled['end_date'])) {
                        $start = Carbon::parse($cancelled['start_date']);
                        $end   = Carbon::parse($cancelled['end_date']);

                        $dates = collect($start->daysUntil($end))
                            ->filter(fn($d) => $d->month == $monthNumber && $d->year == $year)
                            ->map(fn($d) => $d->format('d M Y'))
                            ->toArray();

                        $cancelledDates = array_merge($cancelledDates, $dates);
                    }
                }
            }


            // Convert pack (e.g., "500ml", "1ltr", "2ltr", "1/2ltr") to liters
            $packValue = 0;

            if (preg_match('/(\d+)\s*(ml)/i', $pack, $matches)) {
                // e.g. 500ml → 0.5 liter
                $packValue = ((float) $matches[1]) / 1000;
            } elseif (preg_match('/(\d+(?:\/\d+)?)\s*(ltr|tr|lt)/i', $pack, $matches)) {
                // e.g. 1ltr → 1, 2ltr → 2, 1/2ltr → 0.5
                $packValue = eval('return ' . str_replace('ltr', '', $matches[1]) . ';');
            }
            // Completed deliveries count
            $completedCount = count($completedDates);
            // Each delivery quantity × pack size
            $usedLiters = $completedCount * ($quantity * $packValue);
            // Total plan capacity (for all scheduled deliveries)
            $planCapacity = ($quantity * $packValue) * count($scheduleDates);
            // Remaining liters overall
            $remainingLiters = max(0, $planCapacity - $usedLiters);
            // usage_history: only completed deliveries
            $usageHistory = collect($completedDates)
                ->sortBy(function ($date) {
                    return Carbon::createFromFormat('d M Y', $date);
                })
                ->values()
                ->map(function ($date) use ($quantity, $packValue) {
                    return [
                        'date' => $date,
                        'consuming_liters' => round($quantity * $packValue, 2),
                        'status' => 'completed',
                    ];
                });
            //remaining_history: all scheduled (excluding cancelled), showing remaining liters daily
            $cumulativeUsed = 0;
            $mergedDate = array_merge($cancelledDates, $completedDates);
            $remainingHistory = collect($scheduleDates)
                ->reject(function ($date) use ($mergedDate) {
                    // Skip cancelled dates
                    return in_array($date, $mergedDate);
                })
                ->sortBy(function ($date) {
                    return Carbon::createFromFormat('d M Y', $date);
                })
                ->values()
                ->map(function ($date) use ($usageHistory, $planCapacity, &$cumulativeUsed) {
                    // If this date was completed, add to cumulative usage
                    $usedToday = 0;
                    $completed = $usageHistory->firstWhere('date', $date);
                    if ($completed) {
                        $usedToday = $completed['consuming_liters'];
                    }
                    $cumulativeUsed += $usedToday;
                    return [
                        'date' => $date,
                        'remaining_liters' => round(max(0, $planCapacity - $cumulativeUsed), 2),
                        'status' => $completed ? 'completed' : 'pending',
                    ];
                });
            // Final response
            return response()->json([
                'status' => 200,
                'message' => 'Fetch calendar details successfully.',
                'response' => [
                    'month' => $monthName,
                    'subscription_dates' => [
                        'schedule_date' => $scheduleDates,
                        'cancelled_date' => $cancelledDates,
                        'completed_dates' => $completedDates,
                    ],
                    'deliveries' => [
                        'scheduled' => count($scheduleDates),
                        'cancelled' => count($cancelledDates),
                        'completed' => $completedCount,
                    ],
                    'usage_summary' => [
                        'variable_size' => $pack,
                        'used_liters' => round($usedLiters, 2),
                        'remaining_liters' => round($remainingLiters, 2),
                        'usage_history' => $usageHistory,
                        'remaining_history' => $remainingHistory,
                    ],
                    'createdAt' => Carbon::now()->format('d/m/Y'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error fetching calendar details.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getUserPlanDetails(Request $request)
    {
        try {
            $userId = auth()->id();
            $subscription = UserSubscription::with('get_subscription')
                ->where('user_id', $userId)
                ->where('status', 1)
                ->latest()
                ->first();
            if (!$subscription) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No active subscription found.',
                    'response' => (object)[],
                ], 404);
            }
            // Extract subscription & plan details
             $plan = $subscription->get_subscription;
            if(!empty($plan->plan_pack)){
                 $subscriptionType = 'month';
            }else{
                 $subscriptionType = 'days';
            }
            $planId = $plan->id ?? 'N/A';
            $planAmount = $plan->plan_amount ?? 0;
            $deliveryType = $plan->plan_type ?? 'basic'; // e.g. basic, bestValue, customizable
            $pack = $plan->pack ?? '500ml';
            $quantity = $plan->quantity ?? 1;
            $subscriptionType = $subscriptionType; // e.g. month, quarter, half_year

            // Convert start & end dates
            $startDate = Carbon::parse($subscription->start_date);
            $endDate = Carbon::parse($subscription->end_date);
            $today = Carbon::today();

            // Calculate total & remaining subscription days
            $totalDays = $startDate->diffInDays($endDate);
            $remainingDays = $today->lessThanOrEqualTo($endDate)
                ? $today->diffInDays($endDate)
                : 0;

            // Prepare formatted response
            $responseData = [
                'plan_details' => [
                    'plan_id' => $planId,
                    'plan_amount' => $planAmount,
                    'delivery_type' => strtolower($deliveryType),
                    'pack' => $pack,
                    'quantity' => $quantity,
                    'total_subscription_days' => $totalDays,
                    'remaining_subscription_day' => $remainingDays,
                    'plan_start_date' => $startDate->format('d/m/Y'),
                    'plan_end_date' => $endDate->format('d/m/Y'),
                    'subscription_type' => strtolower($subscriptionType),
                ]
            ];

            return response()->json([
                'status' => 200,
                'message' => 'Fetch subscription details successfully.',
                'response' => $responseData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong while fetching subscription details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
