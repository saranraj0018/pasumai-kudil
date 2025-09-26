<div id="couponModal" x-data="{ open: false }">
    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">

            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>

            <!-- Modal Box -->
            <div class="bg-white p-8 rounded-2xl shadow-2xl w-[700px] max-w-[95%] relative z-10">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Add Coupon</h2>

                <form id="couponForm" novalidate class="space-y-6">
                    @csrf
                    <input type="hidden" name="coupon_id" id="coupon_id">

                    <!-- Code + Type + Value -->
                    <div class="flex items-center gap-3">
                        <div class="w-full">
                            <label class="block text-gray-700 font-medium mb-2">Coupon Code</label>
                            <input type="text" name="coupon_code" id="coupon_code"
                                   class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#ab5f00]" required>
                        </div>
                        <div class="w-full">
                            <label class="block text-gray-700 font-medium mb-2">Discount Type</label>
                            <select name="discount_type" id="discount_type"
                                    class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#ab5f00]">
                                <option value="1">Percentage</option>
                                <option value="2">Fixed</option>
                            </select>
                        </div>
                        <div class="w-full">
                            <label class="block text-gray-700 font-medium mb-2">Discount Value</label>
                            <input type="number" name="discount_value" id="discount_value"
                                   class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#ab5f00]" required>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Description</label>
                        <input type="text" name="description" id="description"
                               class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#ab5f00]">
                    </div>

                    <!-- Apply For + Prices + Orders -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Apply For</label>
                            <select name="apply_for" id="apply_for"
                                    class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#ab5f00]">
                                <option value="1">Subtotal</option>
                                <option value="2">Order</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Max Price</label>
                            <input type="number" name="max_price" id="max_price"
                                   class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#ab5f00]">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Min Price</label>
                            <input type="number" name="min_price" id="min_price"
                                   class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#ab5f00]">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Order Count</label>
                            <input type="number" name="order_count" id="order_count"
                                   class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#ab5f00]">
                        </div>
                    </div>

                    <!-- Expiry + Status -->
                    <div class="flex items-center gap-3">
                        <div class="w-full">
                            <label class="block text-gray-700 font-medium mb-2">Expires At</label>
                            <input type="date" name="expires_at" id="expires_at"
                                   class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#ab5f00]">
                        </div>
                        <div class="w-full">
                            <label class="block text-gray-700 font-medium mb-2">Status</label>
                            <select name="status" id="status"
                                    class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#ab5f00]">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button type="button" @click="open = false"
                                class="px-5 py-1 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                        <button type="submit"
                                class="bg-[#ab5f00] text-white px-5 py-1 rounded-lg hover:bg-[#ab5f00]">Save</button>
                    </div>

                    <div id="couponErrors" class="text-red-600 text-sm mt-2 hidden"></div>
                </form>
            </div>
        </div>
    </template>
</div>

<div id="deleteCouponModal" x-data="{ open: false, deleteId: null }">
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Confirm Delete</h2>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this coupon?</p>
                <div class="flex justify-end gap-3">
                    <button @click="open = false"
                        class="px-4 py-1 border rounded-lg hover:bg-gray-100">Cancel</button>
                    <button @click="deleteCoupon(deleteId)"
                        class="px-4 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    </template>
</div>

