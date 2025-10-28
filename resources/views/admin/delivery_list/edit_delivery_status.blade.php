<div id="editdeliveryListModal" x-data="{
    open: false,
    previewUrl: null,
    exiting_image: '',
    form: {
        status: '',
        image: null,
     },
    closeModal() {
        this.open = false;
        this.form = {
            status: '',
            image: null,
         };
    }
}" x-cloak>
    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <!-- Modal Box -->
            <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-[90%] relative z-50">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Edit Delivery</h2>
                <form id="deliverystatusChangeForm" enctype="multipart/form-data" novalidate
                    class="flex flex-col justify-start items-start w-full  h-[45vh] overflow-y-scroll">
                    @csrf
                    <input type="hidden" name="exiting_image" x-model="exiting_image" id="exiting_image" />
                    <input type="hidden" name="delivery_id" x-model="form.delivery_id"
                        id="delivery_id" />
                    <div class="p-5 space-y-3">
                        {{-- Step 1: Product Information --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-label>Status</x-label>
                                <x-select x-model="form.status" name="status" id="status" required>
                                    <option value="" selected disabled>Please Select Status</option>
                                        <option value="delivered">Delivered</option>
                                </x-select>
                            </div>
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
                    </div>
                    <!-- Buttons -->
                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 w-full">
                        <button type="button" @click="closeModal()"
                            class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg hover:bg-[#ab5f00]"
                            id="save_product">
                            Save
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </template>
</div>
