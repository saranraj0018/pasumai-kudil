<div id="faqModal" x-data="{ form: { question: '', answer: '', faq_id: 0 , sort_order: '', faq_status: '1' } }" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="absolute inset-0 bg-black/40" onclick="$('#faqModal').hide()"></div>
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-[600px] max-w-[90%] relative z-10">
        <h2 class="text-2xl font-bold mb-6 text-gray-800" id="faq_label">Add FAQ</h2>
        <form id="faqForm" class="space-y-6">
            <input type="hidden" name="faq_id" x-model="form.faq_id" id="faq_id" />
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Question<span class="text-red-500">*</span></label>
                    <input type="text" name="question" id="faq_question" x-model="form.question"
                        placeholder="Enter Question"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#FF6A00]">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Answer<span class="text-red-500">*</span></label>
                    <textarea name="answer" id="faq_answer" x-model="form.answer" rows="4" placeholder="Enter Answer"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#FF6A00]"></textarea>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Sort Order<span class="text-red-500">*</span></label>
                    <input type="number" name="sort_order" id="sort_order" x-model="form.sort_order"
                        placeholder="Enter Sort Order" min="1"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#FF6A00]">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Status</label>
                    <select name="faq_status" x-model="form.faq_status" id="faq_status"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#FF6A00]">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="$('#faqModal').hide()"
                    class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg hover:bg-[#8f4f00]"
                    id="save_faq">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<div id="deleteFaqModal" x-data="{ open: false, deleteId: null }">
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Confirm Delete</h2>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this FAQ?</p>
                <div class="flex justify-end gap-3">
                    <button @click="open = false" class="px-4 py-1 border rounded-lg hover:bg-gray-100">Cancel</button>
                    <button @click="deleteFaq(deleteId)"
                        class="px-4 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    </template>
</div>
