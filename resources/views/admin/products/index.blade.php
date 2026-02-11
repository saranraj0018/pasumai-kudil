<x-layouts.app>
    <div class="p-4" x-data="{ open: false }">
        {{ $search }}
        <input type="text" name="search" id="searchInput" value="{{ $search ?? '' }}" placeholder="Search products..." class="bg-white border rounded px-3 py-2 focus:ring-2 focus:ring-green-500">
       @if (session()->has('failures'))
        <div class="text-red-700 alert alert-danger border border-danger shadow-sm mt-2">
            <h5 class="mb-3 fw-bold">
                <i class="fa fa-exclamation-triangle"></i> Import Errors
            </h5>
            <ul class="list-unstyled mb-0 mt-2">
                @foreach (session('failures') as $failure)
                    <li class="mb-3 p-3 bg-light border-start border-4 border-danger rounded">
                        <div class="mb-1">
                            <strong class="text-danger">
                                Row #{{ $failure->row() }}
                            </strong>
                        </div>
                        <div>
                            <span class="badge bg-dark me-2">
                                {{ $failure->attribute() }}
                            </span>

                            @foreach ($failure->errors() as $error)
                                <span class="badge bg-danger me-1">
                                    {{ $error }}
                                </span>
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                showToast("{{ session('success') }}", "success");
            });
        </script>
    @endif
        <div class="flex flex-wrap justify-between items-center gap-3 mt-3">
            <h2 class="text-xl font-bold">Products</h2>
            <div class="flex flex-wrap items-center gap-3">
                {{-- Create Product --}}
                @can('add_products')
                    <button type="button" @click="document.querySelector('#productCreateModal').__x.$data.open = true"
                        class="bg-[#ab5f00] hover:bg-[#8f4f00] text-white px-4 py-2 rounded transition add_product">
                        Create Product
                    </button>
                @endcan

                {{-- Download Template --}}
                <a href="{{ route('download_template') }}"
                    class="bg-green-600 px-4 py-2 text-white rounded-full transition flex items-center gap-1">
                    <i class="fa fa-download"></i>
                    <span>Download Template</span>
                </a>

                {{-- Upload Product --}}
                <form action="{{ route('product_upload') }}" method="POST" enctype="multipart/form-data"
                    class="flex items-center gap-2">
                    @csrf

                    <input type="file" name="file" accept=".xlsx,.csv" required
                        class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary">

                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-full transition flex items-center gap-1">
                        <i class="fa fa-upload"></i>
                        <span>Upload</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-3 overflow-x-auto bg-white rounded-xl shadow-md" id="productTableWrapper">
            <table id="products" class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                        <th class="px-2 py-2">S.No</th>
                        <th class="px-2 py-2">Name</th>
                        <th class="px-2 py-2">Image</th>
                        <th class="px-2 py-2">Description</th>
                        <th class="px-2 py-2">Benefits</th>
                        <th class="px-2 py-2">Expiry Date</th>
                        <th class="px-2 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTableBody" class="divide-y divide-gray-200">
                    @if ($products->isNotEmpty())
                        @foreach ($products as $product)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-2 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                                <td class="px-2 py-3">{{ $product->name ?? '' }}</td>
                                <td class="px-2 py-3">
                                    @if ($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}"
                                            class="h-10 w-10 object-cover rounded-lg shadow-sm border" />
                                    @else
                                        <span class="text-gray-400 italic">No Image</span>
                                    @endif
                                </td>
                                <td class="px-2 py-3">
                                    {{ $product->description ?? '' }}
                                </td>
                                <td class="px-2 py-3">
                                    {{ $product->benefits ?? '' }}
                                </td>
                                <td class="px-2 py-3">
                                    {{ $product->expiry_date ?? '' }}
                                </td>
                                <td class="px-2 py-3 flex justify-center gap-4">
                                    <!-- Edit -->
                                    @can('edit_products')
                                        <button class="text-blue-600 hover:text-blue-800 transition editProduct"
                                            data-id="{{ $product->id }}" data-name="{{ $product->name ?? '' }}"
                                            data-description="{{ $product->description ?? '' }}"
                                            data-benefits="{{ $product->benefits ?? '' }}"
                                            data-category="{{ $product->details->category_id ?? '' }}"
                                            data-sale_price="{{ $product->details->sale_price ?? '' }}"
                                            data-regular_price="{{ $product->details->regular_price ?? '' }}"
                                            data-purchase_price="{{ $product->details->purchase_price ?? '' }}"
                                            data-weight="{{ $product->details->weight ?? '' }}"
                                            data-weight_unit="{{ $product->details->weight_unit ?? '' }}"
                                            data-stock="{{ $product->details->stock ?? '' }}"
                                            data-tax_type="{{ $product->details->tax_type ?? '' }}"
                                            data-tax_percentage="{{ $product->details->tax_percentage ?? '' }}"
                                            data-is_featured="{{ $product->details->is_featured_product ?? '' }}"
                                            data-expiry_date="{{ $product->expiry_date ?? '' }}"
                                            data-image="{{ $product->image ? asset('storage/' . $product->image) : '' }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    @endcan
                                    <!-- Delete -->
                                    @can('delete_products')
                                        @if ($product->order_details->isEmpty())
                                            <button class="text-red-600 hover:text-red-800 deleteProduct"
                                                data-id="{{ $product->id }}">
                                                <i class="fa-solid fa-delete-left"></i>
                                            </button>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center p-5">No Products found</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class="p-4">
                {{ $products->appends(['search' => $search])->links() }}
            </div>
        </div>
        @include('admin.products.product_create_modal')
    </div>
</x-layouts.app>
<script>
  SEARCH_URL = "{{ route('lists.products') }}";
</script>
<script src="{{ asset('admin/js/product.js') }}?v={{ time() }}"></script>
