<meta charset="utf-8">
<link
    href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;700&family=Nunito:wght@400;700&display=swap"
    rel="stylesheet">

<div id="userCreateModal"
    x-data="{
        open: false,
        previewUrl: null,
        exiting_image: '',
        latitude: null,
        longitude: null,
        stepNumber: 0,
        errors: {}, // ✅ Added missing reactive object
        form: {
            name: '',
            email: '',
            image: '',
            mobile_number: '',
            city: '',
            address: '',
            plan_id: ''
        },
        closeModal() {
            this.open = false;
            this.form = {
                name: '',
                email: '',
                image: '',
                mobile_number: '',
                city: '',
                address: '',
                plan_id: ''
            };
            this.previewUrl = null;
            this.errors = {}; // ✅ clear errors on close
        },
    }"
    x-init="initMapAndAutocomplete()"
    x-cloak
>
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
                    <input type="hidden" name="latitude" x-model="latitude">
                    <input type="hidden" name="longitude" x-model="longitude">
                    <input type="hidden" name="exiting_image" x-model="exiting_image" id="exiting_image" />
                    <input type="hidden" name="user_id" x-model="form.user_id" id="user_id" />
                    <textarea x-model="form.address" name="address" type="hidden"></textarea>

                    <div class="p-5 space-y-5 flex-1 w-full h-fit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-label>Name</x-label>
                                <x-input type="text" x-model="form.name" name="name" id="name"
                                    placeholder="Enter Your Name" required />
                            </div>
                            <div>
                                <x-label>Email</x-label>
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
                            <!-- Profile Image -->
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

                            <!-- City / Search -->
                            <div>
                                <x-label class="block text-sm font-medium text-gray-700 mb-1">
                                    Search location (address / area / shop / company)
                                </x-label>
                                <x-input type="text" x-model="form.city" name="city" x-ref="cityInput"
                                    id="cityInput" autocomplete="off"
                                    placeholder="Type address, area, shop name, company..."
                                    class="w-full rounded-lg border border-gray-300 p-2 text-gray-700 focus:border-[#ab5f00] focus:ring-[#ab5f00]/30"
                                    required />
                                <p x-show="errors.city" x-text="errors.city" class="text-red-600 text-sm mt-1"></p>
                            </div>

                            <!-- Map -->
                            <div>
                                <x-label class="block text-sm font-medium text-gray-700 mb-1">Map</x-label>
                                <div id="map" class="w-full h-64 rounded-lg border border-gray-300"></div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-center gap-3">
                            <button type="button" @click="closeModal()"
                                class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                            <button type="submit"
                                class="bg-[#ab5f00] text-white px-5 py-2 rounded-lg hover:bg-[#ab5f00]">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<!-- Global Font + Map Styles -->
<style>
    body,
    input,
    textarea,
    select,
    button {
        font-family: 'Noto Sans Tamil', 'Nunito', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif !important;
    }

    .pac-container,
    .pac-item,
    .pac-item * {
        font-family: inherit !important;
        font-size: 14px !important;
    }

    .pac-item {
        padding: 8px 10px !important;
    }
</style>

<!-- Google Transliteration (optional for Tamil typing) -->
<script src="https://www.google.com/jsapi"></script>
<script>
    google.load("elements", "1", { packages: "transliteration" });
</script>

<!-- Google Maps JS -->
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&callback=initGoogleMaps"
    async defer></script>

<script>
    let googleMapsLoaded = false;

    function initGoogleMaps() {
        googleMapsLoaded = true;
    }

    function initMapAndAutocomplete() {
        const interval = setInterval(() => {
            if (googleMapsLoaded && document.getElementById('cityInput')) {
                clearInterval(interval);
                setupMapAndAutocomplete();
            }
        }, 200);
    }

    function setupMapAndAutocomplete() {
        const mapEl = document.getElementById('map');
        const input = document.getElementById('cityInput');
        const addressField = document.querySelector('textarea[name=address]');
        const defaultCenter = { lat: 11.1271, lng: 78.6569 }; // Tamil Nadu center

        const map = new google.maps.Map(mapEl, {
            center: defaultCenter,
            zoom: 6
        });

        const marker = new google.maps.Marker({
            position: defaultCenter,
            map: map,
            draggable: true
        });

        const geocoder = new google.maps.Geocoder();

        const autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: 'in' },
            fields: ['geometry', 'formatted_address', 'address_components', 'name'],
        });

        // 🧭 When user selects an address
        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (!place.geometry) return;

            const loc = place.geometry.location;
            const lat = loc.lat();
            const lng = loc.lng();
            const address = place.formatted_address || input.value;

            marker.setPosition(loc);
            map.panTo(loc);
            map.setZoom(15);

            // ✅ Store values in hidden inputs
            document.querySelector('[name=latitude]').value = lat;
            document.querySelector('[name=longitude]').value = lng;
            addressField.value = address; // ✅ Save address text
        });

        // 📍 When marker is dragged
        marker.addListener('dragend', () => {
            const pos = marker.getPosition();
            document.querySelector('[name=latitude]').value = pos.lat();
            document.querySelector('[name=longitude]').value = pos.lng();

            geocoder.geocode({ location: pos }, (results, status) => {
                if (status === 'OK' && results.length) {
                    const address = results[0].formatted_address;
                    input.value = address;
                    addressField.value = address; // ✅ Save dragged address
                }
            });
        });

        // 🈹 Optional: Tamil transliteration
        try {
            const control = new google.elements.transliteration.TransliterationControl({
                sourceLanguage: 'en',
                destinationLanguage: ['ta'],
                transliterationEnabled: true,
            });
            control.makeTransliteratable(['cityInput']);
        } catch (err) {
            console.warn('Transliteration unavailable');
        }
    }
</script>

