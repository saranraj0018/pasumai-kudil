<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller
{
    public function productLists(Request $request)
    {
        $search = $request->input('search');
        $this->data['products'] = Product::with('details', 'order_details')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('benefits', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $this->data['category'] = Category::where('status',1)->get();
        $this->data['search'] = $search;
        return view('admin.products.index')->with($this->data);
    }

    public function saveProduct(Request $request)
    {
        $rules = [
            'product_name'   => 'required|string|max:255',
            'category_id'    => 'required',
         ];

        if (empty($request['product_id']) && !$request->has('existing_image')) {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg';
        } else if ($request->hasFile('image')) {
            // validate new uploaded image if provided
            $rules['image'] = 'image|mimes:jpeg,png,jpg';
        }

        $request->validate($rules);

     DB::beginTransaction();

    try {
            echo '<pre>';
            print_r($request->all());
            echo '<pre>';
            exit;
        if (!empty($request['product_id'])) {
            $message = 'Product updated successfully';
            $product = Product::findOrFail($request['product_id']);
        } else {
            $product = new Product();
            $message = 'Product created successfully';
        }

        $product->name = $request['product_name'];
        $product->description = $request['description'];
        $product->benefits = $request['benefits'] ?? null;

        if ($request->hasFile('image')) {
            $img_name = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->image->storeAs('products/', $img_name, 'public');
            $product->image = 'products/' . $img_name;
        } elseif ($request->filled('existing_image')) {
            $product->image = $request->existing_image;
        }

        $product->save();

        $variantIdsInRequest = [];

        if (!empty($request['variants']) && is_array($request['variants'])) {
            foreach ($request['variants'] as $index => $variantData) {
                if (empty($variantData)) {
                    Log::warning("Variant #{$index} is empty");
                    continue;
                }
                if (!empty($variantData['id'])) {
                    $variant = ProductDetail::find($variantData['id']);
                    if ($variant) {
                        $variant->update([
                            'category_id'        => $request['category_id'] ?? null,
                            'sale_price'         => $variantData['sale_price'] ?? 0,
                            'regular_price'      => $variantData['regular_price'] ?? 0,
                            'purchase_price'     => $variantData['purchase_price'] ?? 0,
                            'weight'             => $variantData['weight'] ?? 0,
                            'weight_unit'        => $variantData['weight_unit'] ?? '',
                            'stock'              => $variantData['stock'] ?? 0,
                            'tax_type'           => $variantData['tax_type'] ?? 0,
                            'tax_percentage'     => $variantData['tax_percentage'] ?? 0,
                            'is_featured_product'=> $request['is_featured'] ?? 0,
                        ]);

                        $variantIdsInRequest[] = $variant->id;
                    } else {
                        Log::error("Variant with ID {$variantData['id']} not found for update.");
                    }
                } else {

                $product_details = new ProductDetail();
                $product_details->product_id  = $product->id;
                $product_details->category_id = $request['category_id'];
                $product_details->sale_price  = $variantData['sale_price'] ?? 0;
                $product_details->regular_price = $variantData['regular_price'] ?? 0;
                $product_details->purchase_price = $variantData['purchase_price'] ?? 0;
                $product_details->weight  = $variantData['weight'] ?? 0;
                $product_details->weight_unit  = $variantData['weight_unit'] ?? 0;
                $product_details->tax_type   = $variantData['tax_type'] ?? 0;
                $product_details->tax_percentage   = $variantData['tax_percentage'] ?? 0;
                 $product_details->stock   = $variantData['stock'] ?? 0;
                $product_details->is_featured_product  = $request['is_featured'] ?? 0;
                $product_details->save();
                $variantIdsInRequest[] = $product_details->id;
                }
            }
        }

        ProductDetail::where('product_id', $product->id)
            ->whereNotIn('id', $variantIdsInRequest)
            ->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => $message,
            'product' => $product->load('details'),
        ]);
    } catch (Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'error' => $e->getMessage(),
        ], 500);
    }

    }

    public function deleteProduct(Request $request)
    {
        $product = Product::find($request->id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        ProductDetail::where('product_id', $request->id)->delete();
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    public function editProduct(Request $request)
    {

        $getProductDetails = ProductDetail::where('product_id', $request->product_id)->get();

        return response()->json([
            'success' => true,
            'product_details' => $getProductDetails,
        ], 200);
    }
}
