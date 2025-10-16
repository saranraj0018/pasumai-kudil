
    <div id="hub_model" x-data="{ open: false }" x-show="open"
         class="fixed inset-0 flex items-center justify-center z-50 overflow-auto bg-black/40 p-4" style="display:none;">

        <!-- Modal Container -->
        <div class="bg-white p-8 rounded-3xl shadow-2xl w-1/2 relative z-10">
            <h2 class="text-2xl font-bold mb-6 text-gray-800" id="subscription_title">Add Hub</h2>
            <form id="subscriptionForm" class="space-y-6">
                @csrf
                <input type="hidden" name="id" id="subscription_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Plan Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status"
                                class="block w-full rounded-lg border border-gray-300 bg-white p-2 text-gray-700 focus:border-[#ab5f00] focus:ring focus:ring-[#ab5f00]/30">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hub Name</label>
                        <input type="text" name="hub_name" id="hub_name"
                               class="form-input w-full border rounded-lg p-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Map Location</label>
                    <div id="hub_map" style="height: 300px; width: 100%;"></div>
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
