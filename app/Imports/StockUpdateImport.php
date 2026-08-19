<?php

namespace App\Imports;

use App\Models\ProductDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\{
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    SkipsOnError,
    SkipsEmptyRows
};
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsErrors;

// Re-upload of the "Stock & Expiry Update" template. Rows are matched back
// to their variant by the Variant Id column (product_details.id), which the
// export always includes precisely so this import can find them again. The
// Product Name / Purchase / Regular / Sale Price columns are reference-only:
// this class only ever writes stock (on the variant) and expiry date (on
// the parent product), even though those other columns are present.
class StockUpdateImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    SkipsOnError,
    SkipsEmptyRows
{
    use Importable, SkipsFailures, SkipsErrors;

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                if ($row->filter()->isEmpty()) {
                    continue;
                }

                $variant = ProductDetail::with('product')->find($row['variant_id'] ?? null);
                if (!$variant || !$variant->product) {
                    continue;
                }

                $variant->stock = (int) $row['stock'];
                $variant->save();

                $expiryDate = $this->transformDate($row['expiry_date'] ?? null);
                if ($expiryDate !== null) {
                    $variant->product->expiry_date = $expiryDate;
                    $variant->product->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function transformDate($value)
    {
        if (!$value) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(
                Date::excelToDateTimeObject($value)
            )->format('Y-m-d');
        }

        return Carbon::parse($value)->format('Y-m-d');
    }

    public function rules(): array
    {
        return [
            '*.variant_id' => ['required', 'integer', Rule::exists('product_details', 'id')],
            '*.stock' => ['required', 'integer', 'min:0'],
            '*.expiry_date' => ['nullable'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.variant_id.required' => 'Variant Id is required — do not remove or edit this column.',
            '*.variant_id.integer' => 'Variant Id must be the number from the exported sheet.',
            '*.variant_id.exists' => 'Variant Id does not match any existing product variant.',
            '*.stock.required' => 'Stock is required.',
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }
}
