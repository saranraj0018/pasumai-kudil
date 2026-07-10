<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyDelivery;
use App\Models\DeliveryPartner;
use App\Models\Hub;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryListController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index(Request $request)
    {

        $query = DailyDelivery::with(['get_user', 'get_delivery_partner', 'get_user_subscription', 'get_order'])
            ->whereHas('get_user_subscription', function ($q) {
                $q->where('status', 1);
            });
        if ($request->city) {
            $hub = Hub::find($request->city);
            if ($hub && $hub->get_city && $hub->get_city->coordinates) {
                $polygon = json_decode($hub->get_city->coordinates, true); // Array of ['lat'=>..,'lng'=>..]

                // Filter using a closure to check if user is inside polygon
                $query->whereHas('get_user', function ($q) use ($polygon) {
                    $q->where(function ($userQuery) use ($polygon) {
                        $users = User::all();
                        $userIds = [];

                        foreach ($users as $user) {
                            if ($user->latitude && $user->longitude) {
                                if ($this->isPointInPolygon($user->latitude, $user->longitude, $polygon)) {
                                    $userIds[] = $user->id;
                                }
                            }
                        }
                        $userQuery->whereIn('id', $userIds);
                    });
                });
            }
        }

        // Delivery Date
        if ($request->delivery_date) {
            $query->whereDate('delivery_date', $request->delivery_date);
        }

        // Month
        if ($request->delivery_month) {
            $query->whereMonth('delivery_date', date('m', strtotime($request->delivery_month)))
                ->whereYear('delivery_date', date('Y', strtotime($request->delivery_month)));
        }

        // Username
        if ($request->username) {
            $query->whereHas('get_user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->username . '%');
            });
        }

        // Status
        if ($request->status) {
            $query->where('delivery_status', $request->status);
        }

        // Delivery Boy
        if ($request->delivery_boy) {
            $query->whereHas('get_delivery_partner', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->delivery_boy . '%');
            });
        }

        $this->data['daily_delivery']  = $query->orderBy('id', 'asc')->paginate(10)->withQueryString();
        $this->data['delivery_boy'] = DeliveryPartner::get();
        $this->data['users'] = User::with('subscriptions', 'subscriptions')
            ->whereHas('subscriptions', function ($q) {
                $q->whereIn('status', [1, 2]);
            })
            ->get();
        $this->data['hub_list'] = Hub::with('get_city')->where('type', 2)->orderBy('created_at', 'desc')->get();
        return view('admin.delivery_list.list')->with($this->data);
    }

    public function statusSave(Request $request)
    {
        $rules = [
            'status'  => 'required',
            'delivery_boy' => 'nullable'
        ];

        // if (empty($request['delivery_id']) && !$request->has('existing_image')) {
        //     $rules['image'] = 'required|image|mimes:jpeg,png,jpg';
        // } elseif ($request->hasFile('image')) {
        //     $rules['image'] = 'image|mimes:jpeg,png,jpg';
        // }

        $request->validate($rules);

        try {
            $delivery = DailyDelivery::findOrFail($request->delivery_id);
            $wallet   = Wallet::where(['user_id' => $delivery->user_id, 'subscription_id' => $delivery->subscription_id])->first();

            // ---- IMAGE HANDLING ----
            if ($request->hasFile('image')) {
                $img_name = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->image->storeAs('products/', $img_name, 'public');
                $image = 'delivery/' . $img_name;
            } else {
                $image = $request->existing_image ?? null;
            }

            // ---------- WALLET CHECK ----------
            $deductionAmount = $delivery->amount;
            $remaining = $wallet->balance - $deductionAmount;

            if ($remaining < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient wallet balance. Please recharge wallet.'
                ]);
            }

            // ---------- CHECK IF ALREADY DEDUCTED ----------
            $exists = Transaction::where('user_id', $delivery->user_id)
                ->where('date', $delivery->delivery_date)
                ->exists();

            if (!$exists) {
                // Deduct wallet
                $transaction = new Transaction();
                $transaction->user_id        = $delivery->user_id;
                $transaction->wallet_id      = $wallet->id;
                $transaction->type           = 'debit';
                $transaction->amount         = $deductionAmount;
                $transaction->balance_amount = $remaining; // always correct new balance
                $transaction->description    = "Daily milk amount ₹{$deductionAmount} deducted.";
                $transaction->date           = date('Y-m-d');
                $transaction->save();

                $wallet->update([
                    'balance' => $remaining
                ]);
            }

            // ---------- NOW ALLOW STATUS UPDATE ----------
            $delivery->update([
                'delivery_id' => $request->delivery_boy ?? '',
                'delivery_status' => $request->status,
                'image' => $image,
            ]);

            $user = User::where('id', $delivery->user_id)->first();

            if ($user->fcm_token) {
                $notification = new Notification();
                $notification->user_id = $user->id;
                $notification->title = 'Delivery Status Changed';
                $notification->description = "Your delivery has been $request->status sucessfully!";
                $notification->type = 2;
                $notification->role = 2;
                $notification->save();
                $this->firebase->sendNotification(
                    $user->fcm_token,
                    'Delivery Status Changed',
                    "Your delivery has been $request->status sucessfully!",
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Delivery status updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save delivery status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function overallSave(Request $request)
    {
        $request->validate([
            'from_date'    => 'required|date',
            'to_date'      => 'required|date|after_or_equal:from_date',
            'delivery_boy' => 'nullable',
            'users'        => 'required',
        ]);

        DB::beginTransaction();

        try {

            $image = null;

            $query = DailyDelivery::whereBetween('delivery_date', [
                $request->from_date,
                $request->to_date
            ]);

            if ($request->users !== 'all') {

                $userIds = is_array($request->users)
                    ? $request->users
                    : explode(',', $request->users);

                $query->whereIn('user_id', $userIds);
            }

            $deliveries = $query->get();

            if ($deliveries->isEmpty()) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No deliveries found for the selected date range.'
                ]);
            }

            $updated = 0;
            $skipped = [];

            foreach ($deliveries as $delivery) {

                // Wallet Deduction
                if ($request->status == 'delivered') {

                    $wallet = Wallet::where('user_id', $delivery->user_id)
                        ->where('subscription_id', $delivery->subscription_id)
                        ->first();

                    if (!$wallet) {
                        continue;
                    }

                    $alreadyDeducted = Transaction::where('user_id', $delivery->user_id)
                        ->where('subscription_id', $delivery->subscription_id)
                        ->whereDate('date', $delivery->delivery_date)
                        ->exists();

                    if (!$alreadyDeducted) {

                        $remaining = $wallet->balance - $delivery->amount;

                        if ($remaining < 0) {

                            $skipped[] = $delivery->user_id;
                            continue;
                        }

                        Transaction::create([
                            'user_id'         => $delivery->user_id,
                            'wallet_id'       => $wallet->id,
                            'subscription_id' => $delivery->subscription_id,
                            'type'            => 'debit',
                            'amount'          => $delivery->amount,
                            'balance_amount'  => $remaining,
                            'description'     => 'Daily milk amount deducted.',
                            'date'            => $delivery->delivery_date,
                        ]);

                        $wallet->update([
                            'balance' => $remaining
                        ]);
                    }
                }

                // Update Delivery
                $delivery->update([
                    'delivery_id'     => $request->delivery_boy ?: $delivery->delivery_id,
                    'delivery_status' => 'delivered',
                    'image'           => $image ?? $delivery->image,
                ]);

                $updated++;

                // Send Notification
                $user = User::find($delivery->user_id);

                if ($user) {

                    $notification = new Notification();
                    $notification->user_id = $user->id;
                    $notification->title = 'Delivery Status Changed';
                    $notification->description = "Your delivery has been delivered sucessfully!";
                    $notification->type = 2;
                    $notification->role = 2;
                    $notification->save();

                    if (!empty($user->fcm_token)) {
                        try {
                            $this->firebase->sendNotification(
                                $user->fcm_token,
                                'Delivery Status Changed',
                                "Your delivery status has been changed to {$request->status} successfully."
                            );
                        } catch (\Exception $e) {
                            Log::error('Firebase Notification Error', [
                                'user_id' => $user->id,
                                'token'   => $user->fcm_token,
                                'error'   => $e->getMessage(),
                            ]);
                            // Optional:
                            // $user->update(['fcm_token' => null]);
                        }
                    }
                }
            }

            DB::commit();

            $message = "{$updated} delivery record(s) updated successfully.";

            if (!empty($skipped)) {
                $message .= " " . count($skipped) . " user(s) were skipped due to insufficient wallet balance.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Overall Delivery Update Error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update delivery status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function isPointInPolygon($lat, $lng, $polygon)
    {
        $inside = false;
        $j = count($polygon) - 1;

        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i]['lat'];
            $yi = $polygon[$i]['lng'];
            $xj = $polygon[$j]['lat'];
            $yj = $polygon[$j]['lng'];

            $intersect = (($yi > $lng) != ($yj > $lng)) &&
                ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi + 0.0000001) + $xi);
            if ($intersect) $inside = !$inside;
            $j = $i;
        }

        return $inside;
    }
}
