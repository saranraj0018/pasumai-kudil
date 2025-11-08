<?php

namespace App\Http\Controllers\API\Milk;

use App\Http\Controllers\Controller;
use App\Jobs\DailyDeliveries;
use App\Jobs\GenerateDailyDeliveries;
use App\Models\Banner;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Wallet;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MilkHomeAPIController extends Controller
{
    public function fetchHomeDetails(Request $request)
    {
        try {

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

            $banner = Banner::where('type', 'MilkMain')
                ->get()
                ->map(function ($item) {
                    return url('/storage/' . ltrim($item->image_url, '/'));
                })
                ->toArray();
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
                'user_image' => $user->image ? url('storage/'.$user->image) : 'https://example.com/default_user.jpg',
                'user_name' => $user->name,
                'plan_status' => $subscription->status,
                'wallet_balance' => (string) $walletBalance,
                'banner' => $banner,
                'plan_details' => [
                    'plan_id' => $subscription->subscription_id,
                    'plan_amount' => $subscription->get_subscription?->plan_amount ?? 0,
                    'delivery_type' => $subscription->delivery_type ?? 'Daily',
                    'pack' => $subscription->pack,
                    'quantity' => $subscription->quantity,
                    'total_subscription_days' => (int) $subscription->days ?? 0,
                    'remaining_subscription_day' => $remainingDays,
                    'plan_start_date' => $startDate->format('d/m/Y'),
                    'plan_end_date' => $endDate->format('d/m/Y'),
                    'subscription_type' => $subscription->get_subscription?->plan_type ?? null,
                ],
            ];

            return response()->json([
                'status' => 200,
                'message' => 'Fetch home details successfully.',
                'response' => $response,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "order_amount" => 'required|numeric',
//            "paymentMode" => 'required|string', need to change
//            "addressId" => 'required|string',
        ]);

        #check if successful
        if ($validator->fails())
            return response()->json([
                'status' => 500,
                'message' => $validator->errors()->first(),
            ], 500);
        $razorPayOrder = razorPay()->createOrder($request['order_amount']);
        return response()->json([
            'status' => 200,
            'message' => 'Subscription created successfully!',
            'order_id' => $razorPayOrder->id,
        ], 200);

    }

    public function subscriptionPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_id'           => 'required|integer',
//            "transactionId" => "required", store db
            "order_id" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }


        DB::beginTransaction();
        try {
            $subscription = Subscription::findOrFail($request['subscription_id']);

            if ($request->filled('custom_days')) {
                $daycount = (int) $request['custom_days'];
                $daymonth = false;
            } else {
                $daycount = (int) $subscription->plan_pack;
                $daymonth = true;
            }

            $start_date = Carbon::now()->addDay();

            if ($daymonth) {
                $end_date = $start_date->copy()->addMonthsNoOverflow($daycount);
            } else {
                $end_date = $start_date->copy()->addDays($daycount);
            }

            $validdaycount = (int) ($subscription->plan_duration ?? 0);
            $valid_date = $end_date->copy()->addDays($validdaycount);

            $start_date_formatted = $start_date->format('Y-m-d');
            $end_date_formatted   = $end_date->format('Y-m-d');
            $valid_date_formatted = $valid_date->format('Y-m-d');

            $startDate = Carbon::parse($start_date_formatted);
            $endDate = Carbon::parse($end_date_formatted);

            $user = $request->user();
            $user = $user ? User::where('mobile_number', $user['mobile_number'])->first() : null;

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'User Not found.',
                ]);
            }

            $existing_subscription = UserSubscription::where('user_id', $user->id)->where('status',1)->first();

            if (!empty($existing_subscription)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has an active subscription. Please deactivate it before adding a new one.'
                ], 400);
            }

            $user_subscription = new UserSubscription();
            $user_subscription->user_id         = $user->id;
            $user_subscription->subscription_id = $subscription->id;
            $user_subscription->start_date      = $start_date_formatted;
            $user_subscription->end_date        = $end_date_formatted;
            $user_subscription->valid_date      = $valid_date_formatted;
            $user_subscription->pack            = $subscription->pack ?? 0;
            $user_subscription->quantity        = !empty($request['quantity']) ? $request['quantity'] : $subscription->quantity;
            $user_subscription->status        = 1;
            $user_subscription->days            = $startDate->diffInDays($endDate) ?? 0;
            $user_subscription->save();

            User::where('id', $user->id)->update(['subscription_id' => $user_subscription->id]);

            DB::commit();
            $start = Carbon::parse($user_subscription->start_date);
            $end   = Carbon::parse($user_subscription->end_date);
            $dates = collect();


            while ($start->lte($end)) {
                $dates->push($start->toDateString());
                $start->addDay();
            }
            foreach ($dates->chunk(50) as $chunk) {
                GenerateDailyDeliveries::dispatch($user_subscription, $chunk->toArray());
            }
            return response()->json([
                'success' => true,
                'message' => 'Subscription added successfully!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save user',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function cancelSubscription(Request $request)
    {

        $validated = $request->validate([
            'plan_id' => 'required',
            'user_id' => 'required|integer',
            'reason' => 'required|string|max:255',
            'account_details.account_holder_name' => 'required|string|max:100',
            'account_details.bank_name' => 'required|string|max:100',
            'account_details.account_number' => 'required|string',
            'account_details.ifsc_code' => 'required|string|max:20',
            'account_details.branch' => 'required|string|max:100',
        ]);
        try {
            DB::beginTransaction();

            $subscription = UserSubscription::where('user_id', $request['user_id'])
            ->where('subscription_id', $validated['plan_id'])
            ->where('status', 1)
            ->first();
            if (!$subscription) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Active subscription not found for this plan.'
                ], 404);
            }

            $subscription->update([
                'status' => 2,
                'description' => $validated['reason'],
                'cancelled_at' => now(),
                'in_active_date' => Carbon::today()->toDateString(),
            ]);

            User::where('id',$request['user_id'])->update([
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
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' =>  $e->getMessage(),
            ], 500);
        }
    }
}
