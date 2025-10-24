<x-layouts.app>
    <div class="mx-auto bg-white rounded-2xl shadow-lg p-8 mt-3">
        <h2 class="text-xl font-semibold mb-5 text-center">Add Shipping Location</h2>

        <form id="shippingAddForm"
              enctype="multipart/form-data"
              novalidate
              x-data="shippingForm(@js($get_shipping ?? null))"
              x-init="initMapAndAutocomplete()"
              @submit.prevent="submitForm"
              class="space-y-5">
            @csrf

            <!-- Hidden Fields -->
            <input type="hidden" name="latitude" x-model="form.latitude">
            <input type="hidden" name="longitude" x-model="form.longitude">
            <input type="hidden" name="shipping_id" x-model="form.shipping_id">

            <!-- Address -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea x-model="form.address"
                          name="address"
                          rows="2"
                          class="w-full rounded-lg border border-gray-300 p-2 text-gray-700 focus:border-[#ab5f00] focus:ring-[#ab5f00]/30"
                          placeholder="Enter address"
                          required></textarea>
            </div>

            <!-- City -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
                <input type="text"
                       x-model="form.city"
                       name="city"
                       x-ref="cityInput"
                       placeholder="Search city..."
                       id="cityInput"
                       class="w-full rounded-lg border border-gray-300 p-2 text-gray-700 focus:border-[#ab5f00] focus:ring-[#ab5f00]/30"
                       required />
                <p x-show="errors.city" x-text="errors.city" class="text-red-600 text-sm mt-1"></p>
            </div>

            <!-- Map -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Map</label>
                <div id="map" class="w-full h-64 rounded-lg border border-gray-300"></div>
            </div>

            <!-- Free Shipping & Extra KM -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Free Shipping (km)</label>
                    <input type="number"
                           x-model="form.free_shipping"
                           name="free_shipping"
                           placeholder="5"
                           class="w-full rounded-lg border border-gray-300 p-2 text-gray-700 focus:border-[#ab5f00] focus:ring-[#ab5f00]/30" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Extra Kilometer Fee</label>
                    <input type="number"
                           x-model="form.extra_km"
                           name="extra_km"
                           placeholder="2"
                           class="w-full rounded-lg border border-gray-300 p-2 text-gray-700 focus:border-[#ab5f00] focus:ring-[#ab5f00]/30" />
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-center pt-5">
                <button type="submit"
                        class="bg-[#ab5f00] text-white px-6 py-2.5 rounded-lg hover:bg-[#924f00] transition">
                    Save
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&callback=initGoogleMaps&language=en&region=US"
    async defer>
</script>


<script>
    let googleMapsLoaded = false;
    function initGoogleMaps() {
        googleMapsLoaded = true;
    }

    function shippingForm(existing = null) {
        return {
            form: {
                shipping_id: existing?.id ?? '',
                address: existing?.address ?? '',
                city: existing?.city ?? '',
                latitude: existing?.latitude ? parseFloat(existing.latitude) : '',
                longitude: existing?.longitude ? parseFloat(existing.longitude) : '',
                free_shipping: existing?.free_shipping ?? '',
                extra_km: existing?.extra_km ?? ''
            },
            map: null,
            marker: null,
            errors: {},

            async initMapAndAutocomplete() {
                const waitForGoogle = setInterval(() => {
                    if (googleMapsLoaded && this.$refs.cityInput) {
                        clearInterval(waitForGoogle);

                        const defaultCenter = this.form.latitude && this.form.longitude
                            ? { lat: this.form.latitude, lng: this.form.longitude }
                            : { lat: 20.5937, lng: 78.9629 }; // Default India center

                        // Initialize Map
                        this.map = new google.maps.Map(document.getElementById('map'), {
                            center: defaultCenter,
                            zoom: this.form.latitude ? 10 : 5,
                            mapId: "DEMO_MAP_ID",
                        });

                        // Create Draggable Marker
                        this.marker = new google.maps.Marker({
                            position: defaultCenter,
                            map: this.map,
                            draggable: true,
                        });

                        // Handle marker drag to update address + city
                        const geocoder = new google.maps.Geocoder();
                        this.marker.addListener("dragend", () => {
                            const position = this.marker.getPosition();
                            this.form.latitude = position.lat();
                            this.form.longitude = position.lng();
                            this.updateAddressFromLatLng(geocoder, position);
                        });

                        // Setup city autocomplete
                        const autocomplete = new google.maps.places.Autocomplete(this.$refs.cityInput, {
                            types: ['(cities)'],
                            componentRestrictions: { country: 'in' },
                            fields: ['address_components', 'geometry', 'name'],
                        });


                        autocomplete.addListener('place_changed', () => {
                            const place = autocomplete.getPlace();
                            if (!place.geometry) return;

                            this.form.city = place.name;
                            this.form.latitude = place.geometry.location.lat();
                            this.form.longitude = place.geometry.location.lng();

                            const location = place.geometry.location;
                            this.map.setCenter(location);
                            this.map.setZoom(10);
                            this.marker.setPosition(location);

                            // Update address too
                            this.updateAddressFromLatLng(geocoder, location);
                        });
                    }
                }, 300);
            },

            // Reverse geocode to get address and city from coordinates
            updateAddressFromLatLng(geocoder, latLng) {
                geocoder.geocode({ location: latLng }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        this.form.address = results[0].formatted_address;

                        // Try to extract city name
                        const cityComponent = results[0].address_components.find(c =>
                            c.types.includes("locality") || c.types.includes("administrative_area_level_2")
                        );
                        if (cityComponent) {
                            this.form.city = cityComponent.long_name;
                            this.$refs.cityInput.value = cityComponent.long_name;
                        }
                    }
                });
            },

            submitForm() {
                this.errors = {}; // Reset previous errors

                // Validation
                if (!this.form.city) {
                    this.errors.city = "City is required.";
                    showToast("Please enter a city.", "error", 2000);
                    return;
                }

                const formEl = document.getElementById("shippingAddForm");
                const formData = new FormData(formEl);

                sendRequest(
                    "/admin/shipping/save-shipping",
                    formData,
                    "POST",
                    (res) => {
                        if (res.success) {
                            showToast("Shipping saved successfully!", "success", 2000);
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            showToast("Something went wrong!", "error", 2000);
                        }
                    },
                    (err) => {
                        if (err.errors) {
                            let msg = "";
                            $.each(err.errors, function (k, v) {
                                msg += v[0] + "<br>";
                            });
                            showToast(msg, "error", 2000);
                        } else {
                            showToast(err.message || "Unexpected error", "error", 2000);
                        }
                    }
                );
            }
        };
    }
</script>
