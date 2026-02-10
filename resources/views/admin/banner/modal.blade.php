<div id="bannerModal" x-data="{ previewUrl: null, existing_image: '', form: { type: 'main', banner_id: 0 } }" class="fixed inset-0 hidden items-center justify-center z-50">

    <div class="absolute inset-0 bg-black/40" @click="$('#bannerModal').hide()"></div>

    <div class="bg-white p-8 rounded-2xl shadow-2xl w-[600px] max-w-[90%] relative z-10">
        <h2 class="text-2xl font-bold mb-6" id="banner_label">Add Banner</h2>

        <form id="bannerForm" class="space-y-6">
            <input type="hidden" name="banner_id" x-model="form.banner_id" />
            <input type="hidden" name="existing_image" x-model="existing_image" id="existing_image"/>

            <div class="flex items-center gap-3">
                <div class="w-full">
                    <label>Type</label>
                    <select id="banner_type" name="type" x-model="form.type"
                        class="form-input w-full border p-2 rounded-lg">
                        <option value="GroceryMain">Grocery Main</option>
                        <option value="MilkMain">Milk Main</option>
                        <option value="MilkSub">Milk Sub</option>
                    </select>
                </div>
            </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Banner Image</label>
                    <input type="file" name="banner_image" id="banner_image" accept=".png, .jpg, .jpeg"
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

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="$('#bannerModal').hide()"
                        class="px-5 py-2 border rounded-lg">Cancel</button>
                    <button type="submit" class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg"
                        id="save_banner">Save</button>
                </div>
        </form>
    </div>
</div>

<div id="deleteBannerModal" x-data="{ open: false, deleteId: null }">
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4">Confirm Delete</h2>
                <p class="mb-6">Are you sure you want to delete this banner?</p>
                <div class="flex justify-end gap-3">
                    <button @click="open=false" class="px-4 py-1 border rounded-lg">Cancel</button>
                    <button @click="deleteBanner(deleteId)"
                        class="px-4 py-1 bg-red-600 text-white rounded-lg">Delete</button>
                </div>
            </div>
        </div>
    </template>
