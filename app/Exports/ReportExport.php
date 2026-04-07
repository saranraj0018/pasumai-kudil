<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExport implements FromCollection, WithHeadings
{
    protected $data, $type, $reportType;

    public function __construct($data, $type, $reportType)
    {
        $this->data = $data;
        $this->type = $type;
        $this->reportType = $reportType;
    }

    // ================= COLLECTION =================
    public function collection()
    {
        return $this->type === 'grocery'
            ? $this->groceryReport()
            : $this->milkReport();
    }

    // ================= HEADINGS =================
    public function headings(): array
    {
        if ($this->reportType === 'summary') {
            return ['Name', 'Total Quantity', 'Total Amount'];
        }

        if ($this->reportType === 'daily') {
            return ['Date', 'Total Quantity', 'Total Amount'];
        }

        if ($this->reportType === 'detailed') {

            if ($this->type === 'grocery') {
                return ['Order ID', 'User', 'Product', 'Quantity', 'Price', 'Total', 'Date'];
            }

            if ($this->type === 'milk') {
                return ['User', 'Subscription Name', 'Quantity', 'Price', 'Pack','Delivery Status','Date'];
            }
        }

        return [];
    }

    // ================= GROCERY =================
    private function groceryReport()
    {
        $rows = collect();
        if ($this->reportType === 'detailed') {
            foreach ($this->data as $item) {
                $rows->push([
                    $item->order_id ?? '',
                    $item->order->user->name ?? '',
                    $item->product->name ?? '',
                    $item->quantity ?? 0,
                    $item->net_amount ?? 0,
                    $item->order->gross_amount ?? 0,
                    optional($item->order->created_at)->format('Y-m-d'),
                ]);
            }
        }

        elseif ($this->reportType === 'summary') {
            $group = $this->data->groupBy('product_id');
            foreach ($group as $items) {
                $rows->push([
                    optional($items->first()->product)->name ?? '',
                    $items->sum('quantity'),
                    $items->sum(fn($i) => $i->order->gross_amount ?? 0)
                ]);
            }
        }

        elseif ($this->reportType === 'daily') {
            $group = $this->data->groupBy(
                fn($i) =>
                optional($i->order->created_at)->format('Y-m-d')
            );

            foreach ($group as $date => $items) {
                $rows->push([
                    $date,
                    $items->sum('quantity'),
                    $items->sum(fn($i) => $i->order->gross_amount ?? 0),
                ]);
            }
        }

        return $rows;
    }

    // ================= MILK =================
    private function milkReport()
    {
        $rows = collect();
        if ($this->reportType === 'detailed') {
            foreach ($this->data as $item) {
                $rows->push([
                    $item->get_user->name ?? '',
                    optional($item->get_user_subscription->get_subscription)->name ?? 'Milk',
                    $item->quantity ?? 1,
                    $item->amount ?? 0,
                    $item->pack ?? 0,
                    $item->delivery_status ?? '',
                    $item->delivery_date ?? '',
                ]);
            }
        }

        // -------- SUMMARY --------
        elseif ($this->reportType === 'summary') {
            $group = $this->data->groupBy(
                fn($i) =>
                optional($i->get_user_subscription->get_subscription)->name ?? 'Milk'
            );
            foreach ($group as $name => $items) {
                $rows->push([
                    $name,
                    $items->sum('quantity'),
                    $items->sum(fn($i) => $i->amount ?? 0)
                ]);
            }
        }

        // -------- DAILY --------
        elseif ($this->reportType === 'daily') {
            $group = $this->data->groupBy('delivery_date');
            foreach ($group as $date => $items) {
                $rows->push([
                    $date,
                    $items->sum('quantity'),
                    $items->sum(fn($i) => $i->amount ?? 0),
                ]);
            }
        }

        return $rows;
    }
}
