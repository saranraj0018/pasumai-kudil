<x-layouts.app>

    <div x-data="{
        products: [],
        showModal: true,
        modalTitle: '',
        form: {
            id: null,
            name: '',
            image: '',
            description: '',
            benefits: '',
        },

        init() {
            this.fetchProducts();
        },

        fetchProducts() {
            // Fetch products from backend
            fetch('/api/products')
                .then(res => res.json())
                .then(data => this.products = data);
        },

        openCreateModal() {
            this.modalTitle = 'Create Product';
            this.form = { id: null, name: '', image: '', description: '', benefits: '' };
            this.showModal = true;
        },

        editProduct(product) {
            this.modalTitle = 'Edit Product';
            this.form = { ...product };
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
        },

        submitForm() {
            if (this.form.id) {
                this.updateProduct();
            } else {
                this.createProduct();
            }
        },

        createProduct() {
            fetch('/api/products', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.form)
            })
            this.closeModal();
            this.fetchProducts();
        },

        updateProduct() {
            fetch(`/api/products/${this.form.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.form)
            })
            this.closeModal();
            this.fetchProducts();
        },

        deleteProduct(id) {
            if (!confirm('Are you sure?')) return;
            fetch(`/api/products/${id}`, { method: 'DELETE' })
            this.fetchProducts()
        }
    }" x-init="init()">

<div class="max-w-7xl mx-auto p-6">

    {{-- Create Product Button --}}
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold mb-6 text-[#d98c33]">Product Management</h1>
        <x-button
            @click="openCreateModal()">
            Create Product
        </x-button>
    </div>

    {{-- Product Table --}}
    <div class="bg-white shadow rounded overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#d98c33]/20">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-800">Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-800">Image</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-800">Description</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-800">Benefits</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-800">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-for="product in products" :key="product.id">
                    <tr>
                        <td class="px-6 py-4 text-sm" x-text="product.name"></td>
                        <td class="px-6 py-4 text-sm">
                            <img :src="product.image" alt="Image" class="h-12 w-12 object-cover rounded">
                        </td>
                        <td class="px-6 py-4 text-sm" x-text="product.description"></td>
                        <td class="px-6 py-4 text-sm" x-text="product.benefits"></td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button @click="editProduct(product)" class="px-3 py-1 bg-blue-500 text-white rounded">Edit</button>
                            <button @click="deleteProduct(product.id)" class="px-3 py-1 bg-red-500 text-white rounded">Delete</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Modal for Create/Edit --}}
    <div x-show="showModal" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
        @include('components.domains.products.product-form')
    </div>
</div>

    </div>
</x-layouts.app>
