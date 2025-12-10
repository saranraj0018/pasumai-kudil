<div id="rolesModal"
     x-data="{ form: { name: '' } }"
     class="fixed inset-0 hidden items-center justify-center z-50">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40" onclick="$('#rolesModal').hide()"></div>

    <!-- Modal Box -->
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-[600px] max-w-[90%] relative z-10">
        <h2 class="text-2xl font-bold mb-6 text-gray-800" id="role_label">Add Role</h2>

        <form id="roleForm" class="space-y-6">
            <input type="hidden" name="role_id"  x-model="form.role_id" id="role_id" />
             <!-- Name + Status -->
            <div class="flex items-center gap-3">
                <div class="w-full">
                    <label class="block text-gray-700 font-medium mb-2">Role Name<span class="text-red-500">*</span></label>
                    <input type="text" name="role_name" id="role_name"
                           x-model="form.name"
                           placeholder="Enter Role Name"
                           class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#ab5f00]">
                </div>
            </div>
            <!-- Buttons -->
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="$('#rolesModal').hide()"
                        class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                <button type="submit"
                        class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg hover:bg-[#ab5f00]" id="save_role">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>


