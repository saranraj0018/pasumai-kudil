<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Notification;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\DailyDelivery;
use App\Models\DeliveryPartner;
use App\Models\UserSubscription;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateDailyDeliveries;
use Illuminate\Support\Facades\Validator;

class UserlistController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $this->data['users'] = User::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $this->data['search'] = $search;
        return view('admin.user-list')->with($this->data);
    }

    public function userLists(Request $request)
    {
        $search = $request->input('search');
        $this->data['getuser'] = User::with('get_wallet')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $this->data['subscription_plan'] = Subscription::get();
        $this->data['search'] = $search;

        return view('admin.users.users_list')->with($this->data);
    }

    public function userProfileView(Request $request)
    {
        $this->data['user'] = User::with('get_wallet')->where('id', $request->id)->first();
        $this->data['getuserSubscription'] = UserSubscription::where(['user_id' => $request->id, 'status' => 1])->first();
        if (!$this->data['getuserSubscription']) {
            $cancelled = []; // No cancelled deliveries
        } else {
            $cancelled = DailyDelivery::where([
                'subscription_id' => $this->data['getuserSubscription']->id,
                'delivery_status' => 'cancelled'
            ])->pluck('delivery_date')->toArray();
        }

        $this->data['cancelled'] = $cancelled;
        // Convert to array of ranges {start_date,end_date}
        // if you have single-day cancellations only, start=end
        $this->data['cancelledRanges']  = array_map(function ($d) {
            return ['start_date' => $d, 'end_date' => $d];
        }, array_values(array_unique($cancelled)));
        $latestinactivesubscription = UserSubscription::with('get_subscription')->where('user_id', $request->id)
            ->where('status', 2)
            ->pluck('id');
        $this->data['previouswalletamount'] = Wallet::where('user_id', $request->id)
            ->whereIn('subscription_id', $latestinactivesubscription)
            ->pluck('balance')
            ->sum();
        $this->data['delivery'] = $this->data['getuserSubscription']?->id
            ? DailyDelivery::with('get_delivery_partner')
            ->where('user_id', $request->id)
            ->where('subscription_id', $this->data['getuserSubscription']->id)
            ->paginate(10)   // <<< add pagination (10 per page)
            : collect();
        $this->data['delivery_boy'] = DeliveryPartner::get();
        return view('admin.users.users_view')->with($this->data);
    }

    public function transactionHistory(Request $request)
    {
        $this->data['transactions'] = Transaction::with('get_user')->where('user_id', $request->id)
            ->paginate(15)
            ->appends($request->query());

        return view('admin.users.user_transaction_history')->with($this->data);
    }

    public function addWallet(Request $request)
    {

        $rules = [
            'user_id'      => 'required|exists:users,id',
            'type'    => 'required|in:credit,debit',
            'amount'  => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ];
        $request->validate($rules);

        DB::beginTransaction();
        try {
            $userId = $request['user_id'];

            // Check active subscription
            $subscription = UserSubscription::with('get_subscription')
                ->where('user_id', $userId)
                ->where('status', 1)
                ->latest()
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet money was not added. Please activate your subscription.',
                ], 200);
            }

            // Get or create wallet
            $wallet = Wallet::firstOrNew(['user_id' => $userId]);
            if (!$wallet->exists) {
                // New wallet creation
                $wallet->user_id = $userId;
                $wallet->balance = 0;
                $wallet->subscription_id = $subscription->id;
                $wallet->save();
            }
            $currentBalance = $wallet->balance;     // old balance
            $amount         = $request->amount;
            $type           = $request->type;

            // ---- Calculate NEW BALANCE ----
            if ($type === 'credit') {
                $newBalance = $currentBalance + $amount;
            } else { // debit
                if ($currentBalance < $amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient wallet balance for debit transaction.',
                    ], 400);
                }
                $newBalance = $currentBalance - $amount;
            }

            // ---- Update Wallet ----
            $wallet->balance = $newBalance;
            $wallet->save();

            // ---- Save Transaction ----
            $transaction = new Transaction();
            $transaction->user_id        = $userId;
            $transaction->wallet_id      = $wallet->id;
            $transaction->type           = $type;
            $transaction->amount         = $amount;
            $transaction->balance_amount = $newBalance; // always correct new balance
            $transaction->description    = $request->description ?? '';
            $transaction->date           = date('Y-m-d');
            $transaction->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Wallet updated successfully!',
                'balance' => $newBalance,
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update wallet.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function saveUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:255',
            'mobile_number'  => 'required|regex:/^[0-9]{10}$/|unique:users,mobile_number', // 10-digit validation
            'email'          => 'nullable|unique:users,email',
            'plan_id'        => 'required|integer|exists:subscriptions,id',
            'custom_days'    => 'nullable|integer|min:1',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'state'        => 'nullable|string|max:255',
            'pincode'      => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }

        DB::beginTransaction();
        try {
            $subscription = Subscription::findOrFail($request['plan_id']);

            if ($request->filled('custom_days')) {
                $daycount = (int) $request['custom_days'];
                $daymonth = false;
                $amount =  round((float)$subscription->plan_amount, 2);
            } else {
                $daycount = (int) $subscription->plan_pack;
                $daymonth = true;
            }

            $start_date = Carbon::now()->addDay();

            if ($daymonth) {
                $end_date = $start_date->copy()->addMonthsNoOverflow($daycount)->subDay();
            } else {
                $end_date = $start_date->copy()->addDays($daycount)->subDay();
            }

            if ($request->filled('custom_days')) {
                $amount =  round((float)$subscription->plan_amount, 2);
            } else {
                $totalDays = $start_date->diffInDays($end_date) + 1;
                $totalDays = $totalDays > 0 ? $totalDays : 1;
                $totalAmount = (float)$subscription->plan_amount;
                $perDay = $totalAmount / $totalDays;
                $amount = round($perDay, 2);
            }

            $validdaycount = (int) ($subscription->plan_duration ?? 0);
            $valid_date = $end_date->copy()->addDays($validdaycount);

            $start_date_formatted = $start_date->format('Y-m-d');
            $end_date_formatted   = $end_date->format('Y-m-d');
            $valid_date_formatted = $valid_date->format('Y-m-d');

            $startDate = Carbon::parse($start_date_formatted);
            $endDate = Carbon::parse($end_date_formatted);

            $image = null;
            if ($request->hasFile('image')) {
                $img_name = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->image->storeAs('users/', $img_name, 'public');
                $image = 'users/' . $img_name;
            }

            $user = User::where('mobile_number', $request['mobile_number'])->first();

            if (!$user) {
                $user = new User();
                $user->name          = $request['name'];
                $user->mobile_number = $request['mobile_number'];
                $user->email         = $request['email'] ?? null;
                $user->image         = $image;
                $user->city          = $request['city'];
                $user->state         = $request['state'];
                $user->pincode       = $request['pincode'];
                $user->latitude      = $request['latitude'] ?? '';
                $user->longitude     = $request['longitude'] ?? '';
                $user->address       = $request['address'] ?? '';
                $user->prefix        = $request['prefix'] ?? '';
                $user->save();
            }

            $existing_subscription = UserSubscription::where(['user_id' => $user->id])->first();

            if ($existing_subscription && $existing_subscription->status == 1) {
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
            $user_subscription->quantity        = $subscription->quantity ?? 0;
            $user_subscription->price           = $amount;
            $user_subscription->status          = 1;
            $user_subscription->days            = $startDate->diffInDays($endDate) + 1;
            $user_subscription->save();

            User::where('id', $user->id)->update(['subscription_id' => $user_subscription->id]);

            DB::commit();
            $start_date = Carbon::parse($user_subscription->start_date);
            $end_date   = Carbon::parse($user_subscription->end_date);

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
                'message' => 'User added successfully!',
                'user'    => $user->load('subscriptions'),
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

    public function getCustomSubscription(Request $request)
    {
        if ($request->ajax()) {
            if ($request->get_custom_subscription) {
                $getsubs = Subscription::where('id', $request->subs_id)->first();

                return response()->json([
                    'success' => true,
                    'subs' => $getsubs
                ], 200);
            }
        }
    }

    public function addUserAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_number' => 'required',
            'confirm_account_number'  => 'required',
            'bank_name' => 'nullable|string',
            'ifsc_code' => 'required',
            'account_holder_name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }

        try {
            User::where('id', $request['id'])->update([
                'account_number' => $request['account_number'],
                'bank_name' => $request['bank_name'],
                'ifsc_code' => $request['ifsc_code'],
                'account_holder_name' => $request['account_holder_name'],
                'upi' => $request['upi'],
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Account details added successfully!',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add Account details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancelSubscription(Request $request)
    {
        try {
            $exist_check = UserSubscription::where(['user_id' => $request['user_id'], 'status' => 1])->first();
            if (!$exist_check) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found for this user.'
                ], 404);
            }

            $update_status = UserSubscription::where('user_id', $request['user_id'])->update([
                'status'   => $request['status'],
                'description' => $request['description']
            ]);
            $user = User::where('id', $request['user_id'])->first();

            if ($user && $user->fcm_token) {

                $notification = new Notification();
                $notification->user_id = $user->id;
                $notification->title = 'Subscription Cancelled';
                $notification->description = 'Your subscription has been cancelled successfully.';
                $notification->type = 1;
                $notification->role = 2;
                $notification->save();

                $this->firebase->sendNotification(
                    $user->fcm_token,
                    'Subscription Cancelled',
                    'Your subscription has been cancelled successfully.'
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Subscription',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function modifySubscription(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id'     => 'required|integer',
                'start_date'  => 'required|date|before_or_equal:end_date',
                'end_date'    => 'required|date',
                'description' => 'nullable|string',
            ]);
            $userId = $validated['user_id'];
            $subscription = UserSubscription::where('user_id', $userId)
                ->where('status', 1)
                ->first();
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active subscription not found.'
                ]);
            }
            $subQty      = (int)$subscription->quantity;
            $unitPrice   = $subscription->price;
            $subPack     = $subscription->pack;
            if ($subQty <= 0) {
                return response()->json(['success' => false, 'message' => 'Invalid subscription quantity']);
            }

            $start = Carbon::parse($validated['start_date']);
            $end   = Carbon::parse($validated['end_date']);

            $changeDays = [];
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $changeDays[] = $date->format('Y-m-d');
            }

            $oldEnd   = Carbon::parse($subscription->end_date);
            $validDate = Carbon::parse($subscription->valid_date);
            // Fetch all affected orders
            $orders = DailyDelivery::where('user_id', $userId)
                ->where('subscription_id', $subscription->id)
                ->whereIn('delivery_date', $changeDays)
                ->get();
            if ($orders->count() != count($changeDays)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Some selected dates do not exist in delivery schedule'
                ]);
            }
            // Total cancelled quantity
            $totalCancelQty = (int)$orders->sum('quantity');

            if ($totalCancelQty <= 0) {
                return response()->json(['success' => false, 'message' => 'No quantity available to cancel']);
            }
            // Calculate extend days
            $extendDays = (int) ceil($totalCancelQty / $subQty);
            $newEnd     = $oldEnd->copy()->addDays($extendDays);

            DailyDelivery::where('user_id', $userId)
                ->where('subscription_id', $subscription->id)
                ->whereIn('delivery_date', $changeDays)
                ->update(['delivery_status' => 'cancelled', 'modify' => 2]);
            $cancelledDatesText = implode(', ', $changeDays);

            // SEND NOTIFICATION TO USER
            $user = User::find($userId);

            if ($user && $user->fcm_token) {
                // Save Notification in DB
                $notify = new Notification();
                $notify->user_id     = $user->id;
                $notify->title       = 'Delivery Cancelled';
                $notify->description = "Your deliveries scheduled on these dates have been cancelled: $cancelledDatesText.";
                $notify->type        = 3;
                $notify->role        = 2;
                $notify->save();

                // Send Firebase Notification
                $this->firebase->sendNotification(
                    $user->fcm_token,
                    'Delivery Cancelled',
                    "Your deliveries on these dates were cancelled: $cancelledDatesText."
                );
            }

            // Create future pending deliveries
            $qtyPending = $totalCancelQty;
            $deliveryId = $orders->first()->delivery_id ?? null;

            if ($newEnd <= $validDate) {
                for ($i = 1; $i <= $extendDays; $i++) {
                    $qtyToday = min($subQty, $qtyPending);
                    $qtyPending -= $qtyToday;
                    $daily_delivery = new DailyDelivery();
                    $daily_delivery->user_id         = $userId;
                    $daily_delivery->subscription_id = $subscription->id;
                    $daily_delivery->delivery_id     = $deliveryId;
                    $daily_delivery->delivery_date   = $oldEnd->copy()->addDays($i)->format('Y-m-d');
                    $daily_delivery->delivery_status = 'pending';
                    $daily_delivery->quantity        = $qtyToday;
                    $daily_delivery->pack       = $subPack;
                    $daily_delivery->amount     =  round($qtyToday * $unitPrice, 2);
                    $daily_delivery->save();
                }

                // Update subscription end date + description
                $subscription->update([
                    'end_date'       => $newEnd->format('Y-m-d'),
                    'description'    => $validated['description'],
                ]);

                return response()->json([
                    'success'        => true,
                    'message'       => 'Subscription updated successfully!',
                    'extended_days' => $extendDays,
                    'new_end_date'  => $newEnd->format('Y-m-d'),
                ]);
            } else {
                return response()->json([
                    'success'        => false,
                    'message'       => '"Order Cancelled Successfully, but the valid date is less than or equal to the current date, so it was not extended."',
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subscription',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function removePreviousWallet(Request $request)
    {
        $userId = $request->id;
        $inactiveSubscriptionIds = UserSubscription::where('user_id', $userId)
            ->where('status', 2)
            ->pluck('id');
        if ($inactiveSubscriptionIds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No inactive subscriptions found.',
            ]);
        }
        $wallets = Wallet::where('user_id', $userId)
            ->whereIn('subscription_id', $inactiveSubscriptionIds)
            ->get();
        if ($wallets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No wallets found for inactive subscriptions.',
            ]);
        }
        $walletIds = $wallets->pluck('id');
        Wallet::whereIn('id', $walletIds)->update(['balance' => 0]);

        foreach ($wallets as $wallet) {
            $transaction = new Transaction();
            $transaction->user_id        = $userId;
            $transaction->wallet_id      = $wallet->id;
            $transaction->type           = 'debit';
            $transaction->amount         = 0;
            $transaction->balance_amount = 0;
            $transaction->description    = "Previous wallet amount is deducted.";
            $transaction->date           = date('Y-m-d');
            $transaction->save();
        }
        $user = User::where('id', $userId)->first();
        if ($user->fcm_token) {

            $notification = new Notification();
            $notification->user_id = $user->id;
            $notification->title = 'Previous Wallet Amount Refund';
            $notification->description = "Your Previous Wallet Amount has been Refunded sucessfully!";
            $notification->type = 1;
            $notification->role = 2;
            $notification->save();

            $this->firebase->sendNotification(
                $user->fcm_token,
                'Previous Wallet Amount Refund',
                "Your Previous Wallet Amount has been Refunded sucessfully!",
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Wallet amount removed successfully.',
        ]);
    }

    public function revokeSubscriptionDay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sub_id'     => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success'  => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }

        $order = DailyDelivery::find($request['sub_id']);
        $userId = $request['user_id'];
        $subscription = UserSubscription::where('id', $request['subscription_id'])->first();
        DB::beginTransaction();

        $revokeDate = Carbon::parse($order->delivery_date);

        // Restore the cancelled order
        $order->update([
            'delivery_status' => 'pending',
            'modify' => 1
        ]);

        // Reverse quantity
        $qtyToReverse = (int) $order->quantity;

        // Reverse from future deliveries in descending order
        $futureDeliveries = DailyDelivery::where('user_id', $userId)
            ->where('subscription_id', $subscription->id)
            ->where('delivery_status', 'pending')
            ->where('delivery_date', '>', $revokeDate)
            ->orderBy('delivery_date', 'desc')   // newest first (last day first)
            ->get();
        $unitPrice = $subscription->price / $subscription->quantity;
        foreach ($futureDeliveries as $delivery) {
            if ($qtyToReverse <= 0) break;
            // If the row qty is fully used up → delete the row
            if ($delivery->quantity <= $qtyToReverse) {
                $qtyToReverse -= $delivery->quantity;
                $delivery->delete();
            } else {
                // Partial reverse update qty & amount
                $delivery->quantity -= $qtyToReverse;
                $delivery->amount = round($delivery->quantity * $unitPrice, 2);
                $delivery->save();
                $qtyToReverse = 0;
            }
        }
        // Remove entry from cancelled_date JSON array
        $cancelled = $subscription->cancelled_date
            ? json_decode($subscription->cancelled_date, true)
            : [];
        foreach ($cancelled as $key => $c) {
            if ($c['order_id'] == $order->id && $c['start_date'] == $revokeDate->format('Y-m-d')) {
                unset($cancelled[$key]);
            }
        }
        $cancelled = array_values($cancelled);
        // Update subscription end_date using last remaining delivery
        $lastDelivery = DailyDelivery::where('user_id', $userId)
            ->where('subscription_id', $subscription->id)
            ->where('delivery_status', '!=', 'cancelled')
            ->latest('delivery_date')
            ->first();
        $subscription->update([
            'cancelled_date' => json_encode($cancelled),
            'end_date'       => $lastDelivery ? $lastDelivery->delivery_date : $subscription->start_date
        ]);
        DB::commit();
        $user = User::where('id', $userId)->first();
        if ($user && $user->fcm_token) {
            $notification = new Notification();
            $notification->user_id = $user->id;
            $notification->title = 'Subscription Cancellation Revoked';
            $notification->description = "Your cancelled delivery for {$revokeDate->format('Y-m-d')} has been restored.";
            $notification->type = 1;
            $notification->role = 2;
            $notification->save();

            $this->firebase->sendNotification(
                $user->fcm_token,
                "Subscription Cancellation Revoked",
                "Your cancelled delivery for {$revokeDate->format('Y-m-d')} has been restored."
            );
        }
        return response()->json([
            'success' => true,
            'message' => 'Cancellation revoked successfully!'
        ]);
    }
}
