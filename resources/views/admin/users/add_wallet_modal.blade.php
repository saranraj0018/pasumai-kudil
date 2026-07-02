<!-- WALLET MODAL -->
<style>
.flatpickr-day.marked-date {
    background: #22c55e !important;
    color: #fff !important;
    border-color: #22c55e !important;
}
.flatpickr-day.marked-date:hover {
    background: #16a34a !important;
    border-color: #16a34a !important;
}
</style>
<div id="addWalletModal" x-data="{
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
}" x-cloak>
    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

            <!-- Wider Modal Box -->
            <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-2xl relative z-50">
                <h2 class="text-2xl font-semibold mb-4 text-gray-800">Add / Deduct</h2>

                <form id="walletAddForm" enctype="multipart/form-data" novalidate class="flex flex-col space-y-5">
                    @csrf
                    <x-input name="user_id" value="{{ request()->id }}" type="hidden" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label>Type<span class="text-red-500">*</span></x-label>
                            <x-select x-model="form.type" name="type" id="type" required>
                                <option value="" selected disabled>Please Select Type</option>
                                <option value="credit">Credit</option>
                                <option value="debit">Debit</option>
                            </x-select>
                        </div>

                        <div>
                            <x-label>Amount<span class="text-red-500">*</span></x-label>
                            <x-input name="amount" id="amount" placeholder="eg .., 500.00" type="number"
                                step="0.01" x-model="form.amount" required />
                        </div>
                    </div>

                    <div>
                        <x-label>Description</x-label>
                        <x-textarea placeholder="Enter Description" name="description" x-model="form.description"
                            rows="3"></x-textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal()"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" class="bg-[#ab5f00] text-white px-4 py-2 rounded-lg hover:bg-amber-500" id="save_wallet">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<!-- ACCOUNT MODAL -->
<div id="addAccountModal" x-data="{
    open: false,
    form: {
        account_holder_name: '',
        bank_name: '',
        account_number: '',
        confirm_account_number: '',
        ifsc_code: '',
        upi: ''
    },
    closeModal() {
        this.open = false;
        this.form = {
            account_holder_name: '',
            bank_name: '',
            account_number: '',
            confirm_account_number: '',
            ifsc_code: '',
            upi: ''
        };
    },
    get accountMismatch() {
        return this.form.account_number !== '' &&
            this.form.confirm_account_number !== '' &&
            this.form.account_number !== this.form.confirm_account_number;
    },
}" x-cloak>

    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

            <!-- Wider Modal Box -->
            <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-2xl relative z-50">
                <h2 class="text-2xl font-semibold mb-4 text-gray-800" x-text="modalTitle">Add Account Details</h2>

                <form id="accountAddForm" enctype="multipart/form-data" novalidate class="flex flex-col space-y-5">
                    @csrf
                    <x-input name="user_id" value="{{ request()->id }}" type="hidden" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label>Account Holder Name<span class="text-red-500">*</span></x-label>
                            <x-input name="account_holder_name" id="account_holder_name" placeholder="Enter Holder Name"
                                type="text" x-model="form.account_holder_name" required />
                        </div>

                        <div>
                            <x-label>Bank Name<span class="text-red-500">*</span></x-label>
                            <x-input name="bank_name" id="bank_name" placeholder="Enter Your Bank Name" type="text"
                                x-model="form.bank_name" required />
                        </div>

                        <div>
                            <x-label>Account Number<span class="text-red-500">*</span></x-label>
                            <x-input name="account_number" id="account_number" placeholder="Enter Your Account Number"
                                type="text" x-model="form.account_number" required />
                        </div>

                        <div>
                            <x-label>Confirm Account Number<span class="text-red-500">*</span></x-label>
                            <x-input name="confirm_account_number" id="confirm_account_number"
                                placeholder="Re-enter Account Number" type="text"
                                x-model="form.confirm_account_number" required />
                            <p x-show="accountMismatch" class="text-red-600 text-sm mt-1">
                                Account numbers do not match.
                            </p>
                        </div>

                        <div>
                            <x-label>IFSC Code<span class="text-red-500">*</span></x-label>
                            <x-input name="ifsc_code" id="ifsc_code" placeholder="Enter Your IFSC Code" type="text"
                                x-model="form.ifsc_code" required />
                        </div>

                        <div>
                            <x-label>UPI Number</x-label>
                            <x-input name="upi" id="upi" placeholder="Enter Your UPI Number" type="text"
                                x-model="form.upi" />
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal()"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" x-text="buttonText" :disabled="accountMismatch" id="save_account"
                            class="bg-[#ab5f00] text-white px-4 py-2 rounded-lg hover:bg-amber-500 disabled:opacity-60 disabled:cursor-not-allowed">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<div id="addSubscriptionModal" x-data="{
    open: false,
    form: {
        status: '',
        description: '',
    },
    closeModal() {
        this.open = false;
        this.form = {
            status: '',
            description: '',
        };
    },
}" x-cloak>
    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

            <!-- Wider Modal Box -->
            <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-2xl relative z-50">
                <h2 class="text-2xl font-semibold mb-4 text-gray-800">Subscription Cancel</h2>

                <form id="subscriptionCancelForm" enctype="multipart/form-data" novalidate
                    class="flex flex-col space-y-5">
                    @csrf
                    <x-input name="user_id" value="{{ request()->id }}" type="hidden" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label>Status<span class="text-red-500">*</span></x-label>
                            <x-select x-model="form.status" name="status" id="status" required>
                                <option value="" selected disabled>Please Select Status</option>
                                <option value="2">In Active</option>
                            </x-select>
                        </div>
                    </div>

                    <div>
                        <x-label>Description<span class="text-red-500">*</span></x-label>
                        <x-textarea placeholder="Enter Description" name="description" id="description" required
                            x-model="form.description" rows="3"></x-textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" id="save_subscription"
                            class="bg-[#ab5f00] text-white px-4 py-2 rounded-lg hover:bg-amber-500">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>


