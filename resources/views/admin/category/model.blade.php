<!-- Add Category Modal -->
<template x-if="open">
    <div x-show="open"
         class="fixed inset-0 flex items-center justify-center z-50"
         x-data="{ previewUrl: null }">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40" @click="open = false"></div>

        <!-- Modal Box -->
        <div class="bg-white p-8 rounded-2xl shadow-2xl w-[600px] max-w-[90%] relative z-10">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Add Category</h2>

            <form id="categoryForm" class="space-y-6">
                <!-- Name + Status in same row -->
                <div class="flex items-center gap-3">
                    <!-- Category Name -->
                    <div class="w-full">
                        <label class="block text-gray-700 font-medium mb-2">Category Name</label>
                        <input type="text" placeholder="Enter category name"
                               class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>

                    <!-- Status Dropdown -->
                    <div class="w-full">
                        <label class="block text-gray-700 font-medium mb-2">Status</label>
                        <select class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Category Image -->
                <div x-data="{ previewUrl: null }">
                    <label class="block text-gray-700 font-medium mb-2">Category Image</label>

                    <input
                        type="file"
                        accept="image/*"
                        x-ref="fileInput"
                        @change="
            const file = $refs.fileInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => { previewUrl = e.target.result }
                reader.readAsDataURL(file);
            }
        "
                        class="block w-full text-sm text-gray-700 border p-3 border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring-2
               focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
               file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                    >

                    <div class="mt-4 flex justify-center overflow-hidden">
                        <img :src="previewUrl" x-show="previewUrl"
                             class="w-full max-h-[30vh] rounded-lg border border-gray-300 shadow-md object-cover" />
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="open = false"
                            class="px-5 py-3 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                    <button type="submit"
                            class="bg-blue-600 text-white px-5 py-3 rounded-lg hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</template>
