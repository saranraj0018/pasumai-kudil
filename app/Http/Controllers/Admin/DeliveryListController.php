<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Hub;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\DailyDelivery;
use App\Models\DeliveryPartner;
use App\Services\FirebaseService;
use App\Http\Controllers\Controller;
use App\Models\Notification;

class DeliveryListController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index(Request $request)
    {
        // $this->data['daily_delivery'] = DailyDelivery::with('get_user', 'get_delivery_partner', 'get_user_subscription')
        //     ->whereHas('get_user_subscription', function ($query) {
        //         $query->where('status', 1);
        //     })
        //     ->orderBy('id', 'asc')
        //     ->paginate(10);
        $query = DailyDelivery::with(['get_user', 'get_delivery_partner', 'get_user_subscription', 'get_order'])
            ->whereHas('get_user_subscription', function ($q) {
                $q->where('status', 1);
            });
        // City polygon filter
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
        $this->data['hub_list'] = Hub::with('get_city')->where('type',2)->orderBy('created_at', 'desc')->get();
        return view('admin.delivery_list.list')->with($this->data);
    }

    public function statusSave(Request $request)
    {
        $rules = [
            'status'  => 'required',
            'delivery_boy' => 'nullable'
        ];

        if (empty($request['delivery_id']) && !$request->has('existing_image')) {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg';
        } elseif ($request->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg';
        }

        $request->validate($rules);
 
        try {
            $delivery = DailyDelivery::findOrFail($request->delivery_id);
            $wallet   = Wallet::where(['user_id' => $delivery->user_id,'subscription_id' => $delivery->subscription_id])->first();

            // ---- IMAGE HANDLING ----
            if ($request->hasFile('image')) {
                $img_name = time().'_'.$request->file('image')->getClientOriginalName();
                $request->image->storeAs('products/', $img_name, 'public');
                $image = 'delivery/'.$img_name;
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
