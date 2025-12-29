<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductsImport implements WithMultipleSheets
{

    public ProductsImportSheet $sheet;

    public function __construct()
    {
        $this->sheet = new ProductsImportSheet();
    }

    public function sheets(): array
    {
        return [
            'Product Upload Sheet' => $this->sheet,
        ];
    }

    public function failures()
    {
        return $this->sheet->failures();
    }
}
