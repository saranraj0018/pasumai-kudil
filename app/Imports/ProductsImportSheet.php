<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Category;
use App\Models\Unit;
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

class ProductsImportSheet implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    SkipsOnError,
    SkipsEmptyRows
{
    use Importable, SkipsFailures, SkipsErrors;

    /**
     * Handle Excel rows
     */
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            // Nothing to process
            return;
        }

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {

                // Skip completely empty rows
                if ($row->filter()->isEmpty()) {
                    continue;
                }

                $categoryName = trim($row['category'] ?? '');
                $productName  = trim($row['product_name'] ?? '');
                $weightUnit   = trim($row['weight_unit'] ?? '');

                if (!$categoryName || !$productName || !$weightUnit) {
                    continue;
                }

                $category = Category::where('name', $categoryName)->first();
                $units = Unit::where('short_name', $weightUnit)->first();
                if (!$category || !$units) {
                    continue;
                }

                // ==============================
                // PRODUCT (create or update)
                // ==============================
                $product = $this->saveOrUpdateProduct($row->toArray());

                // ==============================
                // VARIANT (PRODUCT DETAILS)
                // ==============================
                $productDetail = new ProductDetail();
                $productDetail->product_id          = $product->id;
                $productDetail->category_id         = $category->id;
                $productDetail->regular_price       = $row['regular_price'] ?? 0;
                $productDetail->purchase_price      = $row['purchase_price'] ?? 0;
                $productDetail->sale_price          = $row['sale_price'] ?? 0;
                $productDetail->weight              = $row['weight'] ?? 0;
                $productDetail->weight_unit         = $units->id ?? null;
                $productDetail->tax_type            = $row['tax_type'] ?? 0;
                $productDetail->tax_percentage      = $row['tax_percentage'] ?? 0;
                $productDetail->stock               = $row['stock'] ?? 0;
                $productDetail->is_featured_product = $row['is_featured'] ?? 0;
                $productDetail->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Save or update product based on name
     */
    protected function saveOrUpdateProduct(array $row): Product
    {
        $productName = trim($row['product_name'] ?? '');
        $product = Product::where('name', $productName)->first();

        if ($product) {
            $product->description = $row['description'] ?? $product->description;
            $product->benefits    = $row['benefits'] ?? $product->benefits;
            $product->save();
        } else {
            $product =  new Product();
            $product->name = $productName;
            $product->description = $row['description'] ?? null;
            $product->benefits = $row['benefits'] ?? null;
            $product->image = null;
            $product->expiry_date = $this->transformDate($row['expiry_date'] ?? null);
            $product->save();
        }

        return $product;
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
            '*.product_name' => ['required', 'string', 'max:255'],
            '*.category' => ['required', Rule::exists('categories', 'name')],
            '*.mrp' => ['required', 'numeric', 'min:0'],
            '*.purchase_price' => ['required', 'numeric', 'min:0'],
            '*.sale_price' => ['nullable', 'numeric', 'min:0'],
            '*.weight' => ['nullable', 'numeric', 'min:0'],
            '*.weight_unit' => ['required', Rule::exists('units', 'short_name')],
            '*.tax_type' => ['nullable', 'in:0,1,2'],
            '*.tax_percentage' => ['nullable', 'numeric', 'min:0'],
            '*.stock' => ['required', 'integer', 'min:0'],
            '*.is_featured' => ['nullable', 'in:0,1'],
            '*.expiry_date' => ['nullable'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($validator->getData() as $i => $row) {
                $mrp = (float)($row['mrp'] ?? 0);
                $purchase = (float)($row['purchase_price'] ?? 0);
                $sale = (float)($row['sale_price'] ?? 0);
                if ($purchase >= $mrp) {
                    $validator->errors()->add("{$i}.purchase_price", "Purchase Price ({$purchase}) must be less than MRP ({$mrp}).");
                }
                if ($sale > 0 && $sale >= $mrp) {
                    $validator->errors()->add("{$i}.sale_price", "Sale Price ({$sale}) must be less than MRP ({$mrp}).");
                }
                if ($sale > 0 && $purchase >= $sale) {
                    $validator->errors()->add("{$i}.sale_price", "Sale Price ({$sale}) must be greater than Purchase Price ({$purchase}).");
                }
            }
        });
    }

    public function customValidationMessages()
    {
        return [
            '*.product_name.required' => 'Product Name is required.',
            '*.category.required' => 'Category is required.',
            '*.category.exists' => 'Category does not exist.',
            '*.mrp.required' => 'MRP is required.',
            '*.purchase_price.required' => 'Purchase Price is required.',
        ];
    }

    /**
     * Optional: Customize heading row to lowercase automatically
     */
    public function headingRow(): int
    {
        return 1; // first row
    }
}
