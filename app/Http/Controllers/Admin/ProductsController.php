<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function productLists()
    {
        $this->data['products'] = Product::with('details')->orderBy('created_at', 'desc')->paginate(10);
        $this->data['category'] = Category::get();
        return view('admin.products.index')->with($this->data);
    }

    public function saveProduct(Request $request)
    {
        $rules = [
            'product_name'   => 'required|string|max:255',
            'category_id'    => 'required',
            'sale_price'     => 'required|numeric',
            'regular_price'  => 'required|numeric',
            'purchase_price' => 'required|numeric',
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
            if (!empty($request['product_id'])) {
                $message = 'Product Updated successfully';
                $products = Product::find($request['product_id']);
            } else {
                $products = new Product();
                $message = 'Product saved successfully';
            }


            $products->name   = $request['product_name'];
            if ($request->hasFile('image')) {
                $img_name = $request->file('image')->getClientOriginalName();
                $img_name = time() . '_' . $img_name;
                $request->image->storeAs('products/', $img_name, 'public');
                $products->image =  'products/' . $img_name;
            } else if ($request->has('existing_image')) {
                $products->image = $request->existing_image;
            }
            $products->description   = $request['description'];
            $products->benefits     = $request['benefits'];
            $products->save();

            $product_details = new ProductDetail();
            $product_details->product_id  = $products->id;
            $product_details->category_id = $request['category_id'];
            $product_details->sale_price  = $request['sale_price'] ?? 0;
            $product_details->regular_price = $request['regular_price'] ?? 0;
            $product_details->purchase_price = $request['purchase_price'] ?? 0;
            $product_details->weight  = $request['weight'] ?? 0;
            $product_details->weight_unit  = $request['weight_unit'] ?? 0;
            $product_details->tax_type   = $request['tax_type'];
            $product_details->tax_percentage   = $request['tax_percentage'];
            $product_details->is_featured_product  = $request['is_featured'] ?? 0;
            $product_details->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'product' => $products,
                'product_details' => $product_details
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
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

    public function searchProduct(Request $request)
    {
        $search = $request->input('query');

        $products = Product::with('details')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
                //   ->orWhere('description', 'like', "%{$search}%")
                //   ->orWhere('benefits', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $html = '';
        $count=1;
        foreach ($products as $product) {
            $html .= '
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 font-medium text-gray-900">'.$count.'</td>
            <td class="px-4 py-3">' . e($product->name) . '</td>
            <td class="px-4 py-3">';
            if ($product->image) {
                $html .= '<img src="' . asset('storage/' . $product->image) . '" class="h-10 w-10 object-cover rounded-lg shadow-sm border" />';
            } else {
                $html .= '<span class="text-gray-400 italic">No Image</span>';
            }
            $html .= '</td>
            <td class="px-4 py-3">' . e($product->description) . '</td>
            <td class="px-4 py-3">' . e($product->benefits) . '</td>
            <td class="px-4 py-3 flex justify-center gap-4">
                <button class="text-blue-600 hover:text-blue-800 transition editProduct"
                    data-id="' . $product->id . '"
                    data-name="' . e($product->name) . '"
                    data-description="' . e($product->description) . '"
                    data-benefits="' . e($product->benefits) . '">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>

                <button class="text-red-600 hover:text-red-800 deleteProduct" data-id="' . $product->id . '">
                    <i class="fa-solid fa-delete-left"></i>
                </button>
            </td>
        </tr>';

        $count++;
        }



        return response()->json([
            'success' => true,
            'html' => $html,
            'product' => $products
        ], 200);
    }
}
