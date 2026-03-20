<?php

namespace App\Http\Controllers\API\Milk;

use App\Models\Payment;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Banner;
use App\Models\Wallet;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\DailyDelivery;
use App\Events\NewNotification;
use App\Models\DeliveryPartner;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\GenerateDailyDeliveries;
use Illuminate\Support\Facades\Cache;
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
            $subscription = UserSubscription::with('get_subscription')->where('user_id', $user->id)
                ->where('status', 1)
                ->latest()
                ->first();
            $latestinactivesubscription = UserSubscription::with('get_subscription')->where('user_id', $user->id)
                ->where('status', 2)
                ->pluck('id');
            $previouswalletamount = Wallet::where('user_id', $user->id)
                ->whereIn('subscription_id', $latestinactivesubscription)
                ->pluck('balance')
                ->sum();

            // Get wallet balance
            if (!empty($subscription)) {
                $wallet = Wallet::where(['user_id' => $user->id, 'subscription_id' => $subscription->id])->first();
            } else {
                $wallet = null;
            }


            $walletBalance = $wallet ? $wallet->balance : 0;

            // Get active subscription


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
                        'user_image' => $user->image ? url('storage/' . $user->image) : 'https://example.com/default_user.jpg',
                        'user_name' => $user->name ?? '',
                        'plan_status' => 'inactive',
                        'previous_wallet_balance' => (string) $previouswalletamount,
                        'wallet_balance' => (string) $walletBalance,
                        'banner' => $banner,
                        'plan_details' => (object) [],
                        'milk_config_time' => '',
                        'customer_id' =>  $user->prefix ?? '',
                    ]
                ]);
            }
            // Calculate remaining days
            $startDate = Carbon::parse($subscription->start_date);
            $endDate = Carbon::parse($subscription->end_date);
            // $remainingDays = max($endDate->diffInDays(Carbon::now(), false), 0);
            $remainingDays = DailyDelivery::where(['user_id' => $user->id, 'subscription_id' => $subscription->id, 'delivery_status' => 'pending'])->count();
            $completedDays = DailyDelivery::where(['user_id' => $user->id, 'subscription_id' => $subscription->id, 'delivery_status' => 'delivered'])->count();
            $setting = Setting::where('data_key', 'milk_config_time')->first();
            // Build response
            $response = [
                'user_image' => $user->image ? url('storage/' . $user->image) : 'https://example.com/default_user.jpg',
                'user_name' => $user->name,
                'plan_status' => $subscription->status,
                'previous_wallet_balance' => (string) $previouswalletamount,
                'wallet_balance' => (string) $walletBalance,
                'banner' => $banner,
                'milk_config_time' => $setting->data_value ?? '',
                'plan_details' => [
                    'plan_id' => $subscription->subscription_id,
                    'plan_amount' => $subscription->get_subscription?->plan_amount ?? 0,
                    'delivery_type' => $subscription->delivery_type ?? 'Daily',
                    'pack' => $subscription->pack,
                    'quantity' => $subscription->quantity,
                    'total_subscription_days' => $subscription->days,
                    'completed_days' => $completedDays,
                    'remaining_subscription_day' => $remainingDays,
                    'plan_start_date' => $startDate->format('d/m/Y'),
                    'plan_end_date' => $endDate->format('d/m/Y'),
                    'subscription_type' => $subscription->get_subscription?->plan_type ?? null,
                ],
                'customer_id' =>  $user->prefix ?? '',
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
        ]);

        #check if successful
        if ($validator->fails())
            return response()->json([
                'status' => 500,
                'message' => $validator->errors()->first(),
            ], 500);
        $user = $request->user();

        $existing = UserSubscription::where('user_id', $user->id)
            ->where('status', 1)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'User already has an active subscription. Please deactivate it first.',
            ], 400);
        }

        $user_address = User::where('id', $user->id)->first();

        if ($user_address->address == null && $user_address->address == '' ||
            $user_address->latitude == '' && $user_address->latitude == null ||
            $user_address->longitude == null && $user_address->longitude == '' || $user_address->city == null && $user_address->city == '') {
            return response()->json([
                'status' => 404,
                'message' => 'Address not found.Please enter your address!',
            ], 404);
        }

        $partner = $this->getMappedDeliveryPartner($user);

        if (!$partner) {
            return response()->json([
                'status' => 404,
                'message' => 'Nearby delivery partners not found.',
            ], 404);
        }

        $orderId = 'order_' . time();

        $baseUrl = env('CASHFREE_ENV') === 'sandbox'
            ? 'https://sandbox.cashfree.com'
            : 'https://api.cashfree.com';

        $headers = [
            "Content-Type: application/json",
            "x-api-version: 2023-08-01",
            "x-client-id: " . env('CASHFREE_APP_ID'),
            "x-client-secret: " . env('CASHFREE_SECRET_KEY'),
        ];

        $amount = (float) preg_replace('/[^\d.]/', '', $request->order_amount);

        $orderPayload = [
            "order_id" => $orderId,
            "order_amount" => $amount,
            "order_currency" => "INR",
            "customer_details" => [
                "customer_id" => (string) $user->id,
                "customer_name" => $user->name,
                "customer_email" => $user->email,
                "customer_phone" => $user->mobile_number,
            ],
        ];

        $ch = curl_init("$baseUrl/pg/orders");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($orderPayload),
        ]);

        $orderResponse = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!isset($orderResponse['payment_session_id'])) {
            return response()->json($orderResponse, 500);
        }

        $payment = new Payment();
        $payment->user_id = $user->id;
        $payment->address_id = $request['address_id'];
        $payment->order_id = $orderId;
        $payment->amount = $amount;
        $payment->status = 'PENDING';
        $payment->save();


        return response()->json([
            'status' => 200,
            'message' => 'Order Created successful',
            'order_id' => $orderResponse['order_id'],
            'payment_session_id' => $orderResponse['payment_session_id'],
        ]);
    }

    private function getMappedDeliveryPartner($user)
    {
        $partners = DeliveryPartner::with('get_map_address', 'get_hub')->get();
        foreach ($partners as $partner) {
            if ($partner->get_hub->type == 1) continue;
            if (!$partner->get_map_address) continue;
            $coords = json_decode($partner->get_map_address->coordinates, true);
            if (!is_array($coords)) continue;

            if ($this->isPointInPolygon((float)$user->latitude, (float)$user->longitude, $coords)) {
                return $partner;
            }
        }
        return null;
    }

    private function isPointInPolygon($lat, $lng, array $polygon)
    {
        $inside = false;
        $numPoints = count($polygon);
        $j = $numPoints - 1;

        // Convert to float once
        for ($i = 0; $i < $numPoints; $i++) {
            $polygon[$i]['lat'] = (float)$polygon[$i]['lat'];
            $polygon[$i]['lng'] = (float)$polygon[$i]['lng'];
        }

        // Check if point is exactly on a vertex
        foreach ($polygon as $point) {
            if (abs($lat - $point['lat']) < 1e-9 && abs($lng - $point['lng']) < 1e-9) {
                return true;
            }
        }

        // Check if point is on any polygon edge
        for ($i = 0; $i < $numPoints; $i++) {
            $iLat = $polygon[$i]['lat'];
            $iLng = $polygon[$i]['lng'];
            $jLat = $polygon[$j]['lat'];
            $jLng = $polygon[$j]['lng'];

            // Check collinearity & within segment bounds
            $cross = ($lng - $iLng) * ($jLat - $iLat) - ($lat - $iLat) * ($jLng - $iLng);
            if (abs($cross) < 1e-9) {
                if (
                    $lat >= min($iLat, $jLat) && $lat <= max($iLat, $jLat) &&
                    $lng >= min($iLng, $jLng) && $lng <= max($iLng, $jLng)
                ) {
                    return true;
                }
            }

            $j = $i;
        }

        // Normal ray-casting
        $j = $numPoints - 1;
        for ($i = 0; $i < $numPoints; $i++) {
            $lat_i = $polygon[$i]['lat'];
            $lng_i = $polygon[$i]['lng'];
            $lat_j = $polygon[$j]['lat'];
            $lng_j = $polygon[$j]['lng'];

            $intersect = (($lng_i > $lng) != ($lng_j > $lng)) &&
                ($lat < ($lat_j - $lat_i) * ($lng - $lng_i) / (($lng_j - $lng_i) ?: 1e-9) + $lat_i);

            if ($intersect) {
                $inside = !$inside;
            }

            $j = $i;
        }

        return $inside;
    }

    public function subscriptionPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|integer',
            'order_id'        => 'required',
            'plan_amount'     => 'nullable|numeric',
            'transaction_id' => 'required',
            'custom_days'     => 'nullable',
            'quantity' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }

        DB::beginTransaction();

        try {
            $subscription = Subscription::findOrFail($request->subscription_id);

            // --- Calculate Days ---
            if ($request->filled('custom_days')) {
                $dayCount = (int)$request->custom_days;
                $isMonth = false;
                $walletamount = $request->plan_amount;
            } else {
                $dayCount = (int)$subscription->plan_pack;
                $walletamount = $subscription->plan_amount;
                $isMonth = true;
            }

            $start_date = Carbon::now()->addDay();  // Start tomorrow
            $end_date = $isMonth
                ? $start_date->copy()->addMonthsNoOverflow(2)
                : $start_date->copy()->addDays($dayCount)->subDay();

                $amount = round((float)$subscription->plan_amount, 2);
            $valid_date = $end_date->copy()->addDays((int)$subscription->plan_duration);
            // --- Auth User ---
            $user = $request->user();
            if (!$user) {
                return response()->json(['status' => 404, 'message' => 'User not found']);
            }

            $user = User::where('mobile_number', $user->mobile_number)->first();
            if (!$user) {
                return response()->json(['status' => 404, 'message' => 'User not found']);
            }

            // --- Check Active Subscription ---
            $existing = UserSubscription::where('user_id', $user->id)
                ->where('status', 1)
                ->first();

            $transaction_exists = Wallet::where('transaction_id', $request->transaction_id)->first();
            if ($transaction_exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction already exists!.Please create new order.',
                ], 400);
            }

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has an active subscription. Please deactivate it first.',
                ], 400);
            }

            // --- Create Subscription ---

            $user_subscription = UserSubscription::create([
                'user_id'         => $user->id,
                'subscription_id' => $subscription->id,
                'start_date'      => $start_date->format('Y-m-d'),
                'end_date'        => $end_date->format('Y-m-d'),
                'valid_date'      => $valid_date->format('Y-m-d'),
                'pack'            => $subscription->pack ?? 0,
                'quantity'        => $request->quantity ?? $subscription->quantity,
                'status'          => 1,
                'price'           => $amount  ?? 0,
                'days'            => $start_date->diffInDays($end_date) + 1,
            ]);

            // New wallet → create with all fields
            $wallet = new Wallet();
            $wallet->user_id         = $user->id;
            $wallet->balance         = $walletamount;
            $wallet->subscription_id = $user_subscription->id;
            $wallet->transaction_id  = $request->transaction_id ?? null;
            $wallet->save();

            $transaction = new Transaction();
            $transaction->user_id        = $user->id;
            $transaction->wallet_id      = $wallet->id;
            $transaction->type           = 'credit';
            $transaction->amount         = $walletamount;
            $transaction->balance_amount = $wallet->balance; // always correct new balance
            $transaction->description    = $request->description ?? 'Subscription Payment';
            $transaction->date           = date('Y-m-d');
            $transaction->save();

            // --- Update User with Active Subscription ---
            $user->subscription_id = $user_subscription->id;
            $user->save();

            $payment = Payment::where('order_id', $request['order_id'])->first();
            $payment->status = "PAID";
            $payment->other = $request->others ?? null;
            $payment->save();
            // $get_user = User::where('id', $request['user_id'])->first();

            event(new NewNotification($user->id, "Subscription Added", "  $user->name has added a new Subscription!", 2, 1));

            DB::commit();

            // --- Generate Daily Deliveries ---
            $dates = collect();
            for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
                $dates->push($date->toDateString());
            }

            foreach ($dates->chunk(50) as $chunk) {
                GenerateDailyDeliveries::dispatch(
                    $user_subscription,
                    $chunk->toArray()
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Subscription added successfully!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription.',
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

            User::where('id', $request['user_id'])->update([
                'account_holder_name' => $validated['account_details']['account_holder_name'],
                'bank_name'           => $validated['account_details']['bank_name'],
                'account_number'      => $validated['account_details']['account_number'],
                'ifsc_code'           => $validated['account_details']['ifsc_code'],
                'branch'              => $validated['account_details']['branch'],
                'upi'               => $validated['account_details']['upi_id'] ?? null,
            ]);
            $get_user = User::where('id', $request['user_id'])->first();

            event(new NewNotification($request['user_id'], "Subscription Cancelled", "$get_user->name has cancelled their subscription.", 2, 1));

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
