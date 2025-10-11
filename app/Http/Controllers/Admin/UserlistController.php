<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
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

        $this->data['getuser'] = User::with('get_wallet')->paginate(5);

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
            $exist_wallet = Wallet::where('user_id',$request['user_id'])->first();
            if(!empty($exist_wallet)){ 
                if($request['type'] == 'credit'){
                  $amount = $exist_wallet->balance + $request['amount'];
                }else{
                  $amount = $exist_wallet->balance - $request['amount'];       
                }
                $update = Wallet::where('user_id',$request['user_id'])->update([
                    'balance' => $amount
                ]);
            }else{
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
}
