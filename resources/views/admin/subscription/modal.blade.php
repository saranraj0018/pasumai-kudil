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
                        <label>Plan Type</label>
                        <select name="plan_type" id="plan_type" class="form-input w-full border rounded-lg p-2">
                            <option value="">Select Plan Type</option>
                            <option value="Basic">Basic</option>
                            <option value="Best Value">Best Value</option>
                            <option value="Customize">Customize</option>
                        </select>
                    </div>

                    <div id="plan_pack_container">
                        <label>Plan Pack</label>
                        <div class="flex items-center border rounded-lg overflow-hidden">
                            <input type="number" name="plan_pack" id="plan_pack"
                                class="w-full p-2 outline-none border-0" placeholder="Enter number">
                            <span class="bg-gray-100 px-3 py-2 text-gray-700 text-sm border-l">Month</span>
                        </div>
                    </div>

                    <div id="delivery_days_container" style="display:none;">
                        <label>Delivery Days</label>
                        <div class="flex items-center border rounded-lg overflow-hidden">
                            <input type="number" id="delivery_days_input" placeholder="Enter number of days" class="w-full p-2 outline-none border-0">
                           <button type="button" id="add_delivery_day_btn" class="ml-2 px-3 py-1 bg-[#ab5f00] text-white rounded">
                          <i class="fa-solid fa-plus"></i>
                          </button>
                        </div>
                        <div id="delivery_days_list" class="mt-2"></div>
                    </div>


                <div>
                    <label>Plan Amount</label>
                    <input type="number" name="plan_amount" id="plan_amount"
                        class="form-input w-full border rounded-lg p-2">
                </div>
               <div>
       <label>Duration</label>
      <div class="flex items-center border rounded-lg overflow-hidden">
        <input type="number" name="plan_duration" id="plan_duration"
            class="w-full p-2 outline-none border-0" placeholder="Enter duration">
        <span class="bg-gray-100 px-3 py-2 text-gray-700 text-sm border-l">Days</span>
        </div>
            </div>
            </div>

            <div>
                <label>Plan Details</label>
                <textarea name="plan_details" id="plan_details" class="form-input w-full border rounded-lg p-2"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
               <div>
               <label>Quantity</label>
                <input type="number" name="quantity" id="quantity"
                 class="form-input w-full border rounded-lg p-2"
                  >
               </div>
               <div>
               <label>Pack</label>
                <input type="text" name="pack" id="pack"
                 class="form-input w-full border rounded-lg p-2"
                    >
                </div>
              </div>

            <div class="flex justify-end gap-3 pt-4">
              <button type="button" id="cancelSubscriptionModal" class="px-5 py-2 border rounded-lg">Cancel</button>
                <button type="submit" id="save_subscription"
                    class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg">Save</button>
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
