<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Wallet;
use Carbon\Carbon;
use Exception;
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
            $exist_wallet = Wallet::where('user_id', $request['user_id'])->first();
            if (!empty($exist_wallet)) {
                if ($request['type'] == 'credit') {
                    $amount = $exist_wallet->balance + $request['amount'];
                } else {
                    $amount = $exist_wallet->balance - $request['amount'];
                }
                $update = Wallet::where('user_id', $request['user_id'])->update([
                    'balance' => $amount
                ]);
            } else {
                $wallet = new Wallet();
                $wallet->user_id   = $request['user_id'];
                $wallet->balance   = $request['amount'];
                $wallet->save();
            }

            $transaction = new Transaction();
            $transaction->user_id  = $request['user_id'];
            $transaction->type = $request['type'];
            $transaction->amount  = $request['amount'] ?? 0;
            $transaction->description = $request['description'] ?? '';
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

        $exist_user_subscription = UserSubscription::where('user_id',$user->id)->first();

        if(!empty($exist_user_subscription)){
             $user_subscription = $exist_user_subscription;
        }else{
            $user_subscription = new UserSubscription();
            $user_subscription->user_id         = $user->id;
            $user_subscription->subscription_id = $subscription->id;
            $user_subscription->start_date      = $start_date_formatted;
            $user_subscription->end_date        = $end_date_formatted;
            $user_subscription->valid_date      = $valid_date_formatted;
            $user_subscription->pack            = $subscription->plan_pack ?? 0;
            $user_subscription->quantity        = $subscription->quantity ?? 0;
            $user_subscription->save();
        }

        $update = User::where('id',$user->id)->update([
            'subscription_id' => $user_subscription->id
        ]);

        DB::commit();

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
      if($request->ajax()){
        if($request->get_custom_subscription){
            $getsubs = Subscription::where('id',$request->subs_id)->first();

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
}
