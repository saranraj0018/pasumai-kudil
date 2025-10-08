<div id="subscriptionModal" x-data="{ open: false }" x-show="open"
    class="fixed inset-0 flex items-center justify-center z-50" style="display:none">
    <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-[700px] max-w-[95%] relative z-10">
        <h2 class="text-2xl font-bold mb-6 text-gray-800" id="subscription_label">Add Subscription</h2>
        <form id="subscriptionForm" class="space-y-6">
            @csrf
            <input type="hidden" name="id" id="subscription_id">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label>Plan Pack</label>
                    <input type="text" name="plan_pack" id="plan_pack"
                        class="form-input w-full border rounded-lg p-2">
                </div>
                <div>
                    <label>Plan Type</label>
                    <input type="text" name="plan_type" id="plan_type"
                        class="form-input w-full border rounded-lg p-2">
                </div>
                <div>
                    <label>Plan Amount</label>
                    <input type="number" name="plan_amount" id="plan_amount"
                        class="form-input w-full border rounded-lg p-2">
                </div>
                <div>
                    <label>Duration</label>
                    <div class="flex gap-2">
                        <input type="number" name="min_duration" id="min_duration"
                            class="form-input w-1/3 border rounded-lg p-2" placeholder="Min">
                        <input type="number" name="max_duration" id="max_duration"
                            class="form-input w-1/3 border rounded-lg p-2" placeholder="Max">
                        <select name="plan_duration_unit" id="plan_duration_unit"
                            class="form-input w-1/3 border rounded-lg p-2">
                            <option value="">Select Unit</option>
                            <option value="days">Days</option>
                            <option value="months">Months</option>
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <label>Plan Details</label>
                <textarea name="plan_details" id="plan_details" class="form-input w-full border rounded-lg p-2"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="open=false" class="px-5 py-2 border rounded-lg">Cancel</button>
                <button type="submit" id="save_subscription"
                    class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteSubscriptionModal" x-data="{ open: false, deleteId: null }">
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4">Confirm Delete</h2>
                <p class="mb-6">Are you sure you want to delete this subscription?</p>
                <div class="flex justify-end gap-3">
                    <button @click="open=false" class="px-4 py-1 border rounded-lg">Cancel</button>
                    <button @click="deleteSubscription(deleteId)"
                        class="px-4 py-1 bg-red-600 text-white rounded-lg">Delete</button>
                </div>
            </div>
        </div>
    </template>
</div>
