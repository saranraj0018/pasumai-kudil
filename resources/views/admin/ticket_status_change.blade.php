<div id="editTicketStatusModal" x-data="{
    open: false,
    form: {
        status: '',
    },
    closeModal() {
        this.open = false;
        this.form = {
            status: ''
        };
    }
}" x-cloak>
    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-[50%] relative z-50">
                <h2 class="text-2xl font-bold text-gray-800">Ticket Status Change</h2>
                <form id="ticketstatusChangeForm" enctype="multipart/form-data" novalidate class="flex flex-col justify-start items-start w-full  h-[25vh]">
                    @csrf
                    <input type="hidden" name="ticket_id" x-model="form.ticket_id" id="ticket_id" />
                    <div class="p-5 space-y-3">
                        {{-- Step 1: Product Information --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-label>Status</x-label>
                                <x-select x-model="form.status" name="status" id="status" required>
                                    <option value="" selected disabled>Please Select Status</option>
                                    <option value="2">Closed</option>
                                    <option value="3">Rejected</option>
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
                        <button type="submit" class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg hover:bg-[#ab5f00]">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