<div id="modifySubscriptionModal" x-data="modifySubscription(
        '{{ $userSubscription->start_date ?? '' }}',
        '{{ $userSubscription->end_date ?? '' }}',
        @js($cancelledRanges ?? []),
        @js($deliveredDates ?? [])
    )" x-init="init()" x-cloak
    @keydown.escape.window="closeModal()">

    <div x-show="open" x-transition.opacity class="fixed inset-0 flex items-center justify-center z-50"
        style="display: none;">
        <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

        <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-2xl relative z-50 overflow-visible">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Modify Subscription</h2>

            <form id="modifySubscriptionForm" class="flex flex-col space-y-5" novalidate @submit.prevent="submitForm()">
                @csrf
                <input type="hidden" name="user_id" value="{{ request()->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Cancel Date <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="date_range" x-ref="dateInput" placeholder="Select date range"
                            class="w-full border rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-green-500"
                            readonly aria-label="Date range" @click="initDatePicker()" />
                        <div x-ref="calendarContainer"></div>
                    </div>
                </div>

                <input type="hidden" name="start_date" :value="form.start_date">
                <input type="hidden" name="end_date" :value="form.end_date">

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description <span class="text-red-500">*</span></label>
                    <textarea name="description" id="description" x-model="form.description" rows="3"
                        class="w-full border rounded-lg px-3 py-2 mt-1"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="closeModal()"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">Cancel</button>
                    <button type="submit" id="save_modify"
                        class="bg-[#ab5f00] text-white px-4 py-2 rounded-lg hover:bg-amber-500">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
.flatpickr-day.delivered-date {
    background: #22c55e !important;
    color: #fff !important;
    border-color: #22c55e !important;
    cursor: not-allowed !important;
}
.flatpickr-day.cancelled-date {
    background: #ef4444 !important;
    color: #fff !important;
    border-color: #ef4444 !important;
    cursor: not-allowed !important;
}
.flatpickr-day.flatpickr-disabled.delivered-date,
.flatpickr-day.flatpickr-disabled.cancelled-date {
    opacity: 0.6;
}
</style>

<script>
// Alpine v2 requires a plain global function referenced directly in x-data — no Alpine.data(), no alpine:init event.
function modifySubscription(startDate, endDate, cancelledRanges, deliveredDates) {
    return {
        open: false,
        flatpickrInstance: null,
        form: {
            start_date: '',
            end_date: '',
            description: '',
        },
        disabledDates: [],
        cancelledDates: [],
        deliveredDates: [],

        init() {
            this.deliveredDates = Array.isArray(deliveredDates) ? deliveredDates : [];
            this.cancelledDates = Array.isArray(cancelledRanges)
                ? cancelledRanges.flatMap(r => this.expandRange(r.start_date, r.end_date))
                : [];

            this.disabledDates = [...new Set([...this.cancelledDates, ...this.deliveredDates])];
        },

        // Expand a {start_date,end_date} pair into individual 'Y-m-d' strings (inclusive)
        expandRange(start, end) {
            const dates = [];
            let current = new Date(start);
            const last = new Date(end);
            while (current <= last) {
                dates.push(this.formatDate(current));
                current.setDate(current.getDate() + 1);
            }
            return dates;
        },

        formatDate(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        },

        openModal() {
            this.open = true;
        },

        closeModal() {
            this.open = false;
            if (this.flatpickrInstance) {
                this.flatpickrInstance.destroy();
                this.flatpickrInstance = null;
            }
            this.form.start_date = '';
            this.form.end_date = '';
        },

        initDatePicker() {
            const inputEl = document.getElementById('date_range');
            if (!inputEl) return;

            if (this.flatpickrInstance) {
                this.flatpickrInstance.open();
                return;
            }

            const self = this;

            this.flatpickrInstance = flatpickr(inputEl, {
                mode: "range",
                dateFormat: "Y-m-d",
                allowInput: true,
                minDate: startDate || null,
                maxDate: endDate || null,
                disable: self.disabledDates,

                onDayCreate: function (dObj, dStr, fp, dayElem) {
                    const cellDate = self.formatDate(dayElem.dateObj);
                    if (self.cancelledDates.includes(cellDate)) {
                        dayElem.classList.add('cancelled-date');
                    } else if (self.deliveredDates.includes(cellDate)) {
                        dayElem.classList.add('delivered-date');
                    }
                },

                onChange: function (selectedDates, dateStr, instance) {
                    if (selectedDates.length !== 2) return;

                    const rangeStart = self.formatDate(selectedDates[0]);
                    const rangeEnd = self.formatDate(selectedDates[1]);
                    const rangeDates = self.expandRange(rangeStart, rangeEnd);

                    const blocked = rangeDates.filter(d => self.disabledDates.includes(d));

                    if (blocked.length > 0) {
                        alert('Selected range includes already delivered or cancelled date(s): ' + blocked.join(', '));
                        instance.clear();
                        self.form.start_date = '';
                        self.form.end_date = '';
                        return;
                    }

                    self.form.start_date = rangeStart;
                    self.form.end_date = rangeEnd;
                },
            });

            this.flatpickrInstance.open();
        },

        submitForm() {
            if (!this.form.start_date || !this.form.end_date) {
                alert('Please select a date range.');
                return;
            }
            if (!this.form.description) {
                alert('Description is required.');
                return;
            }
            this.$el.submit();
        },
    };
}
</script>
