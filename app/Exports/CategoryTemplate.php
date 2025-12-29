<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CategoryTemplate implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Category::where('status', 1)->select('id', 'name')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Category Name'];
    }

    public function title(): string
    {
        return 'Category Lists';
    }
}
