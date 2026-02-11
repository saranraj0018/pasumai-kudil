<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductsSampleSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        return [
            [
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
                'Expiry Date'
            ],
            [
                1,
                'Organic Honey',
                'Grocery',
                'Pure honey from organic farms',
                'Boosts immunity',
                1,
                230,
                250,
                200,
                500,
                'g',
                1,
                5,
                50,
                2026-05-01
            ],
            [
                2,
                'Green Tea',
                'Beverages',
                'Loose leaf green tea',
                'Rich in antioxidants',
                0,
                120,
                150,
                100,
                250,
                'g',
                0,
                0,
                100,
                2026 - 06 - 01
            ],
        ];
    }

    public function title(): string
    {
        return 'Sample Data';
    }
}
