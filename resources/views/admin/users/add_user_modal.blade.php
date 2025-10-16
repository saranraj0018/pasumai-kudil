<div id="userCreateModal" x-data="{
    open: false,
    previewUrl: null,
    exiting_image: '',
    stepNumber: 0,
    form: {
        name: '',
        email: '',
        image: '',
        mobile_number: '',
        plan_id: ''
    },
    closeModal() {
        this.open = false;
        this.form = {
            name: '',
            email: '',
            image: '',
            mobile_number: '',
            plan_id: ''
        };
    },
}" x-cloak>
    <template x-if="open">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <!-- Modal Box -->
            <div class="bg-white p-4 rounded-2xl shadow-2xl w-full max-w-[90%] relative z-50">
                <h2 class="text-2xl font-bold mb-6 text-gray-800" id="product_label">Add User</h2>
                <form id="userAddForm" enctype="multipart/form-data" novalidate
                    class="flex flex-col justify-start items-start w-full  h-[65vh] overflow-y-scroll">
                    @csrf
                    <input type="hidden" name="exiting_image" x-model="exiting_image" id="exiting_image" />
                    <input type="hidden" name="user_id" x-model="form.user_id" id="user_id"/>
                    <div class="p-5 space-y-5 flex-1 w-full h-fit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-label>Name</x-label>
                                <x-input type="text" x-model="form.name" name="name" id="name"
                                    placeholder="Enter Your Name" required />
                            </div>
                            <div>
                                <x-label>email</x-label>
                                <x-input type="text" x-model="form.email" name="email" id="email"
                                    placeholder="Enter Your Email" />
                            </div>
                            <div>
                                <x-label>Mobile Number</x-label>
                                <x-input type="text" x-model="form.mobile_number" name="mobile_number"
                                    id="mobile_number" placeholder="Enter Your Mobile Number" required />
                            </div>
                            <div>
                                <x-label>Plan Name</x-label>
                                <x-select x-model="form.plan_id" name="plan_id" id="plan_id" required>
                                    <option value="" selected disabled>Please Select Plan Name</option>
                                    @foreach ($subscription_plan as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->plan_name }}</option>
                                    @endforeach
                                </x-select>
                            </div>
                             <div id="custom_plan_days"></div>
                              <div class="col-span-2">
                                <x-label>Profile Image</x-label>
                                <input type="file" name="image" id="image" accept=".png, .jpg, .jpeg"
                                    x-ref="fileInput"
                                    @change="
                               const file = $refs.fileInput.files[0];
                               if (file) {
                                   const reader = new FileReader();
                                   reader.onload = e => { previewUrl = e.target.result }
                                   reader.readAsDataURL(file);
                               }
                           "
                                    class="form-input w-full border border-gray-300 rounded-lg p-2 cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#ab5f00] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#ab5f00] file:text-white hover:file:bg-[#ab5f00]">

                                <div class="mt-4 flex justify-center overflow-hidden">
                                    <img :src="previewUrl" x-show="previewUrl"
                                        class="w-full max-h-[30vh] rounded-lg border border-gray-300 shadow-md object-cover" />
                                </div>
                            </div>
                        </div>
                        <!-- Buttons -->
                        <div class="flex items-center justify-center gap-3">
                            <button type="button" @click="closeModal()"
                                class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                            <button type="submit" class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg hover:bg-[#ab5f00]">Save</button>
                        </div>
                </form>
            </div>
        </div>
    </template>
</div>
