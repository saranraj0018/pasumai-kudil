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
        $this->data['daily_delivery'] =  DailyDelivery::with('get_user', 'get_delivery_partner')->orderBy('created_at', 'desc')
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
        } else if ($request->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg';
        }
        $request->validate($rules);
        $message = 'Delivery Status updated successfully';
        if ($request->hasFile('image')) {
            $img_name = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->image->storeAs('products/', $img_name, 'public');
            $image = 'delivery/' . $img_name;
        } elseif ($request->filled('existing_image')) {
            $image = $request->existing_image;
        }
        try {
            $get_user = DailyDelivery::where('id', $request['delivery_id'])->first();
            $get_wallet = Wallet::where('user_id', $get_user->user_id)->first();
            $amount = $get_wallet->balance - $get_user->amount;

            $exist_transaction = Transaction::where('user_id', $get_user->user_id)
                                    ->where('date', $get_user->delivery_date)
                                    ->exists();
            if(!$exist_transaction){
                $transaction = new Transaction();
                $transaction->user_id  = $get_user->user_id;;
                $transaction->type = 'debit';
                $transaction->amount  = $amount ?? 0;
                $transaction->description = 'daily milk amount is' .$amount .'deducted sussussfully!'  ?? '';
                $transaction->date = $get_user->delivery_date;
                $transaction->save();

                $wallet_update = Wallet::where('user_id', $get_user->user_id)->update([
                    'balance' => $amount
                ]);
            }

            $update = DailyDelivery::where('id', $request['delivery_id'])->update([
                 'delivery_status' => $request['status'],
                 'image' => $image,
                ]);

             return response()->json([
                'success' => true,
                'message' => $message,
                'product' => $update,
            ]);
        } catch (Exception $e) {

             return response()->json([
                'success' => false,
                'message' => 'Failed to save delivery status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
