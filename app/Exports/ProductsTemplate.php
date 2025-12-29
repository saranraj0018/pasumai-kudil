<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductsTemplate implements FromArray, WithHeadings,WithTitle
{
    public function headings(): array
    {
        return [
            'S.No',
            'Product Name',
            'Category',
            'Description',
            'Benefits',
            'Is Featured Product',
            'Sale Price',
            'Regular Price',
            'Purchase Price',
            'Weight',
            'Weight Unit',
            'Tax Type',
            'Tax Percentage',
            'Stock',
        ];
    }

    public function title(): string
    {
        return 'Product Upload Sheet';
    }

    public function array(): array
    {
        return [];
    }
}
