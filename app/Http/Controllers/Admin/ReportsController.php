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
            'report_type' => 'required|in:slow_moving,fast_moving,non_moving,day_wise_profit,item_wise_profit,product_stock_view,product_list_view',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'view_type' => 'required|in:view,excel,pdf',
            'columns' => 'nullable|array',
            'columns.*' => 'in:purchase_price,sale_price,regular_price,profit,stock',
        ]);

        [$headings, $rows, $title] = match ($request->report_type) {
            'slow_moving' => $this->buildMovementReport($request, 'slow'),
            'fast_moving' => $this->buildMovementReport($request, 'fast'),
            'non_moving' => $this->buildMovementReport($request, 'non'),
            'day_wise_profit' => $this->buildDayWiseProfitReport($request),
            'item_wise_profit' => $this->buildItemWiseProfitReport($request),
            'product_stock_view' => $this->buildProductStockViewReport($request),
            'product_list_view' => $this->buildProductListViewReport($request),
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
            ['Product Id', 'Product Name', 'Sale Quantity', 'Remaining Quantity'],
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

    // Optional columns shared by the two product reports below. Only the
    // columns the user ticks are added to the heading/row output.
    const PRODUCT_OPTIONAL_COLUMNS = [
        'purchase_price' => 'Purchase Price',
        'sale_price' => 'Sale Price',
        'regular_price' => 'Regular Price',
        'profit' => 'Profit',
        'stock' => 'Stock',
    ];

    // ================= PRODUCT STOCK VIEW =================
    // Stock lives on each variant row (product_details); a non-variant
    // product is simply a product with a single variant row.
    private function buildProductStockViewReport(Request $request): array
    {
        // Stock is always shown as its own column here, so the optional
        // "Stock" checkbox (meant for List View) is ignored to avoid a
        // duplicate column.
        $columns = array_diff($request->input('columns', []), ['stock']);

        $headings = ['Product Id', 'Product Name', 'Variant', 'Stock'];
        foreach (self::PRODUCT_OPTIONAL_COLUMNS as $key => $label) {
            if (in_array($key, $columns)) {
                $headings[] = $label;
            }
        }

        $rows = collect();

        $products = Product::with('variants.unit')->orderBy('name')->get();

        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $unit = optional($variant->unit)->short_name ?? optional($variant->unit)->name;
                $variantLabel = trim(rtrim(rtrim(number_format($variant->weight, 2), '0'), '.') . ' ' . $unit);

                $row = [
                    $product->id,
                    $product->name,
                    $variantLabel,
                    (int) $variant->stock,
                ];

                $this->appendOptionalColumns($row, $columns, $variant);

                $rows->push($row);
            }
        }

        return [$headings, $rows, 'Product Stock View Report'];
    }

    // ================= PRODUCT LIST VIEW =================
    // One row per variant (not an aggregated count/total). Stock is an
    // optional column here (ticked via the Columns checkboxes) rather than
    // shown by default.
    private function buildProductListViewReport(Request $request): array
    {
        $columns = $request->input('columns', []);

        $headings = ['Product Code', 'Product Name', 'Variant'];
        foreach (self::PRODUCT_OPTIONAL_COLUMNS as $key => $label) {
            if (in_array($key, $columns)) {
                $headings[] = $label;
            }
        }

        $rows = collect();

        $products = Product::with('variants.unit')->orderBy('name')->get();

        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $unit = optional($variant->unit)->short_name ?? optional($variant->unit)->name;
                $variantLabel = trim(rtrim(rtrim(number_format($variant->weight, 2), '0'), '.') . ' ' . $unit);

                $row = [
                    $product->id,
                    $product->name,
                    $variantLabel,
                ];

                $this->appendOptionalColumns($row, $columns, $variant);

                $rows->push($row);
            }
        }

        return [$headings, $rows, 'Product List View Report'];
    }

    // Appends values in the same order as the PRODUCT_OPTIONAL_COLUMNS
    // headings loop above, so row values always line up with their heading.
    private function appendOptionalColumns(array &$row, array $columns, $variant): void
    {
        $effectiveSalePrice = $variant->sale_price ?: $variant->regular_price;

        foreach (self::PRODUCT_OPTIONAL_COLUMNS as $key => $label) {
            if (!in_array($key, $columns)) {
                continue;
            }

            $row[] = match ($key) {
                'purchase_price' => number_format($variant->purchase_price, 2),
                'sale_price' => number_format($variant->sale_price ?? 0, 2),
                'regular_price' => number_format($variant->regular_price, 2),
                'profit' => number_format($effectiveSalePrice - $variant->purchase_price, 2),
                'stock' => (int) $variant->stock,
            };
        }
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
