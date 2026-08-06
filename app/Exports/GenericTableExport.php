<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Reusable Excel export for report screens that already reduce their data
 * down to a flat list of rows + column headings on the controller side
 * (same "controller pre-aggregates, export just renders" convention as
 * ReportExport/ProductReportExport).
 */
class GenericTableExport implements FromCollection, WithHeadings, WithTitle
{
    protected array $headings;
    protected Collection $rows;
    protected string $title;

    public function __construct(array $headings, Collection $rows, string $title = 'Report')
    {
        $this->headings = $headings;
        $this->rows = $rows;
        $this->title = $title;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }
}
