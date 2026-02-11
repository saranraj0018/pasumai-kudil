<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Category;
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

                if (!$categoryName || !$productName) {
                    continue; // skip invalid row
                }

                // ==============================
                // CATEGORY
                // ==============================
                $category = Category::where('name', $categoryName)->first();
                if (!$category) {
                    continue; // skip row if invalid category
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
                $productDetail->weight_unit         = $row['weight_unit'] ?? null;
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
    /**
     * Excel validation rules
     */
    public function rules(): array
    {
        return [
            '*.product_name'   => ['required', 'string', 'max:255'],
            '*.category'       => ['required', Rule::exists('categories', 'name')],
            '*.regular_price'  => ['required', 'numeric', 'min:0'],
            '*.purchase_price' => ['required', 'numeric', 'min:0'],
            '*.sale_price'     => ['nullable', 'numeric', 'min:0'],
            '*.weight'         => ['nullable', 'numeric', 'min:0'],
            '*.weight_unit'    => ['nullable', 'in:kg,g,ml,l'],
            '*.tax_type'       => ['nullable', 'in:0,1,2'],
            '*.tax_percentage' => ['nullable', 'numeric', 'min:0'],
            '*.stock'          => ['required', 'integer', 'min:0'],
            '*.is_featured'    => ['nullable', 'in:0,1'],
            '*.expiry_date'    => ['nullable'],
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
