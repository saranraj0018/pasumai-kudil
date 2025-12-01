<div id="productCreateModal" x-data="{
    open: false,
    previewUrl: null,
    exiting_image: '',
    steps: ['Product Info', 'Product Details', 'Review'],
    stepNumber: 0,
    form: {
        name: '',
        category_id: '',
        description: '',
        benefits: '',
        image: null,
        sale_price: null,
        regular_price: null,
        purchase_price: null,
        weight: null,
        weight_unit: 'kg',
        tax_type: '',
        tax_percentage: null,
        is_featured_product: false
    },
    closeModal() {
        this.open = false;
        this.stepNumber = 0;
        this.variants = [];
        this.form = {
            name: '',
            category_id: '',
            description: '',
            benefits: '',
            image: null,
            sale_price: null,
            regular_price: null,
            purchase_price: null,
            weight: null,
            weight_unit: 'kg',
            tax_type: '',
            tax_percentage: null,
            is_featured_product: false
        };
    },
    nextStep() { if (this.stepNumber < this.steps.length - 1) this.stepNumber++ },
    prevStep() { if (this.stepNumber > 0) this.stepNumber-- },
}" x-cloak>
    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

            <!-- Modal Box -->
            <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-[90%] relative z-50">

                <h2 class="text-2xl font-bold mb-6 text-gray-800" x-text="modalTitle" id="product_label">Add Product</h2>
                <form id="productAddForm" enctype="multipart/form-data" novalidate
                    class="flex flex-col justify-start items-start w-full  h-[75vh] overflow-y-scroll">
                    @csrf
                    <input type="hidden" name="exiting_image" x-model="exiting_image" id="exiting_image" />
                    <input type="hidden" name="product_id" x-model="form.product_id" id="product_id" />
                    <div class="p-5 space-y-5 flex-1 w-full h-fit">
                        {{-- Step 1: Product Information --}}
                        <div x-show="stepNumber === 0" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-label>Product Name<span class="text-red-500">*</span></x-label>
                                    <x-input type="text" x-model="form.name" name="product_name" id="product_name"
                                        placeholder="eg .., Flower" required />
                                </div>

                                <div>
                                    <x-label>Category</x-label>

                                    <x-select x-model="form.category_id" name="category_id" id="category_id" required>
                                        <option value="" selected disabled>Please Select Category</option>
                                        @foreach ($category as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <div>
                                    <x-label>Description</x-label>
                                    <x-textarea placeholder="Enter Description" name="description"
                                        x-model="form.description"></x-textarea>
                                </div>
                                <div>
                                    <x-label>Benefits</x-label>
                                    <x-textarea placeholder="Enter Benifits" name="benefits" x-model="form.benefits" />
                                </div>
                                <div class="col-span-2">
                                    <x-label>Image</x-label>
                                    <input type="file" name="image" id="image" accept=".png, .jpg, .jpeg"
                                        x-ref="fileInput"
                                        @change="
                               const file = $refs.fileInput.files[0];
                               if (file) {
                                   const reader = new FileReader();
                                   reader.onload = e => { previewUrl = e.target.result }
                                   reader.readAsDataURL(file);
                               }
                           "
                                        class="form-input w-full border border-gray-300 rounded-lg p-2 cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#ab5f00] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#ab5f00] file:text-white hover:file:bg-[#ab5f00]">

                                    <div class="mt-4 flex justify-center overflow-hidden">
                                        <img :src="previewUrl" x-show="previewUrl"
                                            class="w-full max-h-[30vh] rounded-lg border border-gray-300 shadow-md object-cover" />
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 col-span-2">
                                    <input type="checkbox" value="1" name="is_featured"
                                        x-model="form.is_featured_product" class="h-4 w-4" />
                                    <x-label class="block text-sm font-medium">Is Featured Product</x-label>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Product Details --}}
                        <div x-show="stepNumber === 1" class="space-y-6">

                            <!-- Main product pricing -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <x-label>Sale Price</x-label>
                                    <x-input type="number" step="0.01" x-model="form.sale_price"
                                        name="variants[0][sale_price]" />
                                </div>
                                <div>
                                    <x-label>Regular Price<span class="text-red-500">*</span></x-label>
                                    <x-input type="number" step="0.01" class="regularPriceInput" id="regular_price" x-model="form.regular_price"
                                        name="variants[0][regular_price]" required/>
                                </div>
                                <div>
                                    <x-label>Purchase Price<span class="text-red-500">*</span></x-label>
                                    <x-input type="number" step="0.01" class="purchasePriceInput" id="purchase_price" x-model="form.purchase_price"
                                        name="variants[0][purchase_price]" required/>
                                </div>
                                <div>
                                    <x-label>Weight</x-label>
                                    <x-input type="number" step="0.01" x-model="form.weight" name="variants[0][weight]" />
                                </div>
                                <div>
                                    <x-label>Weight Unit</x-label>
                                    <x-select x-model="form.weight_unit" name="variants[0][weight_unit]">
                                        <option value="kg">kg</option>
                                        <option value="g">g</option>
                                        <option value="ml">ml</option>
                                        <option value="l">l</option>
                                    </x-select>
                                </div>
                                <div>
                                    <x-label>Tax Type</x-label>
                                    <x-select x-model="form.tax_type" name="variants[0][tax_type]">
                                        <option value="" selected>Please Select Tax Type</option>
                                        <option value="0">Zero</option>
                                        <option value="1">Inclusive</option>
                                        <option value="2">Exclusive</option>
                                    </x-select>
                                </div>
                                <div x-show="form.tax_type != '0' && form.tax_type != ''">
                                    <x-label>Tax Percentage(%)</x-label>
                                    <x-input type="number" step="0.01" x-model="form.tax_percentage"
                                        name="variants[0][tax_percentage]"/>
                                </div>
                                <div>
                                    <x-label>Stock<span class="text-red-500">*</span></x-label>
                                    <x-input type="number" step="1" class="stock" x-model="form.stock"
                                        name="variants[0][stock]" required/>
                                </div>
                            </div>

                            <!-- Variant Section -->
                            <div class="mt-6">
                                <div class="flex justify-between items-center mb-3">
                                    <h1></h1>
                                    <button type="button" id="addVariantBtn"
                                        class="px-4 py-2 bg-[#ab5f00] text-white rounded-lg ">
                                        Add Variant
                                    </button>
                                </div>
                                  <div id="variantContainer"></div>
                            </div>
                        </div>
                        {{-- Step 3: Review & Save --}}
                        <div x-show="stepNumber === 2" class="space-y-4">

                            <div class="bg-gray-50 rounded-lg p-4 shadow-sm">
                                <h4 class="text-md font-medium mb-2">Product Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <span class="font-semibold">Name:</span>
                                        <p class="text-gray-700" x-text="form.name || '-'"></p>
                                    </div>
                                    <div>
                                        <span class="font-semibold">Category:</span>
                                        <p class="text-gray-700" x-text="form.category_id || '-'"></p>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="font-semibold">Description:</span>
                                        <p class="text-gray-700" x-text="form.description || '-'"></p>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="font-semibold">Benefits:</span>
                                        <p class="text-gray-700" x-text="form.benefits || '-'"></p>
                                    </div>
                                    <div>
                                        <span class="font-semibold">Image Preview:</span>
                                        <div class="mt-2">
                                            <img :src="form.image ? URL.createObjectURL(form.image) : ''"
                                                alt="Product Image"
                                                class="max-h-40 rounded-lg border border-gray-200 object-contain"
                                                x-show="form.image">
                                            <p class="text-gray-500" x-show="!form.image">No image uploaded</p>
                                        </div>
                                    </div>
                                     <div>
                                        <input type="checkbox" disabled class="h-4 w-4"
                                            :checked="form.is_featured_product" />
                                        <span class="font-semibold">Is Featured Product</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4 shadow-sm">
                                <h4 class="text-md font-medium mb-2">Pricing & Details</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <span class="font-semibold">Sale Price:</span>
                                        <p class="text-gray-700" x-text="form.sale_price ? '$'+form.sale_price : '-'">
                                        </p>
                                    </div>
                                    <div>
                                        <span class="font-semibold">Regular Price:</span>
                                        <p class="text-gray-700"
                                            x-text="form.regular_price ? '$'+form.regular_price : '-'"></p>
                                    </div>
                                    <div>
                                        <span class="font-semibold">Purchase Price:</span>
                                        <p class="text-gray-700"
                                            x-text="form.purchase_price ? '$'+form.purchase_price : '-'"></p>
                                    </div>
                                    <div>
                                        <span class="font-semibold">Weight:</span>
                                        <p class="text-gray-700"
                                            x-text="form.weight ? form.weight+' '+form.weight_unit : '-'">
                                        </p>
                                    </div>
                                    <div>
                                        <span class="font-semibold">Tax Type:</span>
                                        <p class="text-gray-700" x-text="form.tax_type || '-'"></p>
                                    </div>
                                    <div>
                                        <span class="font-semibold">Tax Percentage:</span>
                                        <p class="text-gray-700"
                                            x-text="form.tax_percentage ? form.tax_percentage+'%' : '-'"></p>
                                    </div>

                                </div>

                                <div class="mt-6">
                                <div id="viewVariantProducts"></div>
                            </div>
                            </div>
                        </div>
                    </div>
                    <!-- Buttons -->

                    <div class="flex items-center gap-5 justify-between mt-6 w-full">
                        <x-button varient="ghost" type="button" @click="prevStep()"
                            x-show="stepNumber > 0">Back</x-button>

                        <div class="flex justify-between items-center w-full">
                            <x-button varient="primary" type="button" @click="nextStep()"
                                x-show="stepNumber < steps.length - 1">Next</x-button>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-3 pt-4">
                        <button type="button" @click="closeModal()"
                            class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg hover:bg-[#ab5f00]"
                            id="save_product" x-text="buttonText">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<div id="deleteProductModal" x-data="{ open: false, deleteId: null }">
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Confirm Delete</h2>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this product?</p>
                <div class="flex justify-end gap-3">
                    <button @click="open = false"
                        class="px-4 py-1 border rounded-lg hover:bg-gray-100">Cancel</button>
                    <button @click="deleteProduct(deleteId)"
                        class="px-4 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    </template>
</div>



