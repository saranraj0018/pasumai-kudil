<?php

namespace App\Http\Controllers\API\Milk;

use App\Console\Commands\GenerateDailyDeliveries;
use App\Http\Controllers\Controller;
use App\Models\DailyDelivery;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageDeliveriesController extends Controller
{
//    public function manageDeliveries(Request $request)
//    {
//        $userId = auth()->id();
//        $deliveries = DailyDelivery::where('user_id', $userId)->get();
//        if ($deliveries->isEmpty()) {
//            return response()->json([
//                'status' => 404,
//                'message' => 'No deliveries found for this user.'
//            ], 404);
//        }
//        $completed = $deliveries->where('delivery_status', 'delivered');
//        $pending = $deliveries->where('delivery_status', 'pending');
//        $cancelled = $deliveries->where('delivery_status', 'cancelled');
//        $completedDays = $completed->pluck('delivery_date')->map(function ($date) {
//            return \Carbon\Carbon::parse($date)->format('d M Y');
//        })->values();
//        $remainingDays = $pending->pluck('delivery_date')->map(function ($date) {
//            return \Carbon\Carbon::parse($date)->format('d M Y');
//        })->values();
//        $cancelledDays = $cancelled->pluck('delivery_date')->map(function ($date) {
//            return \Carbon\Carbon::parse($date)->format('d M Y');
//        })->values();
//        $first = $deliveries->first();
//        $response = [
//            'id' => $first->subscription_id,
//            'remaining' => $remainingDays->count(),
//            'completed' => $completedDays->count(),
//            'cancelled' => $cancelledDays->count(),
//            'pack_of_milk' => $first->pack ?? '',
//            'quantity' => $first->quantity ?? 0,
//            'completed_days' => $completedDays,
//            'remaining_days' => $remainingDays,
//            'cancelled_days' => $cancelledDays,
//        ];
//        return response()->json([
//            'status' => 200,
//            'message' => 'fetch successfully manage deliveries.',
//            'response' => $response,
//        ]);
//    }

   public function updateManageDeliveries(Request $request)
{
    try {
        $validated = $request->validate([
            'id' => 'required|integer', // subscription id
            'update_status' => 'required|string|in:cancel,update',
            'change_qty' => 'nullable|integer|min:1',
            'change_days' => 'nullable|array',
        ]);

        // Normalize incoming dates (flexible parsing)
        $changeDays = [];
        if (!empty($validated['change_days'])) {
            foreach ($validated['change_days'] as $dateStr) {
                try {
                    $changeDays[] = Carbon::parse($dateStr)->format('Y-m-d');
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 400,
                        'message' => "Invalid date format: {$dateStr}. Please use a parseable date string like 'YYYY-MM-DD' or '9 July 2025'."
                    ], 400);
                }
            }
            // unique & sort
            $changeDays = array_values(array_unique($changeDays));
            sort($changeDays);
        }

        $userId = auth()->id();
        DB::beginTransaction();

        // load subscription with plan
        $subscription = UserSubscription::with('get_subscription')
            ->where('user_id', $userId)
            ->where('status', 1)
            ->where('id', $validated['id'])
            ->first();

        if (!$subscription) {
            DB::rollBack();
            return response()->json(['status' => 404, 'message' => 'Active subscription not found.'], 404);
        }

        $order = DailyDelivery::where([
            'user_id' => $userId,
            'subscription_id' => $subscription->id
        ])->first();



        $subscribedQuantity = (int) $subscription->get_subscription->quantity;
        $subscribedPack = $subscription->get_subscription->pack;


        $subscribedEndDate = Carbon::parse($subscription->end_date);
        $validDate = Carbon::parse($subscription->valid_date);

        // ---------------------------
        // CANCEL FLOW
        // ---------------------------
        if ($validated['update_status'] === 'cancel') {
            if (empty($changeDays)) {
                DB::rollBack();
                return response()->json(['status' => 400, 'message' => 'Please provide change_days for cancellation.'], 400);
            }

            $existingCancelled = [];
            if (!empty($subscription->cancelled_date)) {
                $decoded = json_decode($subscription->cancelled_date, true);
                if (is_array($decoded)) {
                    $existingCancelled = $decoded;
                }
            }

            $newlyCancelledDates = [];

            foreach ($changeDays as $date) {
                $delivery = DailyDelivery::where('subscription_id', $subscription->id)
                    ->where('user_id', $userId)
                    ->whereDate('delivery_date', $date)
                    ->first();

                if ($delivery) {
                    if ($delivery->delivery_status !== 'cancelled') {
                        $delivery->update(['delivery_status' => 'cancelled']);
                        $newlyCancelledDates[] = $date;
                    }
                } else {
                    DB::rollBack();
                    return response()->json(['status' => 400, 'message' => "No delivery found for {$date}."], 400);
                }
            }

            if (empty($newlyCancelledDates)) {
                DB::rollBack();
                return response()->json(['status' => 200, 'message' => 'No pending deliveries were cancelled (already cancelled).']);
            }

            // merge into cancelled_date in [ {start_date, end_date} ] format
            $cancelStart = min($newlyCancelledDates);
            $cancelEnd = max($newlyCancelledDates);

            $existingCancelled[] = [
                'start_date' => $cancelStart,
                'end_date' => $cancelEnd,
            ];

            $subscription->cancelled_date = json_encode($existingCancelled);

            // Extend end_date by count of newly cancelled days (capped to valid_date)
            $extendBy = count($newlyCancelledDates);
            $newEndDate = $subscribedEndDate->copy()->addDays($extendBy);
            if ($newEndDate->gt($validDate)) {
                $newEndDate = $validDate->copy();
            }

            $oldEndDate = $subscribedEndDate->copy();
            $subscription->end_date = $newEndDate->format('Y-m-d');
            $subscription->save();

            // create new pending deliveries for extended days
            $created = 0;
            if ($newEndDate->gt($oldEndDate)) {
                $start = $oldEndDate->copy()->addDay();
                while ($start->lte($newEndDate)) {
                    $exists = DailyDelivery::where('subscription_id', $subscription->id)
                        ->where('user_id', $userId)
                        ->whereDate('delivery_date', $start->format('Y-m-d'))
                        ->exists();

                    if (!$exists) {
                        DailyDelivery::create([
                            'user_id' => $userId,
                            'subscription_id' => $subscription->id,
                            'delivery_id' => $order->delivery_id ?? '',
                            'delivery_date' => $start->format('Y-m-d'),
                            'delivery_status' => 'pending',
                            'quantity' => $subscribedQuantity,
                            'pack' => $subscribedPack,
                            'amount' => $order->amount ?? null,
                        ]);
                        $created++;
                    }
                    $start->addDay();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => "Cancelled " . count($newlyCancelledDates) . " day(s). Subscription extended by {$extendBy} day(s).",
                'response' => [
                    'subscription' => $subscription->fresh(),
                    'cancelled_blocks' => $existingCancelled,
                    'created_new_deliveries' => $created,
                ],
            ]);
        }

        $changeQty = $validated['change_qty'] ?? null;
        if ($changeQty === null || empty($changeDays)) {
            DB::rollBack();
            return response()->json([
                'status' => 400,
                'message' => 'Please provide both change_qty and change_days for updates.'
            ], 400);
        }

        foreach ($changeDays as $d) {
            $delivery = DailyDelivery::where('subscription_id', $subscription->id)
                ->where('user_id', $userId)
                ->whereDate('delivery_date', $d)
                ->first();

            if (!$delivery) {
                DB::rollBack();
                return response()->json(['status' => 400, 'message' => "No delivery found for {$d}."], 400);
            }
        }

        $actionMsg = 'no change';
        $newEndDate = $subscribedEndDate->copy();

        if ($newEndDate->gt($validDate)) $newEndDate = $validDate->copy();

        $subscription->update(['end_date' => $newEndDate->format('Y-m-d')]);

        foreach ($changeDays as $d) {
            DailyDelivery::where('subscription_id', $subscription->id)
                ->where('user_id', $userId)
                ->whereDate('delivery_date', $d)
                ->update([
                    'quantity' => $changeQty,
                ]);
        }

        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => "Subscription {$actionMsg}. New end date: {$newEndDate->format('Y-m-d')}",
            'response' => [
                'subscription' => $subscription->fresh(),
            ],
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Update Order Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        return response()->json([
            'status' => 500,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
