<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GenericTableExport;
use App\Exports\ProductReportExport;
use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DailyDelivery;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Unit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    const ORDER_DELIVERED_STATUS = 4;

    public function index(Request $request)
    {
        $this->data['users'] = User::get();
        $this->data['products'] = Product::orderBy('name')->get();
        $this->data['categories'] = Category::where('status', 1)->orderBy('name')->get();
        $this->data['units'] = Unit::where('status', 1)->get();
        return view('admin.reports')->with($this->data);
    }

    /**
     * Shared filter-dropdown data every report tab needs, so each export
     * method can re-render the reports page on view_type=view without
     * refetching this by hand.
     */
    private function baseViewData(): array
    {
        return [
            'users' => User::get(),
            'products' => Product::orderBy('name')->get(),
            'categories' => Category::where('status', 1)->orderBy('name')->get(),
            'units' => Unit::where('status', 1)->get(),
        ];
    }

    /**
     * Render a pre-aggregated report as View / Excel / PDF, reusing the same
     * three output modes as the Sales / Product Performance reports.
     */
    private function renderTable(Request $request, array $headings, Collection $rows, string $title, string $activeTab, string $fileNamePrefix)
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

        $this->data = $this->baseViewData();
        $this->data['table_headings'] = $headings;
        $this->data['table_rows'] = $rows;
        $this->data['table_title'] = $title;
        $this->data['filters'] = $request->all();
        $this->data['active_tab'] = $activeTab;

        return view('admin.reports')->with($this->data);
    }


    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:grocery,milk',
            'report_type' => 'required|in:detailed,summary,daily',
            'view_type' => 'required|in:view,excel,pdf',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
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
            'to_date' => 'nullable|date|after_or_equal:from_date',
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

    // ================= ITEM CATALOG REPORT (category-wise) =================
    public function itemListExport(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'view_type' => 'required|in:view,excel,pdf',
        ]);

        $details = ProductDetail::with(['product', 'category', 'unit'])
            ->where('category_id', $request->category_id)
            ->get();

        $headings = ['Code', 'Description', 'Category', 'UOM', 'MRP', 'Landing Cost', 'Sale Rate', 'Tax %'];
        $rows = $details->map(fn($d) => [
            $d->product_id,
            $d->product->name ?? '',
            $d->category->name ?? '',
            $d->unit->short_name ?? '',
            number_format($d->regular_price ?? 0, 2),
            number_format($d->purchase_price ?? 0, 2),
            number_format($d->sale_price ?? 0, 2),
            $d->tax_percentage ?? 0,
        ]);

        return $this->renderTable($request, $headings, $rows, 'Item List Report', 'items', 'item_list');
    }

    // ================= SALES BILL DETAILS / ITEM-WISE / CATEGORY-WISE =================
    public function salesBreakdownExport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:bill_details,item_wise,category_wise',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'view_type' => 'required|in:view,excel,pdf',
        ]);

        $orderDetails = $this->getDeliveredOrderDetails($request->from_date, $request->to_date);

        if ($request->report_type === 'bill_details') {
            $headings = ['Bill No', 'Customer', 'Date', 'Sale Type', 'Product', 'Qty', 'Rate', 'Amount'];
            $rows = $orderDetails->map(fn($d) => [
                $d->order->order_id ?? '',
                optional($d->order->user)->name ?? 'Guest',
                optional($d->order->delivered_at) ? Carbon::parse($d->order->delivered_at)->format('d/m/Y H:i') : '',
                $d->order->payment_method ? ucfirst($d->order->payment_method) : 'Unknown',
                $d->product->name ?? '',
                $d->quantity ?? 0,
                $d->quantity ? number_format(($d->net_amount ?? 0) / $d->quantity, 2) : '0.00',
                number_format($d->net_amount ?? 0, 2),
            ]);
            $title = 'Sale Bill Details Report';
        } elseif ($request->report_type === 'item_wise') {
            $headings = ['Code', 'Description', 'Qty', 'Amount'];
            $group = $orderDetails->groupBy('product_id');
            $rows = $group->map(fn($items, $productId) => [
                $productId,
                optional($items->first()->product)->name ?? '',
                $items->sum('quantity'),
                number_format($items->sum(fn($i) => $i->net_amount ?? 0), 2),
            ])->values();
            $title = 'Item Wise Sales Report';
        } else {
            $headings = ['Category', 'Qty', 'Amount'];
            $group = $orderDetails->groupBy('category_id');
            $rows = $group->map(fn($items) => [
                optional($items->first()->category)->name ?? 'Unknown',
                $items->sum('quantity'),
                number_format($items->sum(fn($i) => $i->net_amount ?? 0), 2),
            ])->values();
            $title = 'Category Wise Sales Report';
        }

        return $this->renderTable($request, $headings, collect($rows), $title, 'sales', 'sales_' . $request->report_type);
    }

    // ================= SALES AMOUNT / DAYWISE BILL SUMMARY (payment-mode split) =================
    public function paymentSummaryExport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:sales_amount,daywise_bill_summary',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'view_type' => 'required|in:view,excel,pdf',
        ]);

        $orders = $this->getDeliveredOrders($request->from_date, $request->to_date);
        $methods = ['cash', 'card', 'gpay', 'credit'];

        if ($request->report_type === 'sales_amount') {
            $headings = ['Sale Type', 'Amount'];
            $rows = collect();
            $total = 0;
            foreach ($methods as $method) {
                $amount = $orders->where('payment_method', $method)->sum('net_amount');
                $rows->push([ucfirst($method), number_format($amount, 2)]);
                $total += $amount;
            }
            $unknown = $orders->whereNull('payment_method')->sum('net_amount');
            if ($unknown > 0) {
                $rows->push(['Unknown', number_format($unknown, 2)]);
                $total += $unknown;
            }
            $rows->push(['Total', number_format($total, 2)]);
            $title = 'Sales Amount Report';
        } else {
            $headings = ['Date', 'Cash', 'Card', 'GPay', 'Credit', 'Total'];
            $group = $orders->groupBy(fn($o) => optional($o->delivered_at) ? Carbon::parse($o->delivered_at)->format('Y-m-d') : 'Unknown');
            $rows = $group->map(function ($dayOrders, $date) use ($methods) {
                $row = [$date];
                $total = 0;
                foreach ($methods as $method) {
                    $amount = $dayOrders->where('payment_method', $method)->sum('net_amount');
                    $row[] = number_format($amount, 2);
                    $total += $amount;
                }
                $row[] = number_format($total, 2);
                return $row;
            })->sortKeys()->values();
            $title = 'DayWise Bill Summary';
        }

        return $this->renderTable($request, $headings, collect($rows), $title, 'sales', 'payment_summary_' . $request->report_type);
    }

    // ================= ITEM / CATEGORY / DAY WISE PROFIT =================
    public function profitReportExport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:item_wise,category_wise,day_wise',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'view_type' => 'required|in:view,excel,pdf',
        ]);

        $orderDetails = $this->getDeliveredOrderDetails($request->from_date, $request->to_date);

        if ($request->report_type === 'item_wise') {
            $headings = ['Code', 'Description', 'Sales Qty', 'Landing Cost Amt', 'Sales Amt', 'Profit'];
            $group = $orderDetails->groupBy('product_id');
            $rows = $group->map(function ($items, $productId) {
                $sales = $items->sum(fn($i) => $i->net_amount ?? 0);
                $cost = $items->sum(fn($i) => $this->getProductCost($i) * ($i->quantity ?? 0));
                return [
                    $productId,
                    optional($items->first()->product)->name ?? '',
                    $items->sum('quantity'),
                    number_format($cost, 2),
                    number_format($sales, 2),
                    number_format($sales - $cost, 2),
                ];
            })->values();
            $title = 'Item Wise Profit Report';
        } elseif ($request->report_type === 'category_wise') {
            $headings = ['Category', 'Sales Qty', 'Landing Cost Amt', 'Sales Amt', 'Profit'];
            $group = $orderDetails->groupBy('category_id');
            $rows = $group->map(function ($items) {
                $sales = $items->sum(fn($i) => $i->net_amount ?? 0);
                $cost = $items->sum(fn($i) => $this->getProductCost($i) * ($i->quantity ?? 0));
                return [
                    optional($items->first()->category)->name ?? 'Unknown',
                    $items->sum('quantity'),
                    number_format($cost, 2),
                    number_format($sales, 2),
                    number_format($sales - $cost, 2),
                ];
            })->values();
            $title = 'Category Wise Profit Report';
        } else {
            $headings = ['Date', 'Sales Qty', 'Landing Cost Amt', 'Sales Amt', 'Profit', 'Profit %'];
            $group = $orderDetails->groupBy(fn($i) => optional($i->order->delivered_at) ? Carbon::parse($i->order->delivered_at)->format('Y-m-d') : 'Unknown');
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
            $title = 'Day Wise Profit Report';
        }

        return $this->renderTable($request, $headings, collect($rows), $title, 'sales', 'profit_' . $request->report_type);
    }

    private function getDeliveredOrderDetails($from, $to)
    {
        return OrderDetail::with(['order.user', 'product', 'category', 'variants', 'product.details'])
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('status', self::ORDER_DELIVERED_STATUS);
                if ($from && $to) {
                    $q->whereBetween('delivered_at', [$from, $to]);
                }
            })
            ->get();
    }

    private function getDeliveredOrders($from, $to)
    {
        return Order::where('status', self::ORDER_DELIVERED_STATUS)
            ->when($from && $to, fn($q) => $q->whereBetween('delivered_at', [$from, $to]))
            ->get();
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
