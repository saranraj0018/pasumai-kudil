<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyDelivery;
use App\Models\DeliveryTrack;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TodayDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $this->data['today_delivery'] = DailyDelivery::with('get_user_subscription', 'get_delivery_track')->select(
            'delivery_id',
            DB::raw('COUNT(id) as total_scheduled'),
            DB::raw('SUM(CASE WHEN delivery_status = "pending" THEN 1 ELSE 0 END) as total_pending'),
            DB::raw('SUM(CASE WHEN delivery_status = "delivered" THEN 1 ELSE 0 END) as total_delivered'),
            DB::raw('SUM(quantity) as total_quantity')
        )  ->whereHas('get_user_subscription', function ($query) {
            $query->where('status', 1);
            })
            ->whereDate('delivery_date', date('Y-m-d'))
            ->groupBy('delivery_id')
            ->with('get_delivery_partner') // your relationship
            ->orderBy('delivery_id', 'asc')
            ->paginate(10);

        return view('admin.today_delivery.today_delivery_list')->with($this->data);
    }

    public function stockSave(Request $request)
    {
        $rules = [
            'extra_quantity'  => 'nullable',
            'damage_quantity' => 'nullable',
            'returned_quantity' => 'nullable'
        ];
        $request->validate($rules);
        try {
            $currentDate = Carbon::now()->toDateString();
            // ---------- CHECK IF ALREADY DEDUCTED ----------
            $exists = DeliveryTrack::where('delivery_partner_id', $request->delivery_partner_id)
                ->where('delivery_date', $currentDate)
                ->exists();

            if (!$exists) {
                $delivery_track = new DeliveryTrack();
                $delivery_track->delivery_partner_id  = $request->delivery_partner_id;
                $delivery_track->extra_quantity     = $request->extra_quantity;
                $delivery_track->damage_quantity    = $request->damage_quantity;
                $delivery_track->returned_quantity  = $request->returned_quantity;
                $delivery_track->delivery_date =  $currentDate;
                $delivery_track->save();
            }else{
                $update = DeliveryTrack::where(['delivery_partner_id' => $request->delivery_partner_id, 'delivery_date' =>  $currentDate ])->update([
                'extra_quantity'    => $request->extra_quantity,
                'damage_quantity'   => $request->damage_quantity,
               'returned_quantity'  => $request->returned_quantity,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Delivery Track Updated Successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save delivery track',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
