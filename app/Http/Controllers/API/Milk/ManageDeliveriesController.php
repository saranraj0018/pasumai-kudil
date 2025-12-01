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
        DB::beginTransaction();

        try {
            // ------------------------- VALIDATION -------------------------
            $rules = [
                'id' => 'required|integer',
                'update_status' => 'required|string|in:cancel,update',
                'change_days' => 'required|array|min:1',
                'change_days.*' => 'required|string',
            ];
            if ($request->update_status === 'update') {
                $rules['change_qty'] = 'required|integer|min:1';
            }
            $validated = $request->validate($rules);

            // Normalize & unique days to Y-m-d
            $changeDays = array_values(array_unique(array_map(function ($d) {
                return Carbon::parse($d)->format('Y-m-d');
            }, $validated['change_days'])));

            // ------------------------- FETCH SUBSCRIPTION -------------------------
            $userId = auth()->id();
            if (!$userId) {
                DB::rollBack();
                return response()->json(['status' => 401, 'message' => 'Unauthenticated'], 401);
            }

            $subscription = UserSubscription::with('get_subscription')
                ->where('user_id', $userId)
                ->where('status', 1)
                ->where('id', $validated['id'])
                ->first();

            if (!$subscription) {
                DB::rollBack();
                return response()->json(['status' => 404, 'message' => 'Active subscription not found'], 404);
            }

            $subQty = (int) $subscription->quantity;
            $subPrice = (float) $subscription->price;
            $unitPrice = ($subQty > 0) ? round($subPrice / $subQty, 2) : 0.0;
            $validDate = Carbon::parse($subscription->valid_date);
            $oldEnd = Carbon::parse($subscription->end_date);

            // -------------------------
            //  CASE 1: CANCEL
            // -------------------------
            if ($validated['update_status'] === 'cancel') {
                $orders = DailyDelivery::where('user_id', $userId)
                    ->where('subscription_id', $subscription->id)
                    ->whereIn('delivery_date', $changeDays)
                    ->get();

                // Ensure all selected dates exist
                if ($orders->count() != count($changeDays)) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 400,
                        'message' => 'Some selected dates do not exist in delivery schedule'
                    ], 200);
                }

                $totalCancelQty = (int) $orders->sum('quantity');
                if ($totalCancelQty <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 400,
                        'message' => 'No quantity available to cancel'
                    ], 200);
                }

                if ($subQty <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 500,
                        'message' => 'Subscription daily quantity invalid'
                    ], 500);
                }

                $extendDays = (int) ceil($totalCancelQty / $subQty);
                $newEnd = $oldEnd->copy()->addDays($extendDays);

                if ($newEnd->gt($validDate)) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 400,
                        'message' => 'Subscription validity exceeded by extension'
                    ], 200);
                }

                // Perform DB changes
                DailyDelivery::where('user_id', $userId)
                    ->where('subscription_id', $subscription->id)
                    ->whereIn('delivery_date', $changeDays)
                    ->update(['delivery_status' => 'cancelled']);

                // create pending deliveries at end to push cancelled qty
                $qtyPending = $totalCancelQty;
                $deliveryId = $orders->first()->delivery_id ?? null;

                for ($i = 1; $i <= $extendDays; $i++) {
                    $qtyToday = min($subQty, $qtyPending);
                    $qtyPending -= $qtyToday;

                    DailyDelivery::create([
                        'user_id' => $userId,
                        'subscription_id' => $subscription->id,
                        'delivery_id' => $deliveryId,
                        'delivery_date' => $oldEnd->copy()->addDays($i)->format('Y-m-d'),
                        'delivery_status' => 'pending',
                        'quantity' => $qtyToday,
                        'pack' => $subscription->pack ?? null,
                        'amount' => round($qtyToday * $unitPrice, 2),
                    ]);
                }

                $subscription->update(['end_date' => $newEnd->format('Y-m-d')]);

                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Cancelled and extended successfully',
                    'extended_days' => $extendDays,
                    'new_end_date' => $newEnd->format('Y-m-d'),
                ]);
            }

            // -------------------------
            //  CASE 2: UPDATE (Increase Qty)
            // -------------------------
            if ($validated['update_status'] === 'update') {
                $increaseQty = (int) $validated['change_qty'];
                $daysCount = count($changeDays);
                $totalRequested = $increaseQty * $daysCount;

                // Ensure selected days exist
                $existingOrders = DailyDelivery::where('user_id', $userId)
                    ->where('subscription_id', $subscription->id)
                    ->whereIn('delivery_date', $changeDays)
                    ->get();

                if ($existingOrders->count() != $daysCount) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 400,
                        'message' => 'Some selected days do not exist in delivery schedule'
                    ], 200);
                }

                // IMPORTANT FIX:
                // fetch pending deliveries but EXCLUDE the selected days.
                // We MUST NOT shrink quantity from the very days being increased.
                $pendingRemovable = DailyDelivery::where('user_id', $userId)
                    ->where('subscription_id', $subscription->id)
                    ->where('delivery_status', 'pending')
                    ->whereNotIn('delivery_date', $changeDays)
                    ->orderBy('delivery_date', 'desc') // shrink from latest pending days
                    ->get();

                if ($pendingRemovable->isEmpty()) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 400,
                        'message' => 'No pending deliveries available to remove quantity from (excluding selected days)'
                    ], 200);
                }

                $maxSelectedDate = collect($changeDays)->map(function($d){ return Carbon::parse($d)->format('Y-m-d'); })->max();

                $pendingRemovable = DailyDelivery::where('user_id', $userId)
                    ->where('subscription_id', $subscription->id)
                    ->where('delivery_status', 'pending')
                    ->where('delivery_date', '>', $maxSelectedDate)
                    ->orderBy('delivery_date', 'desc')
                    ->get();

                if ($pendingRemovable->isEmpty()) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 400,
                        'message' => 'No pending future deliveries available to remove quantity from (must be after the selected days).',

                    ], 200);
                }

                $totalPendingQtyRemovable = (int) $pendingRemovable->sum('quantity');

                // Validate total requested qty is available in removable pending pool
                if ($totalRequested > $totalPendingQtyRemovable) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 400,
                        'message' => "Requested extra {$totalRequested} qty but only {$totalPendingQtyRemovable} removable pending qty available (excluded selected days). Operation aborted."
                    ], 200);
                }

                // All validations passed -> perform removals atomically
                $remainingToRemove = $totalRequested;

                foreach ($pendingRemovable as $p) {
                    if ($remainingToRemove <= 0) break;

                    if ($p->quantity <= $remainingToRemove) {
                        $remainingToRemove -= $p->quantity;
                        $p->delete();
                    } else {
                        $newQty = $p->quantity - $remainingToRemove;
                        $p->update([
                            'quantity' => $newQty,
                            'amount' => round($newQty * $unitPrice, 2),
                            'modify' => 2,
                        ]);
                        $remainingToRemove = 0;
                    }
                }

                if ($remainingToRemove > 0) {
                    // defensive, should not happen due to prior check
                    DB::rollBack();
                    return response()->json([
                        'status' => 500,
                        'message' => 'Unexpected shortage while removing pending quantity'
                    ], 500);
                }

                // Add qty to the selected days
                foreach ($existingOrders as $order) {
                    $newQty = $order->quantity + $increaseQty;
                    $order->update([
                        'quantity' => $newQty,
                        'amount' => round($newQty * $unitPrice, 2),
                        'modify' => 2,
                    ]);
                }

                // Recompute and update subscription end_date from remaining pending items
                $newEndDate = DailyDelivery::where('user_id', $userId)
                    ->where('subscription_id', $subscription->id)
                    ->where('delivery_status', 'pending')
                    ->max('delivery_date');

                if ($newEndDate) {
                    $newEndCarbon = Carbon::parse($newEndDate);
                    if ($newEndCarbon->gt($validDate)) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 400,
                            'message' => 'Operation would exceed subscription validity'
                        ], 200);
                    }
                    $subscription->update(['end_date' => $newEndCarbon->format('Y-m-d')]);
                } else {
                    // Fallback: set end_date to last non-pending delivery or today
                    $fallback = DailyDelivery::where('user_id', $userId)
                        ->where('subscription_id', $subscription->id)
                        ->max('delivery_date');
                    $subscription->update(['end_date' => $fallback ? Carbon::parse($fallback)->format('Y-m-d') : Carbon::now()->format('Y-m-d')]);
                }

                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Quantity updated successfully',
                    'new_end_date' => $subscription->end_date,
                ]);
            }

            DB::rollBack();
            return response()->json(['status' => 400, 'message' => 'Invalid update_status'], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
