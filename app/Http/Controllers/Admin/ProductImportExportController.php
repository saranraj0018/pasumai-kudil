<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductExportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
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
}
