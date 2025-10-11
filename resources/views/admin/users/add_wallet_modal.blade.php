<div id="addWalletModal" 
    x-data="{
        open: false,
        form: {
            type: '',
            amount: '',
            description: '',
        },
        closeModal() {
            this.open = false;
            this.form = {
                type: '',
                amount: '',
                description: '',
            };
        },
    }" 
    x-cloak
>
    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

            <!-- Smaller Modal Box -->
            <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-md relative z-50">
                <h2 class="text-xl font-bold mb-4 text-gray-800" id="product_label">Add / Deduct</h2>

                <form id="walletAddForm" enctype="multipart/form-data" novalidate class="flex flex-col space-y-5">
                    @csrf
                    <x-input name="user_id" value="{{ request()->id }}" type="hidden"/>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <x-label>Type</x-label>
                            <x-select x-model="form.type" name="type" id="type" required>
                                <option value="" selected disabled>Please Select Type</option>
                                <option value="credit">Credit</option>
                                <option value="debit">Debit</option>
                            </x-select>
                        </div>

                        <div>
                            <x-label>Amount</x-label>
                            <x-input name="amount" id="amount" placeholder="eg .., 500.00" type="number"
                                step="0.01" x-model="form.amount" required />
                        </div>

                        <div>
                            <x-label>Description</x-label>
                            <x-textarea placeholder="Enter Description" name="description"
                                x-model="form.description" rows="3"></x-textarea>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal()"
                            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                        <button type="submit"
                            class="bg-[#ab5f00] text-white px-4 py-2 rounded-lg hover:bg-[#924f00]">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
