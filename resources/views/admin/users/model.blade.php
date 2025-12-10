    <div id="revokeSubscriptionModal" x-cloak>
        <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
            <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-2xl relative z-50">
                <h2 class="text-2xl font-semibold mb-4">Revoke</h2>
                <form id="revokeForm" class="flex flex-col space-y-5">
                    @csrf
                    <input type="hidden" name="sub_id" :value="selectedId">
                    <input type="hidden" name="user_id" :value="selectedUserId">
                    <input type="hidden" name="subscription_id" :value="userSubscriptionId">
                    <h2>Revoke the day and reduce the validity period</h2>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="open = false"
                                class="px-4 py-2 border rounded-lg">Cancel</button>
                        <button type="submit" class="bg-[#ab5f00] text-white px-4 py-2 rounded-lg">Change</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<div id="changeDeliveryBoy" x-data="{
    open: false,
    form: { delivery_boy: '', description: '' },
    closeModal() {
        this.open = false;
        this.form = { delivery_boy: '', description: '' };
    }
}" x-cloak>
    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-2xl relative z-50">
                <h2 class="text-2xl font-semibold mb-4 text-gray-800">Change Delivery Partner</h2>
                  <form id="changeDeliveryForm" enctype="multipart/form-data" novalidate class="flex flex-col space-y-5">
                {{-- <form id="changeDeliveryForm" enctype="multipart/form-data"  class="flex flex-col space-y-5"> --}}
                    @csrf
                    {{-- <x-input name="user_id" value="{{ request()->id }}" type="hidden" /> --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label>Delivery Partner<span class="text-red-500">*</span></x-label>
                            <x-select x-model="form.delivery_boy" name="delivery_boy" id="delivery_boy" required>
                                <option value="" disabled>Please Select Delivery Boy</option>
                                @foreach ($delivery_boy as $boy)
                                    <option value="{{ $boy->id }}">{{ $boy->name }}</option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>
                    <div>
                        <x-label>Description<span class="text-red-500">*</span></x-label>
                        <x-textarea x-model="form.description" placeholder="Enter Description" name="description" required
                            id="description" rows="3"></x-textarea>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal()"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" class="bg-[#ab5f00] text-white px-4 py-2 rounded-lg hover:bg-amber-500">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>


