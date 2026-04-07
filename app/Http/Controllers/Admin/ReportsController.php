<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\DailyDelivery;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $request->validate([
            'type' => 'required|in:grocery,milk',
            'report_type' => 'required|in:detailed,summary,daily',
            'view_type' => 'required|in:view,excel,pdf',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $data = $this->getReportData($request);
        $fileName = $request->type . '_' . $request->report_type . '_' . Carbon::now()->format('Ymd_His');

        // ===== TABLE VIEW =====
        if ($request->view_type == 'view') {
            $this->data['users'] = User::get();
            $this->data['data'] = $data;
            $this->data['filters'] = $request->all();
            return view('admin.reports')->with($this->data);
        }

        if ($request->view_type == 'excel') {
            return Excel::download(
                new ReportExport($data, $request->type, $request->report_type),
                $fileName . '.xlsx'
            );
        }

        if ($request->view_type == 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf', [
                'data' => $data,
                'filters' => $request->all()
            ])->setPaper('A4', 'landscape');
            return $pdf->download($fileName . '.pdf');
        }
    }

    private function getReportData($request)
    {
        $from = $request->from_date;
        $to = $request->to_date;
        $user = $request->user_id;
        $type = $request->type;

        if ($type == 'grocery') {
            return OrderDetail::with('order.user', 'product')
                ->when(
                    $user,
                    fn($q) =>
                    $q->whereHas('order', fn($q2) => $q2->where('user_id', $user))
                )
                ->when(
                    $from && $to,
                    fn($q) =>
                    $q->whereHas(
                        'order',
                        fn($q2) =>
                        $q2->whereBetween('created_at', [$from, $to])
                    )
                )
                ->get();
        }

        // ===== MILK =====
        return DailyDelivery::with('get_user', 'get_user_subscription.get_subscription')
            ->when($user, fn($q) => $q->where('user_id', $user))
            ->when(
                $from && $to,
                fn($q) =>
                $q->whereBetween('delivery_date', [$from, $to])
            )
            ->get();
    }
}
