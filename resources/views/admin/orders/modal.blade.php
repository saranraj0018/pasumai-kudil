<div id="orderModal" style="display:none;"
     class="fixed inset-0 flex items-center justify-center z-50">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/30" id="orderModalBackdrop"></div>

    <!-- Modal -->
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-[95%] max-w-7xl relative z-10 overflow-y-auto max-h-[95vh]">

        <!-- Header -->
        <div class="flex justify-between items-start mb-6 border-b pb-4">
            <h2 class="text-3xl font-bold text-gray-800" id="orderModalTitle">Order #</h2>
            <button id="closeModalBtn" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Close</button>
        </div>

        <!-- Customer Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-gray-700 mb-2">Customer Details</h3>
                <p><strong>Name:</strong> <span id="orderCustomerName">—</span></p>
                <p><strong>Email:</strong> <span id="orderCustomerEmail">—</span></p>
                <p><strong>Mobile:</strong> <span id="orderCustomerMobile">—</span></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-gray-700 mb-2">Billing Address</h3>
                <p id="orderBillingAddress">—</p>
            </div>
        </div>

        <!-- Products Table -->
        <div class="overflow-x-auto mb-6">
            <table class="w-full text-sm text-left text-gray-700 border border-gray-200 rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Product</th>
                        <th class="px-4 py-2">Qty</th>
                        <th class="px-4 py-2">Price</th>
                        <th class="px-4 py-2">Total</th>
                    </tr>
                </thead>
                <tbody id="orderProductsBody" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>

        <!-- Amount Breakdown -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6 max-w-md">
            <h3 class="font-semibold text-gray-700 mb-2">Amount Breakdown</h3>
            <div class="flex justify-between mb-1"><span>Subtotal:</span> <span id="orderSubtotal">₹0</span></div>
            <div class="flex justify-between mb-1"><span>GST:</span> <span id="orderGST">₹0</span></div>
            <div class="flex justify-between mb-1"><span>Shipping:</span> <span id="orderShipping">₹0</span></div>
            <div class="flex justify-between mb-1"><span>Coupon Discount:</span> <span id="orderCoupon">-₹0</span></div>
            <div class="flex justify-between font-bold text-lg border-t border-gray-300 pt-2 mt-2">
                <span>Grand Total:</span> <span id="orderGrandTotal">₹0</span>
            </div>
        </div>

        <!-- Status Update -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block font-medium text-gray-700 mb-1">Order Status</label>
                <select id="status" class="w-full border p-3 rounded-lg text-gray-900">
                    <option value="1">Pending</option>
                    <option value="3">Shipped</option>
                    <option value="4">Delivered</option>
                    <option value="5">Cancelled</option>
                    <option value="6">Refunded</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Date</label>
                <input type="date" id="statusDate" class="w-full border p-3 rounded-lg text-gray-900"/>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-end gap-4">
            <button id="cancelModalBtn" class="px-6 py-3 border rounded-lg hover:bg-gray-100">Cancel</button>
            <button id="saveStatusBtn" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">Save</button>
        </div>
    </div>
</div>
