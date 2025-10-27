<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TodayDeliveryController extends Controller
{
    public function index(Request $request)
    {

        $this->data['today_delivery'] = DailyDelivery::select(
            'delivery_id',
            DB::raw('COUNT(id) as total_scheduled'),
            DB::raw('SUM(CASE WHEN delivery_status = "pending" THEN 1 ELSE 0 END) as total_pending'),
            DB::raw('SUM(CASE WHEN delivery_status = "delivered" THEN 1 ELSE 0 END) as total_delivered')
        )
            ->whereDate('delivery_date', date('Y-m-d'))
            ->groupBy('delivery_id')
            ->with('get_delivery_partner') // your relationship
            ->orderBy('delivery_id', 'asc')
            ->paginate(10);

        return view('admin.today_delivery.today_delivery_list')->with($this->data);
    }
}
