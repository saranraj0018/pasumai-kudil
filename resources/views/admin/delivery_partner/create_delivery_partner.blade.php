<div id="deliveryPartnerCreateModal" x-data="{
    open: false,
    form: {
        name: '',
        mobile_number: '',
        hub_id: '',
    },
    closeModal() {
        this.open = false;
        this.form = {
            name: '',
            mobile_number: '',
            hub_id: '',
        };
    }
}" x-cloak>
    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <!-- Modal Box -->
            <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-[90%] relative z-50">

                <h2 class="text-2xl font-bold mb-6 text-gray-800" x-text="modalTitle">Add Delivery
                    Partner</h2>
                <form id="deliveryPartnerAddForm" enctype="multipart/form-data" novalidate
                    class="flex flex-col justify-start items-start w-full  h-[35vh] overflow-y-scroll">
                    @csrf
                    <input type="hidden" name="delivery_partner_id" x-model="form.delivery_partner_id"
                        id="delivery_partner_id" />
                    <div class="p-5 space-y-3">
                        {{-- Step 1: Product Information --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-label>Name<span class="text-red-500">*</span></x-label>
                                <x-input type="text" x-model="form.name" name="name" id="name"
                                    placeholder="Enter Your Name" required />
                            </div>
                            <div>
                                <x-label>Mobile Number<span class="text-red-500">*</span></x-label>
                                <x-input type="number" name="mobile_number" id="mobile_number"
                                    x-model="form.mobile_number" />
                            </div>
                            <div>
                                <x-label>Hub<span class="text-red-500">*</span></x-label>
                                <x-select x-model="form.hub_id" name="hub_id" id="hub_id" required>
                                    <option value="" selected disabled>Please Select Hub</option>
                                    @foreach ($hub as $h)
                                        <option value="{{ $h->id }}">{{ $h->name }}</option>
                                    @endforeach
                                </x-select>
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
                            id="save_product" x-text="buttonText">
                            Save
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </template>
</div>

<div id="deleteDeliveryPartnerModal" x-data="{ open: false, deleteId: null }">
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Confirm Delete</h2>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this delivery partner?</p>
                <div class="flex justify-end gap-3">
                    <button @click="open = false" class="px-4 py-1 border rounded-lg hover:bg-gray-100">Cancel</button>
                    <button @click="deleteDeliveryPartner(deleteId)"
                        class="px-4 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    </template>
</div>
