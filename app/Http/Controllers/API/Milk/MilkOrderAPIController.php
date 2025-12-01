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
            $quantity = $subscription->quantity ?? 1;
            $pack = $subscription->pack ?? '500ml';

            $deliveries = DailyDelivery::where('user_id', $userId)
                ->where('subscription_id', $subscription->id)
                ->whereMonth('delivery_date', $monthNumber)
//                ->whereYear('delivery_date', $year)
                ->orderBy('delivery_date', 'asc')
                ->get();

            $scheduleDates = $deliveries
                ->reject(fn($d) => in_array($d->delivery_status, ['cancelled', 'delivered']))
                ->map(fn($d) => Carbon::parse($d->delivery_date)->format('d M Y'))
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
                        'modify_status' => $d->modify,
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
                        'quantity' => (string) $quantity,
                        'order_status' => $deliveryStatus  ?? 'scheduled',
                        'modify_status' => $d->modify,
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
            $quantity = str($subscription->quantity);
            $pack = $subscription->pack ?? '500ml';

            // Step 3: Generate all scheduled dates between start_date and end_date
            $allScheduledDates = collect(
                Carbon::parse($subscription->start_date)
                    ->daysUntil(Carbon::parse($subscription->end_date))
            )->map(fn($d) => $d->format('d M Y'))->values()->toArray();

            // Step 4: Fetch all deliveries for this subscription
            $deliveries = DailyDelivery::where(['user_id' => $subscription->user_id , 'subscription_id' => $subscription->id])->get();

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

            $cancelledDates = $deliveries->where('delivery_status', 'cancelled')
                ->pluck('delivery_date')
                ->map(fn($d) => Carbon::parse($d)->format('d M Y'))
                ->toArray();

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

            // --------------------------
            // VALIDATION
            // --------------------------
            $rules = [
                'order_id' => 'required|integer',
                'plan_id' => 'required|integer|exists:user_subscriptions,subscription_id',
                'status'  => 'required|string|in:cancel,live',
            ];

            if ($request->status === 'live') {
                $rules['extra_quantity'] = 'required|integer|min:1';
            }

            $validated = $request->validate($rules);
            $userId = auth()->id();

            // ---------------------------------
            // LOAD SUBSCRIPTION + ORDER
            // ---------------------------------
            $subscription = UserSubscription::with('get_subscription')
                ->where('user_id', $userId)
                ->where('status', 1)
                ->where('subscription_id', $validated['plan_id'])
                ->first();

            if (!$subscription) {
                return response()->json(['status' => 404, 'message' => 'Active subscription not found']);
            }

            $order = DailyDelivery::find($validated['order_id']);
            $lastOrder = DailyDelivery::where(['user_id' => $userId , 'subscription_id' => $subscription->id])->latest()->first();
            if (!$order) {
                return response()->json(['status' => 404, 'message' => 'Order not found']);
            }

            DB::beginTransaction();

            // COMMON VALUES
            $subscribedQty      = (int)($subscription->quantity ?? 0);
            $unitPrice          = $subscription->price / $subscribedQty;
            $subscribedPack     = $subscription->pack;
            $subscribedEndDate  = Carbon::parse($lastOrder->delivery_date);
            $validDate          = Carbon::parse($subscription->valid_date);
            $today              = Carbon::today();

            if ($subscribedQty <= 0) {
                return response()->json(['status' => 400, 'message' => 'Invalid subscription quantity']);
            }

            // ======================================================
            // CANCEL LOGIC WITH LAST-DAY CHECK
            // ======================================================
            if ($validated['status'] === 'cancel') {

                $cancelDate = Carbon::parse($order->delivery_date);

                // ❗ CONDITION: cancellation on last day not allowed
                if ($cancelDate->equalTo($validDate)) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Cannot cancel on last subscription day',
                    ]);
                }

                // ❗ CONDITION: cancellation not allowed after valid_date
                if ($cancelDate->greaterThan($validDate)) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Cannot cancel, subscription validity expired',
                    ]);
                }

                // ❗ EXISTING CANCEL LOGIC (your logic is good)
                $order->update([
                    'delivery_status' => 'cancelled',
                    'modify' => 2,
                ]);

                // Remaining qty
                $remainingQty = (int)$order->quantity;

                if ($remainingQty <= 0) {
                    return response()->json(['status' => 400, 'message' => 'Order quantity invalid for cancellation']);
                }

                // Calculate new extra days
                $daysNeeded = intdiv($remainingQty, $subscribedQty)
                    + (($remainingQty % $subscribedQty) ? 1 : 0);
                $newEndDate = $subscribedEndDate->copy()->addDays($daysNeeded);
                if ($newEndDate->greaterThan($validDate)) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Cannot cancel, new end date exceeds valid date',
                    ]);
                }

                // ---------------------------
                // SAVE CANCELLED DATE ARRAY
                // ---------------------------
                $existingCancelled = $subscription->cancelled_date
                    ? json_decode($subscription->cancelled_date, true)
                    : [];

                if (!is_array($existingCancelled)) $existingCancelled = [];

                $existingCancelled[] = [
                    'start_date' => $cancelDate->format('Y-m-d'),
                    'end_date'   => $cancelDate->format('Y-m-d'),
                    'order_id'   => $order->id,
                ];

                // ---------------------------
                // CREATE NEW REPLACEMENT DAYS
                // ---------------------------
                $qtyToAllocate = $remainingQty;
                $createdDeliveries = [];

                for ($i = 1; $i <= $daysNeeded; $i++) {

                    $dayQty = min($subscribedQty, $qtyToAllocate);
                    $qtyToAllocate -= $dayQty;

                    $deliveryDate = $subscribedEndDate->copy()->addDays($i)->format('Y-m-d');
                    $amountForRow = round($unitPrice * $dayQty, 2);

                    $exists = DailyDelivery::where('user_id', $userId)
                        ->where('subscription_id', $subscription->id)
                        ->whereDate('delivery_date', $deliveryDate)
                        ->exists();
                    if (!$exists) {
                        $newDelivery = DailyDelivery::create([
                            'user_id' => $userId,
                            'subscription_id' => $subscription->id,
                            'delivery_id' => $order->delivery_id,
                            'delivery_date' => $deliveryDate,
                            'delivery_status' => 'pending',
                            'quantity' => $dayQty,
                            'pack' => $subscribedPack,
                            'amount' => $amountForRow,
                        ]);
                        $createdDeliveries[] = $newDelivery;
                    }
                }
                $lastOrder = DailyDelivery::where(['user_id' => $userId , 'subscription_id' => $subscription->id])->latest()->first();
                // UPDATE SUBSCRIPTION
                $subscription->update([
                    'cancelled_date' => json_encode($existingCancelled),
                    'end_date'       => $lastOrder->delivery_date,
                ]);

                DB::commit();

                return response()->json([
                    'status' => 200,
                    'message' => 'Order cancelled successfully',
                ]);
            }

            // ======================================================
            // LIVE UPDATE LOGIC — STRONG VALIDATION ADDED
            // ======================================================
            if ($validated['status'] === 'live') {

                $extraQty = (int)$validated['extra_quantity'];

                // -----------------------------
                // RULE A: LAST DAY NO UPDATE
                // -----------------------------
                if (Carbon::parse($order->delivery_date)->equalTo($subscribedEndDate)) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Cannot update order on last subscription day',
                    ]);
                }

                // -----------------------------
                // RULE B: Requested quantity > all future pending quantities
                // -----------------------------
                $futureDeliveries = DailyDelivery::where('user_id', $userId)
                    ->where('subscription_id', $subscription->id)
                    ->where('delivery_status', 'pending')
                    ->whereDate('delivery_date', '>', $order->delivery_date)
                    ->get();

                $totalRemainingQty = $futureDeliveries->sum('quantity');

                if ($extraQty > $totalRemainingQty) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Requested extra quantity exceeds remaining pending deliveries',
                    ]);
                }

                // -----------------------------
                // RULE C: extraQuantity > (subscribedQty * remainingDays)
                // -----------------------------
                $remainingDays = $futureDeliveries->count();
                $maxAllowableQty = $remainingDays * $subscribedQty;

                if ($extraQty > $maxAllowableQty) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Extra quantity is too large for the remaining subscription days',
                    ]);
                }

                // ---------------------------------------
                // APPLY YOUR EXISTING LIVE LOGIC
                // ---------------------------------------

                $qtyRemainingToRemove = $extraQty;
                $removedDeliveries = [];
                $updatedDelivery = null;

                foreach ($futureDeliveries->sortByDesc('delivery_date') as $delivery) {
                    if ($qtyRemainingToRemove <= 0) break;

                    if ($delivery->quantity <= $qtyRemainingToRemove) {

                        $qtyRemainingToRemove -= $delivery->quantity;
                        $removedDeliveries[] = $delivery->delivery_date;
                        $delivery->delete();

                    } else {

                        $newQty = $delivery->quantity - $qtyRemainingToRemove;
                        $newAmount = round($newQty * $unitPrice, 2);

                        $delivery->update([
                            'quantity' => $newQty,
                            'amount'   => $newAmount,
                        ]);

                        $updatedDelivery = $delivery;
                        $qtyRemainingToRemove = 0;
                    }
                }

                // Update order
                $extraAmount = round($extraQty * $unitPrice, 2);

                $order->update([
                    'quantity' => $order->quantity + $extraQty,
                    'amount'   => $order->amount + $extraAmount,
                    'modify'   => 2,
                ]);

                // Update subscription end date
                $lastOrder = DailyDelivery::where(['user_id' => $userId , 'subscription_id' => $subscription->id])->latest()->first();
                if ($lastOrder) {
                    $subscription->update(['end_date' => $lastOrder->delivery_date]);
                }

                DB::commit();

                return response()->json([
                    'status' => 200,
                    'message' => 'Extra quantity added successfully',
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Internal error',
                'error'   => $e->getMessage(),
            ]);
        }
    }


}



