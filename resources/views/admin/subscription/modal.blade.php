<!-- Subscription Modal -->
<div id="subscriptionModal" x-data="{ open: false }" x-show="open"
    class="fixed inset-0 flex items-center justify-center z-50 overflow-auto bg-black/40 p-4" style="display:none;">
    
    <!-- Modal Container -->
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-3xl md:max-w-4xl lg:max-w-5xl relative z-10">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Add Subscription</h2>
        <form id="subscriptionForm" class="space-y-6">
            @csrf
            <input type="hidden" name="id" id="subscription_id">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Plan Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan Type</label>
                    <select name="plan_type" id="plan_type"
                        class="block w-full rounded-lg border border-gray-300 bg-white p-2 text-gray-700 focus:border-[#ab5f00] focus:ring focus:ring-[#ab5f00]/30">
                        <option value="">Select Plan Type</option>
                        <option value="Basic">Basic</option>
                        <option value="Best Value">Best Value</option>
                        <option value="Customize">Customize</option>
                    </select>
                </div>
 
                <!-- Plan Pack -->
                <div id="plan_pack_container">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan Pack</label>
                    <div class="flex items-center border rounded-lg overflow-hidden">
                        <input type="number" name="plan_pack" id="plan_pack"
                            class="w-full p-2 outline-none border-0" placeholder="Enter number">
                        <span class="bg-gray-100 px-3 py-2 text-gray-700 text-sm border-l">Month</span>
                    </div>
                </div>

                <!-- Delivery Days -->
                <div id="delivery_days_container" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Days</label>
                    <div class="flex items-center border rounded-lg overflow-hidden">
                        <input type="number" id="delivery_days_input" placeholder="Enter number of days"
                            class="w-full p-2 outline-none border-0">
                        <button type="button" id="add_delivery_day_btn"
                            class="ml-2 px-3 py-1 bg-[#ab5f00] text-white rounded">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <div id="delivery_days_list" class="mt-2"></div>
                </div>

                <!-- Plan Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan Amount</label>
                    <input type="number" name="plan_amount" id="plan_amount"
                        class="form-input w-full border rounded-lg p-2">
                </div>

                <!-- Duration -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                    <div class="flex items-center border rounded-lg overflow-hidden">
                        <input type="number" name="plan_duration" id="plan_duration"
                            class="w-full p-2 outline-none border-0" placeholder="Enter duration">
                        <span class="bg-gray-100 px-3 py-2 text-gray-700 text-sm border-l">Days</span>
                    </div>
                </div>
            </div>

            <!-- Plan Details -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Plan Details</label>
                <textarea name="plan_details" id="plan_details"
                    class="form-input w-full border rounded-lg p-2"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <input type="number" name="quantity" id="quantity"
                        class="form-input w-full border rounded-lg p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pack</label>
                    <input type="text" name="pack" id="pack"
                        class="form-input w-full border rounded-lg p-2">
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" id="cancelSubscriptionModal"
                    class="px-5 py-2 border rounded-lg hover:bg-gray-100">Cancel</button>
                <button type="submit" id="save_subscription"
                    class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg hover:bg-[#9c5200]">Save</button>
            </div>
        </form>
    </div>
</div>


<div id="deleteSubscriptionModal" style="display:none">
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
            <h2 class="text-lg font-bold mb-4">Confirm Delete</h2>
            <p class="mb-6">Are you sure you want to delete this subscription?</p>
            <div class="flex justify-end gap-3">
                <button id="cancelDeleteBtn" class="px-4 py-1 border rounded-lg">Cancel</button>
                <button id="confirmDeleteBtn" class="px-4 py-1 bg-red-600 text-white rounded-lg">Delete</button>
            </div>
        </div>
    </div>
</div>
