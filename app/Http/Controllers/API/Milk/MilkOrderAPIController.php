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
                ->where('subscription_id', $subscription->id)
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
                ->map(function ($d) {
                    return [
                        'id' => $d->id,
                        'order_id' => 'ORD-' . str_pad($d->id, 4, '0', STR_PAD_LEFT),
                        'order_date' => $d->delivery_date ?? null,
                        'plan_name' => $subscription->get_subscription->plan_name ?? '',
                        'pack' => $d->pack,
                        'quantity' => (string)$d->quantity,
                        'order_status' => $d->delivery_status,
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
                    'user_subscription' => $subscription->id ?? 0,
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
            $completedDates = collect($deliveries) // ensure it's a collection
            ->where('delivery_status', 'delivered')
                ->map(function ($delivery) {
                    return [
                        'delivery_date' => Carbon::parse($delivery->delivery_date)->format('d M Y'),
                        'modify' => (int) ($delivery->modify ?? 0),
                        'quantity' => (int) ($delivery->quantity ?? 0),
                    ];
                })
                ->values()
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
            $remainingDates = collect($deliveries) // ensure it's a collection
            ->where('delivery_status', 'pending')
                ->map(function ($delivery) {
                    return [
                        'delivery_date' => Carbon::parse($delivery->delivery_date)->format('d M Y'),
                        'modify' => (int) ($delivery->modify ?? 0),
                        'quantity' => (int) ($delivery->quantity ?? 0),
                    ];
                })
                ->values()
                ->toArray();
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
                'order_id' => 'required',
                'plan_id' => 'required|integer|exists:user_subscriptions,subscription_id',
                'extra_quantity' => 'required|integer|min:1',
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
            ]);

            // Subscription details
            $subscribedQuantity = (int) $subscription->get_subscription->quantity;
            $subscribedPack = $subscription->get_subscription->pack;
            $subscribedEndDate = Carbon::parse($subscription->end_date);
            $validDate = Carbon::parse($subscription->valid_date);
            $requestedQty = (int) $validated['extra_quantity'];

            $subscribedLitersPerDay = $subscribedQuantity;

            if ($subscribedLitersPerDay <= 0) {
                DB::rollBack();
                return response()->json(['status' => 400, 'message' => 'Invalid subscription data']);
            }

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

                DailyDelivery::create([
                    'user_id' => $userId,
                    'subscription_id' => $subscription->id,
                    'delivery_id' => $order->delivery_id,
                    'delivery_date' => $newEndDate->format('Y-m-d'),
                    'delivery_status' => 'pending',
                    'quantity' => $subscribedQuantity,
                    'pack' => $subscribedPack,
                    'amount' => $order->amount,
                ]);

                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Order cancelled, subscription extended by 1 day.',
                    'response' => ['order' => $order, 'subscription' => $subscription]
                ]);
            } else {

                $extra_quantity = $requestedQty;
                $daily_quantity = $subscribedQuantity;
                $schedule = [];

                $last_date = Carbon::parse($subscription->end_date);

                while ($extra_quantity > 0) {
                    $quantity_today = min($daily_quantity, $extra_quantity);
                    $schedule[$last_date->format('d-m-Y')] = $quantity_today;
                    $extra_quantity -= $quantity_today;
                    $last_date->subDay();
                }

                $schedule_date = $schedule;

                foreach ($schedule_date as $date => $quantity) {
                     $formattedDate = Carbon::createFromFormat('d-m-Y', $date)->toDateString();

                  $delivery = DailyDelivery::where('user_id', $userId)
                        ->whereDate('delivery_date', $formattedDate);
                    // If quantity == 0 → remove row
                    if ($subscribedQuantity == $quantity) {
                        $delivery->delete();
                        continue;
                    } else {
                        $final_date = Carbon::parse($date)->format('Y-m-d');
                        $delivery->update(['quantity' => $quantity]);
                    }

                    DailyDelivery::where('id',$request['order_id'])->update(['quantity' => $order->quantity + $requestedQty,'modify' => 2]);

                    $subscription = UserSubscription::where('id', $order->subscription_id )->update(['end_date' => $final_date]);
                }

            }


            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => "Subscription",
                'response' => [
                    'order' => $order,
                    'subscription' => $subscription,
                    'subscribed_liters_per_day' => $subscribedLitersPerDay,
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



