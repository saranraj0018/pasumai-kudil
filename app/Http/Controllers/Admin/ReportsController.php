<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GenericTableExport;
use App\Http\Controllers\Controller;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    const ORDER_DELIVERED_STATUS = 4;

    // A product with no sales in the period is Non Moving; 1-5 units sold is
    // Slow Moving; more than 20 units sold is Fast Moving.
    const SLOW_MOVING_MAX_QTY = 5;
    const FAST_MOVING_MIN_QTY = 20;

    public function index()
    {
        return view('admin.reports');
    }

    public function export(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:slow_moving,fast_moving,non_moving,day_wise_profit,item_wise_profit',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'view_type' => 'required|in:view,excel,pdf',
        ]);

        [$headings, $rows, $title] = match ($request->report_type) {
            'slow_moving' => $this->buildMovementReport($request, 'slow'),
            'fast_moving' => $this->buildMovementReport($request, 'fast'),
            'non_moving' => $this->buildMovementReport($request, 'non'),
            'day_wise_profit' => $this->buildDayWiseProfitReport($request),
            'item_wise_profit' => $this->buildItemWiseProfitReport($request),
        };

        return $this->renderTable($request, $headings, collect($rows), $title, $request->report_type);
    }

    /**
     * Render a pre-aggregated report as View / Excel / PDF.
     */
    private function renderTable(Request $request, array $headings, Collection $rows, string $title, string $fileNamePrefix)
    {
        if ($request->view_type == 'excel') {
            return Excel::download(
                new GenericTableExport($headings, $rows, $title),
                $fileNamePrefix . '_' . Carbon::now()->format('Ymd_His') . '.xlsx'
            );
        }

        if ($request->view_type == 'pdf') {
            $subtitle = ($request->from_date && $request->to_date)
                ? 'For the period ' . $request->from_date . ' to ' . $request->to_date
                : null;

            $pdf = Pdf::loadView('admin.reports.table_pdf', [
                'headings' => $headings,
                'rows' => $rows,
                'title' => $title,
                'subtitle' => $subtitle,
            ])->setPaper('A4', 'landscape');

            return $pdf->download($fileNamePrefix . '_' . Carbon::now()->format('Ymd_His') . '.pdf');
        }

        return view('admin.reports', [
            'table_headings' => $headings,
            'table_rows' => $rows,
            'table_title' => $title,
            'filters' => $request->all(),
        ]);
    }

    // ================= SLOW / FAST / NON MOVING ITEMS =================
    private function buildMovementReport(Request $request, string $bucket): array
    {
        $from = $request->from_date;
        $to = $request->to_date;

        $salesByProduct = OrderDetail::whereHas('order', function ($q) use ($from, $to) {
            $q->where('status', self::ORDER_DELIVERED_STATUS);
            if ($from && $to) {
                $q->whereBetween('delivered_at', [$from, $to]);
            }
        })
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->pluck('total_qty', 'product_id');

        $stockByProduct = ProductDetail::selectRaw('product_id, SUM(stock) as total_stock')
            ->groupBy('product_id')
            ->pluck('total_stock', 'product_id');

        $rows = collect();

        foreach (Product::orderBy('name')->get(['id', 'name']) as $product) {
            $saleQty = (int) ($salesByProduct[$product->id] ?? 0);

            $matches = match ($bucket) {
                'non' => $saleQty === 0,
                'slow' => $saleQty >= 1 && $saleQty <= self::SLOW_MOVING_MAX_QTY,
                'fast' => $saleQty > self::FAST_MOVING_MIN_QTY,
            };

            if (!$matches) {
                continue;
            }

            $rows->push([
                $product->id,
                $product->name,
                $saleQty,
                (int) ($stockByProduct[$product->id] ?? 0),
            ]);
        }

        if ($bucket === 'slow') {
            $rows = $rows->sortBy(fn($row) => $row[2])->values();
        } elseif ($bucket === 'fast') {
            $rows = $rows->sortByDesc(fn($row) => $row[2])->values();
        }

        $titles = [
            'slow' => 'Slow Moving Item Report',
            'fast' => 'Fast Moving Item Report',
            'non' => 'Non Moving Item Report',
        ];

        return [
            ['Product Code', 'Product Name', 'Sale Quantity', 'Remaining Quantity'],
            $rows,
            $titles[$bucket],
        ];
    }

    // ================= DAY WISE PROFIT =================
    private function buildDayWiseProfitReport(Request $request): array
    {
        $orderDetails = $this->getDeliveredOrderDetails($request->from_date, $request->to_date);

        $group = $orderDetails->groupBy(
            fn($item) => optional($item->order)->delivered_at
                ? Carbon::parse($item->order->delivered_at)->format('Y-m-d')
                : 'Unknown'
        );

        $rows = $group->map(function ($items, $date) {
            $sales = $items->sum(fn($i) => $i->net_amount ?? 0);
            $cost = $items->sum(fn($i) => $this->getProductCost($i) * ($i->quantity ?? 0));
            $profit = $sales - $cost;

            return [
                $date,
                $items->sum('quantity'),
                number_format($cost, 2),
                number_format($sales, 2),
                number_format($profit, 2),
                $sales > 0 ? number_format($profit / $sales * 100, 2) : '0.00',
            ];
        })->sortKeys()->values();

        return [
            ['Sale Date', 'Sales Quantity', 'Landing Cost Amount', 'Sales Amount', 'Profit Amount', 'Profit %'],
            $rows,
            'Day Wise Profit Report',
        ];
    }

    // ================= ITEM WISE PROFIT =================
    private function buildItemWiseProfitReport(Request $request): array
    {
        $orderDetails = $this->getDeliveredOrderDetails($request->from_date, $request->to_date);

        $group = $orderDetails->groupBy('product_id');

        $rows = $group->map(function ($items, $productId) {
            $sales = $items->sum(fn($i) => $i->net_amount ?? 0);
            $cost = $items->sum(fn($i) => $this->getProductCost($i) * ($i->quantity ?? 0));
            $profit = $sales - $cost;

            return [
                $productId,
                optional($items->first()->product)->name ?? '',
                $items->sum('quantity'),
                number_format($cost, 2),
                number_format($sales, 2),
                number_format($profit, 2),
                $sales > 0 ? number_format($profit / $sales * 100, 2) : '0.00',
            ];
        })->values();

        return [
            ['Code', 'Description', 'Sales Qty', 'Landing Cost (Purchase Price)', 'Sales Amount', 'Profit Amount', 'Profit %'],
            $rows,
            'Item Wise Profit Report',
        ];
    }

    private function getProductCost($orderDetail)
    {
        return $orderDetail->variants->purchase_price
            ?? optional($orderDetail->product->details)->purchase_price
            ?? 0;
    }

    private function getDeliveredOrderDetails($from, $to)
    {
        return OrderDetail::with(['order', 'product', 'variants', 'product.details'])
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('status', self::ORDER_DELIVERED_STATUS);
                if ($from && $to) {
                    $q->whereBetween('delivered_at', [$from, $to]);
                }
            })
            ->get();
    }
}
