<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GenericTableExport;
use App\Exports\ProductExportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Imports\StockUpdateImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ProductImportExportController extends Controller
{
    public function downloadTemplate()
    {
        return Excel::download(new ProductExportTemplate, 'product_upload_template.xlsx');
    }

    public function uploadProduct(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx'
            ]);
            $import = new ProductsImport();
            try {
                Excel::import($import, $request->file('file'));
            } catch (ValidationException $e) {
                $failures = $e->failures();
                return back()->with('failures', $failures);
            }
            // If you used SkipsOnFailure in your Import class and didn't catch the exception
            if ($import->failures()->isNotEmpty()) {
                $failures = $import->failures();
                return back()->with('failures', $failures);
            }
            return back()->with('success', 'Products Uploaded Successfully');
        } catch (ValidationException $e) {
        }
    }

    // Export every variant with its current price/stock/expiry so it can be
    // edited and re-uploaded via uploadStockUpdate(). "Variant Id" is the
    // match key the re-upload relies on — it must stay in the sheet.
    public function downloadStockUpdateTemplate()
    {
        $headings = ['Variant Id', 'Product Name', 'Purchase Price', 'Regular Price', 'Sale Price', 'Stock', 'Expiry Date'];

        $rows = collect();

        $products = Product::with('variants')->orderBy('name')->get();

        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $rows->push([
                    $variant->id,
                    $product->name,
                    number_format($variant->purchase_price, 2),
                    number_format($variant->regular_price, 2),
                    number_format($variant->sale_price ?? 0, 2),
                    (int) $variant->stock,
                    $product->expiry_date ?? '',
                ]);
            }
        }

        return Excel::download(
            new GenericTableExport($headings, $rows, 'Stock & Expiry Update'),
            'stock_expiry_update_' . Carbon::now()->format('Ymd_His') . '.xlsx'
        );
    }

    // Re-upload of the sheet above. Only stock and expiry date are ever
    // written back — see StockUpdateImport.
    public function uploadStockUpdate(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx'
        ]);

        $import = new StockUpdateImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (ValidationException $e) {
            return back()->with('failures', $e->failures());
        }

        if ($import->failures()->isNotEmpty()) {
            return back()->with('failures', $import->failures());
        }

        return back()->with('success', 'Stock & Expiry Date Updated Successfully');
    }
}
