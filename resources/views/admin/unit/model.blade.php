<div id="unitModal" x-data="{ form: { unit_name: '', unit_status: '1', unit_short_name: '', unit_id: 0 } }"
     class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="absolute inset-0 bg-black/40" onclick="$('#unitModal').hide()"></div>
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-[600px] max-w-[90%] relative z-10">
        <h2 class="text-2xl font-bold mb-6 text-gray-800" id="unit_label">Add Unit</h2>
        <form id="unitForm" class="space-y-6">
            <input type="hidden" name="unit_id"  x-model="form.unit_id" id="unit_id" />
            <!-- Name + Status -->
            <div class="flex items-center gap-3">
                <div class="w-full">
                    <label class="block text-gray-700 font-medium mb-2">Unit Name</label>
                    <input type="text" name="unit_name" id="unit_name"
                           x-model="form.unit_name"
                           placeholder="Enter Unit Name"
                           class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#FF6A00]">
                </div>
                <div class="w-full">
                    <label class="block text-gray-700 font-medium mb-2">Short Name</label>
                    <input type="text" name="unit_short_name" id="unit_short_name"
                           x-model="form.unit_short_name"
                           placeholder="Enter Unit Short Name"
                           class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#FF6A00]">
                </div>
                <div class="w-full">
                    <label class="block text-gray-700 font-medium mb-2">Status</label>
                    <select name="unit_status" x-model="form.unit_status"  id="unit_status"
                            class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#FF6A00]">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <!-- Buttons -->
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="$('#unitModal').hide()"
                        class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                <button type="submit"
                        class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg hover:bg-[#ab5f00]" id="save_unit">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<div id="deleteUnitModal" x-data="{ open: false, deleteId: null }">
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Confirm Delete</h2>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this unit?</p>
                <div class="flex justify-end gap-3">
                    <button @click="open = false"
                        class="px-4 py-1 border rounded-lg hover:bg-gray-100">Cancel</button>
                    <button @click="deleteUnit(deleteId)"
                        class="px-4 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    </template>
</div>

