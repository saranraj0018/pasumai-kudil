<?php

namespace App\Exports;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class WeightUnitTemplate implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Unit::where('status', 1)->select('id', 'short_name')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Unit Name'];
    }

    public function title(): string
    {
        return 'Weight Unit Lists';
    }
}
