<!-- WALLET MODAL -->
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
                        <button type="submit" class="bg-[#ab5f00] text-white px-4 py-2 rounded-lg hover:bg-amber-500">
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
                        <button type="submit" x-text="buttonText" :disabled="accountMismatch"
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
                        <button type="button" @click="closeModal()"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
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



<!-- Load once in your layout head (only once) -->

<div id="modifySubscriptionModal" x-data="modifySubscription(
    '{{ $userSubscription->start_date ?? '' }}',
    '{{ $userSubscription->end_date ?? '' }}',
    @js($cancelled_date)
)" x-init="init()" x-cloak
    @keydown.escape.window="closeModal()">

    <div x-show="open" x-transition.opacity class="fixed inset-0 flex items-center justify-center z-50"
        style="display: none;">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

        <!-- Modal -->
        <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-2xl relative z-50 overflow-visible">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Modify Subscription</h2>

            <form id="modifySubscriptionForm" class="flex flex-col space-y-5" novalidate>
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
                    <label class="block text-sm font-medium text-gray-700">Description <span
                            class="text-red-500">*</span></label>
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

<script>
    window.modifySubscription = function(startDate, validDate, cancelledDates = []) {
        return {
            open: false,
            fp: null,
            minDate: startDate,
            maxDate: validDate,
            cancelledDates: Array.isArray(cancelledDates) ? cancelledDates : [],
            form: {
                start_date: '',
                end_date: '',
                description: '',
            },

            init() {
                this.$watch('open', (isOpen) => {
                    if (isOpen) this.$nextTick(() => this.initDatePicker());
                    else this.destroyPicker();
                });
            },

            openModal() {
                this.open = true;

                // Preselect only if there is exactly ONE cancelled range
                if (this.cancelledDates.length === 1) {
                    this.form.start_date = this.cancelledDates[0].start_date;
                    this.form.end_date = this.cancelledDates[0].end_date;
                }
            },

            closeModal() {
                this.open = false;
            },

            initDatePicker() {
                const input = this.$refs.dateInput;
                const container = this.$refs.calendarContainer;
                if (!input || !container) return;

                this.destroyPicker();

                const highlightRanges = this.cancelledDates.map(r => ({
                    from: r.start_date,
                    to: r.end_date
                }));

                this.fp = flatpickr(input, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'Y-m-d',
                    static: true,
                    appendTo: container,
                    minDate: this.minDate,
                    maxDate: this.maxDate,
                    disable: highlightRanges,

                    defaultDate: this.cancelledDates.length === 1 ?
                        [this.cancelledDates[0].start_date, this.cancelledDates[0].end_date] :
                        null,

                    onDayCreate: (dObj, dStr, fp, dayElem) => {
                        const d = dayElem.dateObj;
                        const ymd =
                            `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;

                        if (highlightRanges.some(r => ymd >= r.from && ymd <= r.to)) {
                            dayElem.style.background = '#ef4444';
                            dayElem.style.color = 'white';
                            dayElem.style.borderRadius = '6px';
                            dayElem.title = 'Cancelled';
                        }
                    },

                    onChange: (dates) => {
                        if (dates.length === 2) {
                            this.form.start_date = this.formatYMD(dates[0]);
                            this.form.end_date = this.formatYMD(dates[1]);
                        }
                    }
                });
            },

            destroyPicker() {
                if (this.fp) this.fp.destroy();
                this.fp = null;
            },

            formatYMD(dt) {
                const y = dt.getFullYear();
                const m = String(dt.getMonth() + 1).padStart(2, '0');
                const d = String(dt.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }
        };
    }
</script>
