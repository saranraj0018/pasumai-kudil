<?php

namespace App\Http\Controllers\API\Milk;

use Carbon\Carbon;
use App\Models\Wallet;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\DailyDelivery;
use App\Models\UserSubscription;
use App\Http\Controllers\Controller;

class MilkAPIController extends Controller
{
    public function fetchWalletDetails(Request $request)
    {
        try {
            $user = auth()->user();
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 1)
                ->latest()
                ->first();
            if(!empty($subscription)){
                $wallet = Wallet::where(['user_id' => $user->id,'subscription_id' => $subscription->id])->first();
            }else{
                $wallet = null;
            }
            $latestinactivesubscription = UserSubscription::with('get_subscription')->where('user_id', $user->id)
                ->where('status', 2)
                ->pluck('id');
            $previouswalletamount = Wallet::where('user_id' , $user->id)
                ->whereIn('subscription_id' ,$latestinactivesubscription)
                ->pluck('balance')
                ->sum();
            if (!$wallet) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Wallet not found for this user.',
                    'response' =>   [
                        'previous_wallet_balance' => $previouswalletamount,
                        'wallet_balance' =>0.0,
                        'validity' =>  null,
                        'subscription' => false,
                        'recent_activity' => [],
                        'createdAt' =>null,
                    ],
                ], 200);
            }
            // Check active subscription


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
                'previous_wallet_balance' => $previouswalletamount,
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
            )->where('is_show_mobile',1)->get();

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
            $monthNumber = Carbon::parse("1 $monthName")->month;
            // Fetch active subscription
            $subscription = UserSubscription::with('get_subscription')
                ->where('user_id', $userId)
                ->where('status', 1)
                ->latest()
                ->first();
            if (!$subscription) {
                return response()->json([
                    'status' => 200,
                    'message' => 'No active subscription found for this user.',
                    'response' => []
                ]);
            }
            $quantity = $subscription->quantity ?? 0;
            $pack = $subscription->pack ?? '';
            $deliveries = DailyDelivery::where('user_id', $userId)
                ->where('subscription_id', $subscription->id)
                ->whereMonth('delivery_date', $monthNumber)
                ->orderBy('delivery_date', 'asc')
                ->get();
            $scheduleDates = $deliveries
                ->reject(fn($d) => in_array($d->delivery_status, ['cancelled', 'delivered']))
                ->map(fn($d) => [
                    'id' => $d->id,
                    'date' => Carbon::parse($d->delivery_date)->format('d M Y'),
                    'modify' => $d->modify,
                ])
                ->values()
                ->toArray();
            $completedDates = $deliveries->where('delivery_status', 'delivered')
                ->pluck('delivery_date')
                ->map(fn($d) => Carbon::parse($d)->format('d M Y'))
                ->toArray();
            $cancelledDates = $deliveries->where('delivery_status', 'cancelled')
                ->pluck('delivery_date')
                ->map(fn($d) => Carbon::parse($d)->format('d M Y'))
                ->toArray();
            $packValue = 0;
            if (preg_match('/(\d+)\s*(ml)/i', $pack, $matches)) {
                $packValue = ((float) $matches[1]) / 1000;
            } elseif (preg_match('/(\d+(?:\/\d+)?)\s*(ltr|tr|lt)/i', $pack, $matches)) {
                $packValue = eval('return ' . str_replace('ltr', '', $matches[1]) . ';');
            }
            $status = ['cancelled', 'delivered'];
            $completedCount = count($completedDates);
            $usedLiters = $completedCount * ($quantity * $packValue);
            $schedule_quantity = DailyDelivery::where('user_id', $userId)
                ->where('subscription_id', $subscription->id)
                ->whereNotIn('delivery_status', $status)
                ->whereMonth('delivery_date', $monthNumber)
                ->orderBy('delivery_date', 'asc')
                ->pluck('quantity')
                ->sum();
            $planCapacity = ($quantity * $packValue) * count($scheduleDates);
            $remainingLiters = round($schedule_quantity * $packValue, 2);
            $usageHistory = $deliveries->where('delivery_status', 'delivered')
                ->map(function ($delivery) use ($quantity, $packValue) {
                    return [
                        'date' => Carbon::parse($delivery->delivery_date)->format('d M Y'),
                        'consuming_liters' => (double)round($quantity * $packValue, 2),
                        'status' => 'completed',
                    ];
                })
                ->values();
            $cumulativeUsed = 0;
            $remainingHistory = $deliveries
                ->whereNotIn('delivery_status', $status)
                ->map(function ($delivery) use ($quantity, $packValue, &$cumulativeUsed) {
                    $usedToday = $delivery->delivery_status === 'delivered'
                        ? ($quantity * $packValue)
                        : 0;
                    $cumulativeUsed += $usedToday;
                    return [
                        'date' => Carbon::parse($delivery->delivery_date)->format('d M Y'),
                        'remaining_liters' => (double) round($quantity * $packValue, 2),
                        'status' => $delivery->delivery_status,
                    ];
                })
                ->values(); // <-- Removes numeric keys
            $setting = Setting::where('data_key', 'milk_config_time')->first();
            return response()->json([
                'status' => 200,
                'message' => 'Fetched calendar details successfully.',
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
                        'used_liters' => (double) round($usedLiters, 2),
                        'remaining_liters' => (double) round($remainingLiters, 2),
                        'usage_history' => $usageHistory,
                        'remaining_history' =>(object) $remainingHistory,
                    ],
                    'milk_config_time' => $setting->data_value ?? '',
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
