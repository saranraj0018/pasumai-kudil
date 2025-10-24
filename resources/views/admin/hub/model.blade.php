
    <div id="hub_model"
         class="fixed inset-0 flex items-center justify-center z-50 overflow-auto bg-black/40 p-4" style="display:none;">

        <!-- Modal Container -->
        <div class="bg-white p-8 rounded-3xl shadow-2xl w-1/2 relative z-10">
            <h2 class="text-2xl font-bold mb-6 text-gray-800" id="hub_title">Add Hub</h2>
            <form id="hub_form" class="space-y-6">
                @csrf
                <input type="hidden" name="hub_id" id="hub_id">
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">City Name</label>
                        <input type="text" name="hub_name" id="hub_name"
                               class="form-input w-full border rounded-lg p-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" id="type"
                                class="block w-full rounded-lg border border-gray-300 bg-white p-2 text-gray-700 focus:border-[#ab5f00] focus:ring focus:ring-[#ab5f00]/30">
                            <option disabled selected>Select Type</option>
                            <option value="1">Grocery</option>
                            <option value="2">Milk</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Map Location</label>
                    <div id="hub_map" style="height: 300px; width: 100%;"></div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" id="cancel_hub_Modal"
                            class="px-5 py-2 border rounded-lg hover:bg-gray-100">Cancel</button>
                    <button type="submit" id="save_hub"
                            class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg hover:bg-[#9c5200]">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="delete_hub_modal" style="display:none">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4">Confirm Delete</h2>
                <p class="mb-6">Are you sure you want to delete this City?</p>
                <div class="flex justify-end gap-3">
                    <button id="cancel_hub_btn" class="px-4 py-1 border rounded-lg">Cancel</button>
                    <button id="delete_hub_Btn" class="px-4 py-1 bg-red-600 text-white rounded-lg">Delete</button>
                </div>
            </div>
        </div>
    </div>
