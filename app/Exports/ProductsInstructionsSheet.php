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
            ['Instructions for Product Import'],

            ['1. Do not rename, delete, or reorder any column headers. They must match the template exactly.'],

            ['2. "Product Name" is required and should be unique for each product.'],

            ['3. "Category" is required and must exactly match an existing category name from the Category sheet.'],

            ['4. "Description" is optional.'],

            ['5. "Benefits" is optional.'],

            ['6. "Is Featured Product" is optional. Enter 1 for Yes or 0 for No.'],

            ['7. "Sale Price" is optional. If provided, it must be greater than Purchase Price and less than MRP.'],

            ['8. "MRP" is required and must be greater than both Sale Price and Purchase Price.'],

            ['9. "Purchase Price" is required and must be less than both MRP and Sale Price (if Sale Price is provided).'],

            ['10. "Weight" is optional and must be a numeric value greater than or equal to 0.'],

            ['11. "Weight Unit" is required and must exactly match an existing Weight Unit from the Weight Unit sheet (e.g., Kg, g, ml, L).'],

            ['12. "Tax Type" is optional. Allowed values: 0 = No Tax, 1 = GST, 2 = VAT.'],

            ['13. "Tax Percentage" is optional and must be a numeric value greater than or equal to 0.'],

            ['14. "Stock" is required and must be a whole number greater than or equal to 0.'],

            ['15. "Expiry Date" is optional. Use the format YYYY-MM-DD (Example: 2026-01-01).'],

            ['16. Leave optional fields blank if they are not applicable.'],

            ['17. Upload only one row for each product variant.'],

            ['18. Do not enter negative values for prices, weight, tax percentage, or stock.'],
        ];
    }

    public function title(): string
    {
        return 'Instructions';
    }
}
