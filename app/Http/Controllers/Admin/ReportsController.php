<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductReportExport;
use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\DailyDelivery;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    const ORDER_DELIVERED_STATUS = 4;

    public function index(Request $request)
    {
        $this->data['users'] = User::get();
        $this->data['products'] = Product::orderBy('name')->get();
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
            $this->data['products'] = Product::orderBy('name')->get();
            $this->data['data'] = $data;
            $this->data['filters'] = $request->all();
            $this->data['active_tab'] = 'sales';
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

    // ================= PRODUCT PERFORMANCE REPORT =================
    public function productExport(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'view_type' => 'required|in:view,excel,pdf',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $isAllProducts = $request->product_id === 'all';

        if (!$isAllProducts) {
            $request->validate(['product_id' => 'exists:products,id']);
        }

        $product = $isAllProducts ? null : Product::with('details')->findOrFail($request->product_id);
        $orderDetails = $this->getProductReportData($request, $isAllProducts);
        $summary = $this->summariseProductReport($orderDetails);
        $daily = $this->groupProductReportByDate($orderDetails);
        $byProduct = $isAllProducts ? $this->groupProductReportByProduct($orderDetails) : null;
        $fileName = 'product_report_' . ($isAllProducts ? 'all' : $product->id) . '_' . Carbon::now()->format('Ymd_His');

        if ($request->view_type == 'view') {
            $this->data['users'] = User::get();
            $this->data['products'] = Product::orderBy('name')->get();
            $this->data['product'] = $product;
            $this->data['is_all_products'] = $isAllProducts;
            $this->data['summary'] = $summary;
            $this->data['daily'] = $daily;
            $this->data['by_product'] = $byProduct;
            $this->data['filters'] = $request->all();
            $this->data['active_tab'] = 'product';
            return view('admin.reports')->with($this->data);
        }

        if ($request->view_type == 'excel') {
            return Excel::download(
                new ProductReportExport($product, $summary, $daily, $byProduct),
                $fileName . '.xlsx'
            );
        }

        if ($request->view_type == 'pdf') {
            $pdf = Pdf::loadView('admin.reports.product_pdf', [
                'product' => $product,
                'is_all_products' => $isAllProducts,
                'summary' => $summary,
                'daily' => $daily,
                'by_product' => $byProduct,
                'filters' => $request->all(),
            ])->setPaper('A4', 'landscape');
            return $pdf->download($fileName . '.pdf');
        }
    }

    /**
     * Delivered order-lines for the selected product (or all products) within the date range.
     */
    private function getProductReportData($request, $isAllProducts = false)
    {
        $from = $request->from_date;
        $to = $request->to_date;

        return OrderDetail::with(['order', 'variants', 'product.details'])
            ->when(!$isAllProducts, fn($q) => $q->where('product_id', $request->product_id))
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('status', self::ORDER_DELIVERED_STATUS);
                if ($from && $to) {
                    $q->whereBetween('delivered_at', [$from, $to]);
                }
            })
            ->get();
    }

    private function groupProductReportByProduct($orderDetails)
    {
        $rows = collect();
        $group = $orderDetails->groupBy('product_id');

        foreach ($group as $items) {
            $quantity = $items->sum('quantity');
            $sales = $items->sum(fn($i) => $i->net_amount ?? 0);
            $cost = $items->sum(fn($i) => $this->getProductCost($i) * ($i->quantity ?? 0));

            $rows->push([
                'name' => optional($items->first()->product)->name ?? 'Unknown',
                'orders' => $items->pluck('order_id')->unique()->count(),
                'quantity' => $quantity,
                'sales' => $sales,
                'cost' => $cost,
                'profit' => $sales - $cost,
            ]);
        }

        return $rows->sortByDesc('sales')->values();
    }

    private function summariseProductReport($orderDetails)
    {
        $totalQuantity = 0;
        $totalSales = 0;
        $totalCost = 0;

        foreach ($orderDetails as $item) {
            $quantity = $item->quantity ?? 0;
            $totalQuantity += $quantity;
            $totalSales += $item->net_amount ?? 0;
            $totalCost += $this->getProductCost($item) * $quantity;
        }

        return [
            'total_orders' => $orderDetails->pluck('order_id')->unique()->count(),
            'total_quantity' => $totalQuantity,
            'total_sales' => $totalSales,
            'total_cost' => $totalCost,
            'total_profit' => $totalSales - $totalCost,
        ];
    }

    private function groupProductReportByDate($orderDetails)
    {
        $rows = collect();
        $group = $orderDetails->groupBy(
            fn($item) => $item->order->delivered_at
                ? Carbon::parse($item->order->delivered_at)->format('Y-m-d')
                : 'Unknown'
        );

        foreach ($group as $date => $items) {
            $quantity = $items->sum('quantity');
            $sales = $items->sum(fn($i) => $i->net_amount ?? 0);
            $cost = $items->sum(fn($i) => $this->getProductCost($i) * ($i->quantity ?? 0));

            $rows->push([
                'date' => $date,
                'orders' => $items->pluck('order_id')->unique()->count(),
                'quantity' => $quantity,
                'sales' => $sales,
                'cost' => $cost,
                'profit' => $sales - $cost,
            ]);
        }

        return $rows->sortBy('date')->values();
    }

    private function getProductCost($orderDetail)
    {
        return $orderDetail->variants->purchase_price
            ?? optional($orderDetail->product->details)->purchase_price
            ?? 0;
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
