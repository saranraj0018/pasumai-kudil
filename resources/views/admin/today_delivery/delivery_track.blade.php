<div id="stockmaintainModal" x-data="{
    open: false,
    previewUrl: null,
    exiting_image: '',
    form: {
        status: '',
        image: null,
        delivery_boy: ''
    },
    closeModal() {
        this.open = false;
        this.form = {
            status: '',
            image: null,
            delivery_boy: ''
        };
    }
}" x-cloak>
    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-[90%] relative z-50">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Stock Maintain</h2>
                <form id="deliveryTrackForm" enctype="multipart/form-data" novalidate class="flex flex-col justify-start items-start w-full">
                    @csrf
                    <input type="hidden" name="delivery_partner_id"  x-model="form.delivery_partner_id" id="delivery_partner_id">
                    <div class="p-5 space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-label>Extra Quantity</x-label>
                                <input type="text" name="extra_quantity" id="extra_quantity" x-model="form.extra_quantity"  class="form-input w-full border rounded-lg p-2">
                            </div>
                            <div>
                                <x-label>Damage Quantity</x-label>
                                <input type="text" name="damage_quantity" id="damage_quantity" x-model="form.damage_quantity"  class="form-input w-full border rounded-lg p-2">
                            </div>
                            <div>
                                <x-label>Returned Quantity</x-label>
                                <input type="text" name="returned_quantity" id="returned_quantity" x-model="form.returned_quantity"  class="form-input w-full border rounded-lg p-2">
                            </div>
                        </div>
                    </div>
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
