<?php

namespace App\Http\Controllers\API\Milk;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\DailyDelivery;
use App\Events\NewNotification;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Console\Commands\GenerateDailyDeliveries;

class ManageDeliveriesController extends Controller
{

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
            $get_user = User::where('id', $userId)->first();
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

            $plan = $subscription->get_subscription;
            if (!$plan) {
                DB::rollBack();
                return response()->json(['status' => 400, 'message' => 'Plan details not found for this subscription'], 200);
            }

            $subQty    = (int) $subscription->quantity;
            $validDate = Carbon::parse($subscription->valid_date);
            $oldEnd    = Carbon::parse($subscription->end_date);

            if ($subQty <= 0) {
                DB::rollBack();
                return response()->json(['status' => 500, 'message' => 'Subscription daily quantity invalid'], 500);
            }

            // CORRECT PER-UNIT RATE (plan_type aware, matches delivery job / updateOrder logic)
            $unitPrice = $this->calculateUnitPrice($plan, $subscription, $validDate);

            // -------------------------
            //  CASE 1: CANCEL
            // -------------------------
            if ($validated['update_status'] === 'cancel') {
                $orders = DailyDelivery::where('user_id', $userId)
                    ->where('subscription_id', $subscription->id)
                    ->whereIn('delivery_date', $changeDays)
                    ->get();

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

                $extendDays = (int) ceil($totalCancelQty / $subQty);
                $newEnd = $oldEnd->copy()->addDays($extendDays);

                if ($newEnd->gt($validDate)) {
                    $availableDays = $oldEnd->diffInDays($validDate);
                    $availableDays = $availableDays > 0 ? $availableDays : 0;
                    $availableQty  = $availableDays * $subQty;

                    DB::rollBack();
                    return response()->json([
                        'status' => 400,
                        'message' => "Subscription validity exceeded by extension. Only {$availableDays} day(s) ({$availableQty} unit(s)) remaining before validity ends on {$validDate->format('Y-m-d')}.",
                        'remaining_days' => $availableDays,
                        'remaining_qty' => $availableQty,
                    ], 200);
                }

                // Perform DB changes
                DailyDelivery::where('user_id', $userId)
                    ->where('subscription_id', $subscription->id)
                    ->whereIn('delivery_date', $changeDays)
                    ->update(['delivery_status' => 'cancelled']);

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

                $cancelled_dates = implode(', ', $changeDays);
                $added_dates = [];
                for ($i = 1; $i <= $extendDays; $i++) {
                    $added_dates[] = $oldEnd->copy()->addDays($i)->format('Y-m-d');
                }
                $added_dates_str = implode(', ', $added_dates);
                $message = "$get_user->name has cancelled delivery for dates ($cancelled_dates) and added new delivery dates ($added_dates_str).";
                event(new NewNotification(
                    $userId,
                    "Delivery Cancelled",
                    $message,
                    2,
                    1
                ));
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

                $maxSelectedDate = collect($changeDays)->map(function ($d) {
                    return Carbon::parse($d)->format('Y-m-d');
                })->max();

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

                if ($totalRequested > $totalPendingQtyRemovable) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 400,
                        'message' => "Requested extra {$totalRequested} qty but only {$totalPendingQtyRemovable} removable pending qty available (excluded selected days). Operation aborted."
                    ], 200);
                }

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
                    DB::rollBack();
                    return response()->json([
                        'status' => 500,
                        'message' => 'Unexpected shortage while removing pending quantity'
                    ], 500);
                }

                foreach ($existingOrders as $order) {
                    $newQty = $order->quantity + $increaseQty;
                    $order->update([
                        'quantity' => $newQty,
                        'amount' => round($newQty * $unitPrice, 2),
                        'modify' => 2,
                    ]);
                }

                $newEndDate = DailyDelivery::where('user_id', $userId)
                    ->where('subscription_id', $subscription->id)
                    ->where('delivery_status', 'pending')
                    ->max('delivery_date');

                if ($newEndDate) {
                    $newEndCarbon = Carbon::parse($newEndDate);
                    if ($newEndCarbon->gt($validDate)) {
                        $availableDays = $oldEnd->diffInDays($validDate);
                        $availableDays = $availableDays > 0 ? $availableDays : 0;
                        $availableQty  = $availableDays * $subQty;

                        DB::rollBack();
                        return response()->json([
                            'status' => 400,
                            'message' => "Operation would exceed subscription validity. Only {$availableDays} day(s) ({$availableQty} unit(s)) remaining before validity ends on {$validDate->format('Y-m-d')}.",
                            'remaining_days' => $availableDays,
                            'remaining_qty' => $availableQty,
                        ], 200);
                    }
                    $subscription->update(['end_date' => $newEndCarbon->format('Y-m-d')]);
                } else {
                    $fallback = DailyDelivery::where('user_id', $userId)
                        ->where('subscription_id', $subscription->id)
                        ->max('delivery_date');
                    $subscription->update(['end_date' => $fallback ? Carbon::parse($fallback)->format('Y-m-d') : Carbon::now()->format('Y-m-d')]);
                }

                $updated_dates = implode(', ', $changeDays);
                $removed_data = [];
                foreach ($pendingRemovable as $pr) {
                    $removed_data[] = $pr->delivery_date . " (qty: " . $pr->quantity . ")";
                }
                $removed_str = implode(', ', $removed_data);
                $message = "$get_user->name has updated delivery quantity. Increased $increaseQty qty for dates ($updated_dates). "
                    . "Removed pending quantity from: $removed_str.";
                event(new NewNotification(
                    $userId,
                    "Delivery Updated",
                    $message,
                    2,
                    1
                ));

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

    private function calculateUnitPrice($plan, $subscription, Carbon $validDate)
    {
        if (strtolower($plan->plan_type) === 'customize') {
            return round((float) $plan->plan_amount, 2);
        }

        // Basic / Best Value: total plan_amount spread across the full period.
        $startDate = Carbon::parse($subscription->start_date); // confirm this column name exists
        $totalDays = $startDate->diffInDays($validDate) + 1;
        $totalDays = $totalDays > 0 ? $totalDays : 1;

        return round((float) $plan->plan_amount / $totalDays, 2);
    }
}
