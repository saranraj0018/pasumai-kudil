<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyDelivery;
use App\Models\Transaction;
use App\Models\Wallet;
use Exception;
use Illuminate\Http\Request;

class DeliveryListController extends Controller
{
    public function index(Request $request)
    {
        $this->data['daily_delivery'] = DailyDelivery::with('get_user', 'get_delivery_partner', 'get_user_subscription')
            ->whereHas('get_user_subscription', function ($query) {
                $query->where('status', 1);
            })
            ->orderBy('id', 'asc')
            ->paginate(10);

         return view('admin.delivery_list.list')->with($this->data);
    }

    public function statusSave(Request $request)
    {
        $rules = [
            'status'   => 'required',
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
                'delivery_status' => $request->status,
                'image' => $image,
            ]);

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

}
