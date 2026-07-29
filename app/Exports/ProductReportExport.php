<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductReportExport implements FromCollection, WithHeadings
{
    protected $product, $summary, $daily, $byProduct;

    public function __construct($product, array $summary, Collection $daily, ?Collection $byProduct = null)
    {
        $this->product = $product;
        $this->summary = $summary;
        $this->daily = $daily;
        $this->byProduct = $byProduct;
    }

    public function headings(): array
    {
        if ($this->byProduct) {
            return ['Product', 'Orders', 'Quantity Sold', 'Sales Amount', 'Cost Amount', 'Profit'];
        }

        return ['Date', 'Orders', 'Quantity Sold', 'Sales Amount', 'Cost Amount', 'Profit'];
    }

    public function collection()
    {
        $rows = collect();

        if ($this->byProduct) {
            foreach ($this->byProduct as $row) {
                $rows->push([
                    $row['name'],
                    $row['orders'],
                    $row['quantity'],
                    $row['sales'],
                    $row['cost'],
                    $row['profit'],
                ]);
            }
        } else {
            foreach ($this->daily as $row) {
                $rows->push([
                    $row['date'],
                    $row['orders'],
                    $row['quantity'],
                    $row['sales'],
                    $row['cost'],
                    $row['profit'],
                ]);
            }
        }

        $rows->push([
            'TOTAL',
            $this->summary['total_orders'],
            $this->summary['total_quantity'],
            $this->summary['total_sales'],
            $this->summary['total_cost'],
            $this->summary['total_profit'],
        ]);

        return $rows;
    }
}
