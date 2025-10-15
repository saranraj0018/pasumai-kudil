<!-- WALLET MODAL -->
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

            <!-- Wider Modal Box -->
            <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-2xl relative z-50">
                <h2 class="text-2xl font-semibold mb-4 text-gray-800">Add / Deduct</h2>

                <form id="walletAddForm" enctype="multipart/form-data" novalidate class="flex flex-col space-y-5">
                    @csrf
                    <x-input name="user_id" value="{{ request()->id }}" type="hidden"/>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                    </div>

                    <div>
                        <x-label>Description</x-label>
                        <x-textarea placeholder="Enter Description" name="description"
                            x-model="form.description" rows="3"></x-textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal()"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit"
                            class="bg-[#ab5f00] text-white px-4 py-2 rounded-lg hover:bg-amber-500">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<!-- ACCOUNT MODAL -->
<div id="addAccountModal"
    x-data="{
        open: false,
        form: {
            account_holder_name: '',
            bank_name: '',
            account_number: '',
            confirm_account_number: '',
            ifsc_code: '',
        },
        closeModal() {
            this.open = false;
            this.form = {
                account_holder_name: '',
                bank_name: '',
                account_number: '',
                confirm_account_number: '',
                ifsc_code: '',
            };
        },
        get accountMismatch() {
            return this.form.account_number !== '' 
                && this.form.confirm_account_number !== '' 
                && this.form.account_number !== this.form.confirm_account_number;
        },
    }"
    x-cloak
>
    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

            <!-- Wider Modal Box -->
            <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-2xl relative z-50">
                <h2 class="text-2xl font-semibold mb-4 text-gray-800" x-text="modalTitle">Add Account Details</h2>

                <form id="accountAddForm" enctype="multipart/form-data" novalidate class="flex flex-col space-y-5">
                    @csrf
                     <x-input name="user_id" value="{{ request()->id }}" type="hidden"/>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label>Account Holder Name</x-label>
                            <x-input name="account_holder_name" id="account_holder_name"
                                placeholder="Enter Holder Name" type="text"
                                x-model="form.account_holder_name" required />
                        </div>

                        <div>
                            <x-label>Bank Name</x-label>
                            <x-input name="bank_name" id="bank_name"
                                placeholder="Enter Your Bank Name" type="text"
                                x-model="form.bank_name" required />
                        </div>

                        <div>
                            <x-label>Account Number</x-label>
                            <x-input name="account_number" id="account_number"
                                placeholder="Enter Your Account Number" type="text"
                                x-model="form.account_number" required />
                        </div>

                        <div>
                            <x-label>Confirm Account Number</x-label>
                            <x-input name="confirm_account_number" id="confirm_account_number"
                                placeholder="Re-enter Account Number" type="text"
                                x-model="form.confirm_account_number" required />
                            <p x-show="accountMismatch" class="text-red-600 text-sm mt-1">
                                Account numbers do not match.
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <x-label>IFSC Code</x-label>
                            <x-input name="ifsc_code" id="ifsc_code"
                                placeholder="Enter Your IFSC Code" type="text"
                                x-model="form.ifsc_code" required />
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal()"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" x-text="buttonText"
                            :disabled="accountMismatch"
                            class="bg-[#ab5f00] text-white px-4 py-2 rounded-lg hover:bg-amber-500 disabled:opacity-60 disabled:cursor-not-allowed">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
