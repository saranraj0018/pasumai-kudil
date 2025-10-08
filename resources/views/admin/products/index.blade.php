<x-layouts.app>
    <div class="p-4" x-data="{ open: false }">
        <input 
        type="text" 
        placeholder="Search products..." 
        class="border p-2 rounded w-20 mb-4 shadow-md search_product">

        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Products</h2>
            <button @click="document.querySelector('#productCreateModal').__x.$data.open = true"
                class="bg-[#ab5f00] text-white px-4 py-2 rounded">
                Create Product
            </button>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table id="products" class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                    <th class="px-3 py-2">S.No</th>
                    <th class="px-3 py-2">Name</th>
                    <th class="px-3 py-2">Image</th>
                    <th class="px-3 py-2">Description</th>
                    <th class="px-3 py-2">Benefits</th>
                    <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTableBody" class="divide-y divide-gray-200">
                    @foreach ($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $product->name }}</td>
                            <td class="px-4 py-3">
                                   @if($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}"
                                     class="h-10 w-10 object-cover rounded-lg shadow-sm border" />
                            @else
                                <span class="text-gray-400 italic">No Image</span>
                            @endif  
                            </td>
                            <td class="px-4 py-3">
                                {{ $product->description }}
                            </td>
                            <td class="px-4 py-3">
                                 {{ $product->benefits }}
                            </td>
                           <td class="px-4 py-3 flex justify-center gap-4">
                            <!-- Edit -->
                            <button
                                class="text-blue-600 hover:text-blue-800 transition editProduct"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-description="{{ $product->description }}"
                                data-benefits="{{ $product->benefits }}"
                                data-category="{{ $product->details->category_id }}"
                                data-sale_price="{{ $product->details->sale_price }}"
                                data-regular_price="{{ $product->details->regular_price }}"
                                data-purchase_price="{{ $product->details->purchase_price }}"
                                data-weight="{{ $product->details->weight }}"
                                data-weight_unit="{{ $product->details->weight_unit }}"
                                data-stock="{{ $product->details->stock }}"
                                data-tax_type="{{ $product->details->tax_type }}"
                                data-tax_percentage="{{ $product->details->tax_percentage }}"
                                data-is_featured="{{ $product->details->is_featured_product }}"
                                data-image="{{ $product->image ? asset('storage/'.$product->image) : '' }}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <!-- Delete -->
                            <button class="text-red-600 hover:text-red-800 deleteProduct" data-id="{{ $product->id }}">
                                <i class="fa-solid fa-delete-left"></i>
                            </button>
                        </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4">
        {{ $products->links() }}
        </div>

        @include('admin.products.product_create_modal')
    </div>
</x-layouts.app>

<script src="{{ asset('admin/js/product.js') }}"></script>
