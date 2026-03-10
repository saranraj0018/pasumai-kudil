<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\DailyDelivery;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $this->data['users'] = User::get();
        return view('admin.reports')->with($this->data);
    }

    public function export(Request $request)
    {
        $from = $request->from_date;
        $to = $request->to_date;
        $user = $request->user_id;
        $type = $request->type;

        if ($type == 'grocery') {
            $data = OrderDetail::with('order.user', 'order', 'product')
                ->when($user, fn($q) => $q->where('user_id', $user))
                ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
                ->get();
        }

        if ($type == 'milk') {
            $data = DailyDelivery::with('get_user', 'get_user_subscription.get_subscription')
                ->when($user, fn($q) => $q->where('user_id', $user))
                ->when($from && $to, fn($q) => $q->whereBetween('delivery_date', [$from, $to]))
                ->get();
        }

        return Excel::download(new ReportExport($data, $type), 'report.xlsx');
    }
}
