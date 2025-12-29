<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductExportTemplate implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Product Upload Sheet'    => new ProductsTemplate(), // MAIN
            'Category' => new CategoryTemplate(),  // REFERENCE
            'Import Instructions Sheet' => new ProductsInstructionsSheet(),
            'Products Sample Sheet' => new ProductsSampleSheet()
        ];
    }
}
