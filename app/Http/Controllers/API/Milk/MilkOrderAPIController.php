<?php
namespace App\Http\Controllers\API\Milk;

use App\Http\Controllers\Controller;
use App\Models\DailyDelivery;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MilkOrderAPIController extends Controller
{
    public function fetchOrderDetails(Request $request)
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
            // Convert month name to number (e.g. October -> 10)
            $monthNumber = Carbon::parse("1 $monthName")->month;
            $year = Carbon::now()->year;
            // Get user active subscription
            $subscription = UserSubscription::with('get_subscription')
                ->where('user_id', $userId)
                ->where('status', 1)
                ->latest()
                ->first();
            if (!$subscription) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No active subscription found for this user.'
                ]);
            }
            $quantity = $subscription->get_subscription->quantity ?? 1;
            $pack = $subscription->get_subscription->pack ?? '500ml';

            // Generate all scheduled dates between start_date and end_date
            $scheduleDates = collect(
                Carbon::parse($subscription->start_date)
                    ->daysUntil(Carbon::parse($subscription->end_date))
            )
                ->filter(fn($d) => $d->month == $monthNumber && $d->year == $year)
                ->map(fn($d) => $d->format('d M Y'))
                ->values()
                ->toArray();

            // Completed deliveries from daily_deliveries
            $deliveries = DailyDelivery::where('user_id', $userId)
                ->whereMonth('delivery_date', $monthNumber)
                ->whereYear('delivery_date', $year)
                ->get();

            $completedDates = $deliveries
                ->where('delivery_status', 'delivered')
                ->pluck('delivery_date')
                ->map(fn($d) => Carbon::parse($d)->format('d M Y'))
                ->toArray();

            // Cancelled dates from subscription JSON
            $cancelledDates = [];
            $cancelledData = json_decode($subscription->cancelled_date, true);
            if (!empty($cancelledData['start_date']) && !empty($cancelledData['end_date'])) {
                $start = Carbon::parse($cancelledData['start_date']);
                $end = Carbon::parse($cancelledData['end_date']);
                $cancelledDates = collect($start->daysUntil($end))
                    ->filter(fn($d) => $d->month == $monthNumber && $d->year == $year)
                    ->map(fn($d) => $d->format('d M Y'))
                    ->toArray();
            }

            // Summary counts
            $scheduledCount = count($scheduleDates);
            $cancelledCount = count($cancelledDates);
            $completedCount = count($completedDates);

            // Upcoming vs Past Deliveries (dynamic separation)
            $today = Carbon::today();
            $upcomingDeliveries = $deliveries
                ->filter(fn($d) => Carbon::parse($d->delivery_date)->isAfter($today))
                ->map(function ($d) use ($quantity, $pack, $subscription) {
                  $deliveryStatus = $d->delivery_status === 'pending' ? 'scheduled' : $d->delivery_status;
                    return [
                        'id' => $d->id,
                        'order_id' => 'ORD-' . str_pad($d->id, 4, '0', STR_PAD_LEFT),
                        'order_date' => $d->delivery_date ?? null,
                        // 'order_time' => "07:30 AM",
                        'plan_name' => $subscription->get_subscription->plan_name ?? '',
                        'pack' => $pack,
                        'quantity' => $quantity,
                        'order_status' => $deliveryStatus,
                    ];
                })
                ->values();

            $pastDeliveries = $deliveries
                ->filter(fn($d) => Carbon::parse($d->delivery_date)->isBefore($today) || Carbon::parse($d->delivery_date)->isSameDay($today))
                ->map(function ($d) use ($quantity, $pack, $subscription) {
                $deliveryStatus = $d->delivery_status === 'pending' ? 'scheduled' : $d->delivery_status;
                    return [
                        'id' => $d->id,
                        'order_id' => 'ORD-' . str_pad($d->id, 4, '0', STR_PAD_LEFT),
                        'order_date' => $d->delivery_date ?? null,
                        // 'order_time' => "07:30 AM",
                        'plan_name' => $subscription->get_subscription->plan_name ?? '',
                        'pack' => $pack,
                        'quantity' => $quantity,
                        'order_status' => $deliveryStatus  ?? 'scheduled',
                    ];
                })
                ->values();

            // Final Response
            return response()->json([
                'status' => 200,
                'message' => 'Fetch orders details successfully.',
                'response' => [
                    'month' => $monthName,
                    'subscription_dates' => [
                        'schedule_date' => $scheduleDates,
                        'cancelled_date' => $cancelledDates,
                        'completed_dates' => $completedDates,
                    ],
                    'scheduled' => $scheduledCount,
                    'cancelled' => $cancelledCount,
                    'completed' => $completedCount,
                    'upcoming_deliveries' => $upcomingDeliveries,
                    'past_deliveries' => $pastDeliveries,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error fetching orders details.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getManageDeliveries(Request $request)
    {
        try {
            $userId = auth()->id();

            // Step 1: Get active subscription
            $subscription = UserSubscription::with('get_subscription')
                ->where(['user_id' => $userId, 'status' => 1])
                ->latest()
                ->first();

            if (!$subscription) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No active subscription found for this user.',
                    'response' => []
                ], 404);
            }

            // Step 2: Basic details
            $quantity = $subscription->get_subscription->quantity ?? 1;
            $pack = $subscription->get_subscription->pack ?? '500ml';

            // Step 3: Generate all scheduled dates between start_date and end_date
            $allScheduledDates = collect(
                Carbon::parse($subscription->start_date)
                    ->daysUntil(Carbon::parse($subscription->end_date))
            )->map(fn($d) => $d->format('d M Y'))->values()->toArray();

            // Step 4: Fetch all deliveries for this subscription
            $deliveries = DailyDelivery::where('user_id', $subscription->user_id)->get();

            // Step 5: Identify completed, cancelled, and scheduled dates
            $completedDates = $deliveries
                ->where('delivery_status', 'delivered')
                ->pluck('delivery_date')
                ->map(fn($d) => Carbon::parse($d)->format('d M Y'))
                ->toArray();

            $cancelledDates = [];

            // Step 6: Handle multiple cancelled date ranges from JSON (array format)
            $cancelledData = json_decode($subscription->cancelled_date, true);
            if (is_array($cancelledData)) {
                foreach ($cancelledData as $cancelRange) {
                    if (!empty($cancelRange['start_date']) && !empty($cancelRange['end_date'])) {
                        $start = Carbon::parse($cancelRange['start_date']);
                        $end = Carbon::parse($cancelRange['end_date']);
                        $rangeDates = collect($start->daysUntil($end))
                            ->map(fn($d) => $d->format('d M Y'))
                            ->toArray();
                        $cancelledDates = array_merge($cancelledDates, $rangeDates);
                    }
                }
            }

            // Step 7: Calculate remaining days (scheduled but not completed or cancelled)
            $remainingDates = array_values(array_diff($allScheduledDates, array_merge($completedDates, $cancelledDates)));

            // Step 8: Final response structure
            $responseData = [
                'id' => $subscription->id,
                'remaining' => count($remainingDates),
                'completed' => count($completedDates),
                'cancelled' => count($cancelledDates),
                'pack_of_milk' => $pack,
                'quantity' => $quantity,
                'completed_days' => $completedDates,
                'remaining_days' => $remainingDates,
                'cancelled_days' => $cancelledDates,
            ];

            return response()->json([
                'status' => 200,
                'message' => 'Fetch successfully manage deliveries.',
                'response' => [$responseData]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateOrder(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|integer|exists:daily_deliveries,id',
                'plan_id' => 'required|integer|exists:user_subscriptions,subscription_id',
                'pack' => 'required|string',
                'quantity' => 'required|integer|min:1',
                'status' => 'required|string|in:cancel,live',
            ]);

            $userId = auth()->id();

            $subscription = UserSubscription::with('get_subscription')
                ->where('user_id', $userId)
                ->where('status', 1)
                ->where('subscription_id', $validated['plan_id'])
                ->first();

            if (!$subscription) {
                return response()->json(['status' => 404, 'message' => 'Active subscription not found']);
            }

            $order = DailyDelivery::find($validated['order_id']);
            if (!$order) {
                return response()->json(['status' => 404, 'message' => 'Order not found']);
            }

            DB::beginTransaction();

            // --- Update Order Basic Info ---
            $delivery_status = $validated['status'] === 'cancel' ? 'cancelled' : 'pending';
            $order->update([
                'delivery_status' => $delivery_status,
                'pack' => $validated['pack'],
                'quantity' => $validated['quantity'],
            ]);

            // Subscription details
            $subscribedQuantity = (int) $subscription->get_subscription->quantity;
            $subscribedPack = $subscription->get_subscription->pack;
            $subscribedEndDate = Carbon::parse($subscription->end_date);
            $validDate = Carbon::parse($subscription->valid_date);
            $requestedQty = (int) $validated['quantity'];
            $requestedPack = trim(strtolower($validated['pack']));

            // Conversion helper
            $convertToLiters = function ($pack) {
                $p = strtolower(trim($pack));
                if (strpos($p, 'ml') !== false) {
                    preg_match('/([\d\.]+)/', $p, $m);
                    return isset($m[1]) ? floatval($m[1]) / 1000 : 0;
                }
                if (strpos($p, 'ltr') !== false || strpos($p, 'liter') !== false || strpos($p, 'l') !== false) {
                    preg_match('/([\d\.]+)/', $p, $m);
                    return isset($m[1]) ? floatval($m[1]) : 1;
                }
                if (is_numeric($p)) {
                    $num = floatval($p);
                    return ($num >= 100) ? $num / 1000 : $num;
                }
                return 1;
            };

            $subscribedLitersPerDay = $subscribedQuantity * $convertToLiters($subscribedPack);
            $requestedLiters = $requestedQty * $convertToLiters($requestedPack);

            if ($subscribedLitersPerDay <= 0) {
                DB::rollBack();
                return response()->json(['status' => 400, 'message' => 'Invalid subscription data']);
            }

            $ratio = $requestedLiters / $subscribedLitersPerDay;
            $newEndDate = $subscribedEndDate->copy();
            $actionMsg = "no change";

            // --- Cancel case ---
            if ($validated['status'] === 'cancel') {
                $cancelDate = Carbon::parse($order->delivery_date);
                $existingCancelled = $subscription->cancelled_date
                    ? json_decode($subscription->cancelled_date, true)
                    : [];
                if (!is_array($existingCancelled)) $existingCancelled = [];

                $existingCancelled[] = [
                    'start_date' => $cancelDate->format('Y-m-d'),
                    'end_date' => $cancelDate->format('Y-m-d'),
                ];

                $newEndDate = $subscribedEndDate->copy()->addDay();
                if ($newEndDate->greaterThan($validDate)) $newEndDate = $validDate;

                $subscription->update([
                    'cancelled_date' => json_encode($existingCancelled),
                    'end_date' => $newEndDate->format('Y-m-d'),
                ]);

                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Order cancelled, subscription extended by 1 day.',
                    'response' => ['order' => $order, 'subscription' => $subscription]
                ]);
            }

            // --- Quantity Adjustment Logic ---
            if ($ratio > 1) {
                $extraLiters = $requestedLiters - $subscribedLitersPerDay;
                $daysToReduce = ceil($extraLiters / $subscribedLitersPerDay);
                $newEndDate->subDays($daysToReduce);
                $actionMsg = "reduced by {$daysToReduce} day(s)";
            } elseif ($ratio < 1) {
                $savedLiters = $subscribedLitersPerDay - $requestedLiters;
                $daysToExtend = ceil($savedLiters / $subscribedLitersPerDay);
                $newEndDate->addDays($daysToExtend);
                $actionMsg = "extended by {$daysToExtend} day(s)";
            }

            if ($newEndDate->greaterThan($validDate)) $newEndDate = $validDate;

            $subscription->update(['end_date' => $newEndDate->format('Y-m-d')]);

            // --- Update Daily Deliveries ---
            $subFk =  $subscription->id;
            $cutoffDate = $newEndDate->toDateString();

            // DELETE future deliveries if subscription reduced
            if ($newEndDate->lt($subscribedEndDate)) {
                $deleted = DailyDelivery::where('subscription_id', $subFk)
                    ->where('user_id', $userId)
                    ->whereDate('delivery_date', '>', $cutoffDate)
                    ->delete();
                Log::info("Deleted {$deleted} daily deliveries after {$cutoffDate}");
            }

            // ADD new daily deliveries if extended
            if ($newEndDate->gt($subscribedEndDate)) {
                $start = $subscribedEndDate->copy()->addDay();
                while ($start->lte($newEndDate)) {
                    DailyDelivery::create([
                        'user_id' => $userId,
                        'subscription_id' => $subFk,
                        'delivery_date' => $start->format('Y-m-d'),
                        'delivery_status' => 'pending',
                        'quantity' => $subscribedQuantity,
                        'pack' => $subscribedPack,
                        'amount' => $order->amount,
                    ]);
                    $start->addDay();
                }
            }

            // --- If requested quantity > subscribed quantity ---
            if ($requestedQty > $subscribedQuantity) {
                $remainingQty = $requestedQty - $subscribedQuantity;
                $nextDelivery = DailyDelivery::where('subscription_id', $subFk)
                    ->where('user_id', $userId)
                    ->whereDate('delivery_date', '>', $order->delivery_date)
                    ->orderBy('delivery_date', 'asc')
                    ->first();

                if ($nextDelivery) {
                    $newQty = max($subscribedQuantity - $remainingQty, 1);
                    $nextDelivery->update(['quantity' => $newQty]);
                    Log::info("Next delivery {$nextDelivery->id} quantity adjusted to {$newQty}");
                }
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => "Subscription {$actionMsg}. New end date: {$newEndDate->format('Y-m-d')}",
                'response' => [
                    'order' => $order,
                    'subscription' => $subscription,
                    'subscribed_liters_per_day' => $subscribedLitersPerDay,
                    'requested_liters' => $requestedLiters,
                    'ratio' => round($ratio, 2),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Order Error: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ]);
        }
    }
}



