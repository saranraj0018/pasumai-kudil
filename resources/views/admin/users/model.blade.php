    <div id="revokeSubscriptionModal" x-cloak>
        <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>

            <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-2xl relative z-50">
                <h2 class="text-2xl font-semibold mb-4">Revoke</h2>

                <form id="revokeForm" class="flex flex-col space-y-5">
                    @csrf
                    <input type="hidden" name="sub_id" :value="selectedId">

                    <h2>Revoke the day and reduce the validity period</h2>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="open = false"
                                class="px-4 py-2 border rounded-lg">Cancel</button>
                        <button type="submit" class="bg-[#ab5f00] text-white px-4 py-2 rounded-lg">Change</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

