<?php

namespace App\Http\Controllers\API\Milk;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MilkHomeAPIController extends Controller
{
    public function fetchHomeDetails(Request $request)
    {
        try {

            $userId = auth()->id() ?? session()->getId();
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Unauthorized user',
                ], 401);
            }

            // Get wallet balance
            $wallet = Wallet::where('user_id', $user->id)->first();
            $walletBalance = $wallet ? $wallet->balance : 0;

            // Get active subscription
            $subscription = UserSubscription::with('get_subscription')->where('user_id', $user->id)
            ->where('status', 1)
            ->latest()
            ->first();
            $banner = Banner::whereIn('type',['MilkMain', 'MilkSub'])->pluck('image_url')->toArray();
            if (!$subscription) {
                return response()->json([
                    'status' => 200,
                    'message' => 'No active subscription found.',
                    'response' => [
                        'user_image' => $user->image_url ?? null,
                        'user_name' => $user->name ?? '',
                        'plan_status' => 'inactive',
                        'wallet_balance' => (string) $walletBalance,
                        'banner' => $banner,
                        'plan_details' => (object) []
                        ]
                    ]);
                }
            // Calculate remaining days
            $startDate = Carbon::parse($subscription->start_date);
            $endDate = Carbon::parse($subscription->end_date);
            $remainingDays = max($endDate->diffInDays(Carbon::now(), false), 0);

            // Build response
            $response = [
                'user_image' => $user->image ?? 'https://example.com/default_user.jpg',
                'user_name' => $user->name,
                'plan_status' => $subscription->status,
                'wallet_balance' => (string) $walletBalance,
                'banner' => $banner,
                'plan_details' => [
                    'plan_id' => $subscription->plan_id,
                    'plan_amount' => $subscription->amount,
                    'delivery_type' => $subscription->delivery_type,
                    'pack' => $subscription->pack,
                    'quantity' => $subscription->quantity,
                    'total_subscription_days' => (int) $subscription->total_days,
                    'remaining_subscription_day' => $remainingDays,
                    'plan_start_date' => $startDate->format('d/m/Y'),
                    'plan_end_date' => $endDate->format('d/m/Y'),
                    'subscription_type' => $subscription->subscription_type,
                ],
            ];

            return response()->json([
                'status' => 200,
                'message' => 'Fetch home details successfully.',
                'response' => $response,
            ], 200);
        } catch (\Exception $e) {
            print_r($e->getMessage()); exit;
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function cancelSubscription(Request $request)
    {

        $validated = $request->validate([
            'plan_id' => 'required',
            'remaining_subscription_days' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
            'account_details.account_holder_name' => 'required|string|max:100',
            'account_details.bank_name' => 'required|string|max:100',
            'account_details.account_number' => 'required|numeric',
            'account_details.ifsc_code' => 'required|string|max:20',
            'account_details.branch' => 'required|string|max:100',
        ]);

        try {
            DB::beginTransaction();
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Unauthorized user.'
                ], 401);
            }
            // 🔹 Step 1: Find the active subscription
            $subscription = UserSubscription::where('user_id', $user->id)
            ->where('id', $validated['plan_id'])
            ->where('status', 1)
            ->first();
            if (!$subscription) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Active subscription not found for this plan.'
                ], 404);
            }
            // 🔹 Step 2: Update subscription as cancelled
            $subscription->update([
                'status' => 2,
                'description' => $validated['reason'],
                'cancelled_at' => now(),
                'in_active_date' => Carbon::today()->toDateString(),
            ]);
            // 🔹 Step 3: Update user's bank details
            $update = User::where('id',$user->id)->update([
                'account_holder_name' => $validated['account_details']['account_holder_name'],
                'bank_name'           => $validated['account_details']['bank_name'],
                'account_number'      => $validated['account_details']['account_number'],
                'ifsc_code'           => $validated['account_details']['ifsc_code'],
                'branch'              => $validated['account_details']['branch'],
            ]);
            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Subscription cancelled successfully and account details updated.',
                'response' => [
                    'plan_id' => $subscription->id,
                    'remaining_subscription_days' =>  $validated['remaining_subscription_days'],
                    'cancel_reason' => $subscription->description,
                    'account_details' => [
                        'account_holder_name' => $user->account_holder_name,
                        'bank_name' => $user->bank_name,
                        'account_number' => $user->account_number,
                        'ifsc_code' => $user->ifsc_code,
                        'branch' => $user->branch,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while cancelling the subscription.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
