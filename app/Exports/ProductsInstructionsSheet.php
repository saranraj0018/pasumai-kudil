<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductsInstructionsSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        return [
            ['Instructions for Product Import:'],
            ['1. Do not rename columns. They must match exactly.'],
            ['2. Category names must exist in the system.'],
            ['3. Numeric fields (prices, weight, tax, stock) must be >= 0.'],
            ['4. Weight unit must be one of: kg, g, ml, l'],
            ['5. Tax type must be 0 (No Tax), 1 (GST), or 2 (VAT)'],
            ['6. is_featured must be 0 (No) or 1 (Yes)'],
            ['7. Leave optional fields blank if not applicable.'],
            ['8. Only one row per product-variant combination.'],
            ['9. Expiry Date formate should be (YYYY-MM-DD) 2026-01-01.'],
        ];
    }

    public function title(): string
    {
        return 'Instructions';
    }
}
