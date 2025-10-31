<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateDailyDeliveries;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Wallet;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class UserlistController extends Controller
{
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
        return view('admin.users.users_view')->with($this->data);
    }

    public function transactionHistory(Request $request)
    {
        $this->data['transactions'] = Transaction::with('get_user')->where('user_id', $request->id)->paginate();

        return view('admin.users.user_transaction_history')->with($this->data);
    }

    public function addWallet(Request $request)
    {
        $rules = [
            'type'   => 'required',
            'amount'    => 'required',
        ];

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $exist_wallet = Wallet::where('user_id', $request['id'])->first();
            if (!empty($exist_wallet)) {
                if ($request['type'] == 'credit') {
                    $amount = $exist_wallet->balance + $request['amount'];
                } else {
                    $amount = $exist_wallet->balance - $request['amount'];
                }
                $update = Wallet::where('user_id', $request['id'])->update([
                    'balance' => $amount
                ]);
            } else {
                $wallet = new Wallet();
                $wallet->user_id   = $request['id'];
                $wallet->balance   = $request['amount'];
                $wallet->save();
            }

            $transaction = new Transaction();
            $transaction->user_id  = $request['id'];
            $transaction->type = $request['type'];
            $transaction->amount  = $request['amount'] ?? 0;
            $transaction->description = $request['description'] ?? '';
            $transaction->date = date('Y-m-d');
            $transaction->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Wallet added sucessfully!',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create wallet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function saveUser(Request $request)
    {
        //  $request->validate([
        //     'name'           => 'required|string|max:255',
        //     'mobile_number'  => ['required', 'regex:/^[0-9]{10}$/'], // 10-digit validation
        //     'email'          => 'nullable|email',
        //     'plan_id'        => 'required|integer|exists:subscriptions,id',
        //     'custom_days'    => 'nullable|integer|min:1',
        //     'image'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        // ]);

        DB::beginTransaction();
        try {
            $subscription = Subscription::findOrFail($request['plan_id']);

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

            $image = null;
            if ($request->hasFile('image')) {
                $img_name = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->image->storeAs('users/', $img_name, 'public');
                $image = 'users/' . $img_name;
            }

            $user = User::where('email', $request['email'])->first();

            if (!$user) {
                $user = new User();
                $user->name          = $request['name'];
                $user->mobile_number = $request['mobile_number'];
                $user->email         = $request['email'] ?? null;
                $user->image         = $image;
                $user->city          = $request['city'];
                $user->latitude      = $request['latitude'] ?? '';
                $user->longitude     = $request['longitude'] ?? '';
                $user->address       = $request['address'] ?? '';
                $user->save();
            }

            $existing_subscription = UserSubscription::where('user_id', $user->id)->first();

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
            $user_subscription->status        = 1;
            $user_subscription->save();

            $update = User::where('id', $user->id)->update([
                'subscription_id' => $user_subscription->id
            ]);

            DB::commit();
            $start = Carbon::parse($user_subscription->start_date);
            $end   = Carbon::parse($user_subscription->end_date);
            $dates = collect();

            while ($start->lte($end)) {
                $dates->push($start->toDateString());
                $start->addDay();
            }

            /** 🔹 Chunk and batch the jobs */
            $batch = Bus::batch([])->name("DailyDeliveries for User {$user->id}")->dispatch();

            foreach ($dates->chunk(50) as $chunk) {
                $jobs = $chunk->map(
                    fn($date) =>
                    new GenerateDailyDeliveries($user_subscription, [$date])
                )->all();

                $batch->add($jobs);
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
        $request->validate([
            'account_number' => 'required',
            'confirm_account_number'  => 'required',
            'bank_name' => 'nullable|string',
            'ifsc_code' => 'required',
            'account_holder_name' => 'required|string'
        ]);

        try {
            $update = User::where('id', $request['user_id'])->update([
                'account_number' => $request['account_number'],
                'bank_name' => $request['bank_name'],
                'ifsc_code' => $request['ifsc_code'],
                'account_holder_name' => $request['account_holder_name']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account details added sucessfully!',
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
            $exist_check = UserSubscription::where('user_id', $request['user_id'])->first();
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
            $userId = $request->user_id;
            $subscription = UserSubscription::where(['user_id' => $userId, 'status' => 1])->first();
            if (!$subscription) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Subscription not found.'
                ]);
            }
            // Convert to Carbon instances
            $endDate = Carbon::parse($subscription->end_date);
            $validDate = Carbon::parse($subscription->valid_date);
            $cancelStart = Carbon::parse($request->start_date);
            $cancelEnd = Carbon::parse($request->end_date);
            $cancelDays = $cancelStart->diffInDays($cancelEnd) + 1;
            $newEndDate = $endDate->copy()->addDays($cancelDays);
            $greaterthan = false;
            if ($newEndDate->greaterThan($validDate)) {
                $greaterthan = true;
                $newEndDate = $validDate;
            }
            $existingCancelled = $subscription->cancelled_date
                ? json_decode($subscription->cancelled_date, true)
                : [];
            if (!is_array($existingCancelled)) {
                $existingCancelled = [];
            }
            $existingCancelled[] = [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ];
            // Update record
            if (!$greaterthan) {
                $update_status = UserSubscription::where('user_id', $userId)->update([
                    'cancelled_date' => json_encode($existingCancelled),
                    'description' => $request->description,
                    'end_date' => $newEndDate->format('Y-m-d'),
                    'updated_at' => now(),
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Subscription date has been successfully updated!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Subscription',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
