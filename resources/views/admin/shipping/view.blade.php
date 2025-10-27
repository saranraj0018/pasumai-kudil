<x-layouts.app>
    <meta charset="utf-8">
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;700&family=Nunito:wght@400;700&display=swap"
        rel="stylesheet">

    <div class="mx-auto bg-white rounded-2xl shadow-lg p-8 mt-3">
        <h2 class="text-xl font-semibold mb-5 text-center">Add Shipping Location</h2>

        <form id="shippingAddForm" enctype="multipart/form-data" novalidate x-data="shippingForm(@js($get_shipping ?? null))" x-init="initMapAndAutocomplete()"
            @submit.prevent="submitForm" class="space-y-5">
            @csrf

            <!-- Hidden Fields -->
            <input type="hidden" name="latitude" x-model="form.latitude">
            <input type="hidden" name="longitude" x-model="form.longitude">
            <input type="hidden" name="shipping_id" x-model="form.shipping_id">
            <input type="hidden" name="status" :value="form.status">

            <!-- Address -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea x-model="form.address" name="address" rows="2"
                    class="w-full rounded-lg border border-gray-300 p-2 text-gray-700 focus:border-[#ab5f00] focus:ring-[#ab5f00]/30"
                    placeholder="Enter address (you can pick from suggestions)" required></textarea>
            </div>

            <!-- City / Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search location (address / area / shop /
                    company)</label>
                <input type="text" x-model="form.query" name="city" x-ref="cityInput" id="cityInput"
                    autocomplete="off" placeholder="Type address, area, shop name, company..."
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
                    <input type="number" x-model="form.free_shipping" name="free_shipping" placeholder="5"
                        class="w-full rounded-lg border border-gray-300 p-2 text-gray-700 focus:border-[#ab5f00] focus:ring-[#ab5f00]/30" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Extra Kilometer Fee</label>
                    <input type="number" x-model="form.extra_km" name="extra_km" placeholder="2"
                        class="w-full rounded-lg border border-gray-300 p-2 text-gray-700 focus:border-[#ab5f00] focus:ring-[#ab5f00]/30" />
                </div>
            </div>

            <!-- Is Active (toggle) -->
            <div>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="status" name="status" :checked="form.status == 1"
                        @change="form.status = $event.target.checked ? 1 : 0" class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-[#ab5f00]">
                        <div class="absolute top-[2px] left-[2px] bg-white w-5 h-5 rounded-full transition-transform"
                            :class="form.status == 1 ? 'translate-x-5' : ''"></div>
                    </div>
                    <span class="ms-3 text-sm font-medium">Is Active</span>
                </label>
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

    <!-- Global styles for fonts and Google Autocomplete dropdown -->
    <style>
        /* Use Tamil-capable font + fallback stack for other scripts */
        body,
        input,
        textarea,
        select,
        button {
            font-family: 'Noto Sans Tamil', 'Nunito', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif !important;
        }

        /* Google autocomplete suggestions (pac) usually appended to body with these classes */
        .pac-container,
        .pac-item,
        .pac-item * {
            font-family: inherit !important;
            font-size: 14px !important;
            direction: ltr;
        }

        /* make suggestion items readable */
        .pac-item {
            padding: 8px 10px !important;
        }
    </style>

    <!-- Google Transliteration (optional) - helps typing Tamil from Latin keyboard -->
    <script src="https://www.google.com/jsapi"></script>
    <script>
        // load transliteration library asynchronously (optional)
        google.load("elements", "1", {
            packages: "transliteration"
        });
    </script>
    <!-- Google Maps JS: no hard-coded language allows Google to choose best language,
         but you can add &language=ta to favor Tamil suggestions if you want -->
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&callback=initGoogleMaps"
        async defer></script>
    <script>
        let googleMapsLoaded = false;
        function initGoogleMaps() {
            // Called once Maps library loaded
            googleMapsLoaded = true;
        }
        function shippingForm(existing = null) {
            return {
                form: {
                    shipping_id: existing?.id ?? '',
                    address: existing?.address ?? '',
                    query: existing?.city ?? '',
                    city: existing?.city ?? '',
                    latitude: existing?.latitude ? parseFloat(existing.latitude) : '',
                    longitude: existing?.longitude ? parseFloat(existing.longitude) : '',
                    free_shipping: existing?.free_shipping ?? '',
                    extra_km: existing?.extra_km ?? '',
                    status: existing?.status ?? 0,
                },
                map: null,
                marker: null,
                geocoder: null,
                autocomplete: null,
                errors: {},
                initMapAndAutocomplete() {
                    // Wait until Maps loaded and input exists
                    const poll = setInterval(() => {
                        if (googleMapsLoaded && this.$refs.cityInput) {
                            clearInterval(poll);
                            this._setupMap();
                            this._setupAutocomplete();
                            this._setupTransliteration();
                        }
                    }, 200);
                },
                _setupMap() {
                    const defaultCenter = (this.form.latitude && this.form.longitude) ? {
                        lat: this.form.latitude,
                        lng: this.form.longitude
                    } : {
                        lat: 11.1271,
                        lng: 78.6569
                    }; // Tamil Nadu center fallback

                    this.map = new google.maps.Map(document.getElementById('map'), {
                        center: defaultCenter,
                        zoom: this.form.latitude ? 14 : 6,
                    });

                    this.marker = new google.maps.Marker({
                        position: defaultCenter,
                        map: this.map,
                        draggable: true,
                    });

                    this.geocoder = new google.maps.Geocoder();

                    // Marker drag -> reverse geocode
                    this.marker.addListener('dragend', () => {
                        const pos = this.marker.getPosition();
                        this._applyLatLng(pos.lat(), pos.lng());
                        this._reverseGeocode(pos);
                    });

                        // Setup city autocomplete
                        const autocomplete = new google.maps.places.Autocomplete(this.$refs.cityInput, {
                            types: ['(cities)'],
                            componentRestrictions: { country: 'in' },
                            fields: ['address_components', 'geometry', 'name'],
                        });


                    // If we have initial lat/lng but no address loaded, reverse-geocode to fill address
                    if (this.form.latitude && this.form.longitude && !this.form.address) {
                        const latlng = new google.maps.LatLng(this.form.latitude, this.form.longitude);
                        this._reverseGeocode(latlng);
                    }
                },
                _setupAutocomplete() {
                    // Autocomplete with no restrictive 'types' to allow addresses, establishments, regions, shops etc.
                    const input = this.$refs.cityInput;
                    const options = {
                        // types: [], // empty or omitted -> broad results (addresses, establishments)
                        componentRestrictions: {
                            country: 'in'
                        }, // restrict to India for better local results; remove if global
                        fields: ['place_id', 'geometry', 'name', 'formatted_address', 'address_components']
                    };

                    this.autocomplete = new google.maps.places.Autocomplete(input, options);

                    // When user selects an item
                    this.autocomplete.addListener('place_changed', () => {
                        const place = this.autocomplete.getPlace();
                        if (!place || !place.geometry) {
                            // Sometimes place has no geometry (rare); if so, try AutocompleteService fallback
                            console.warn('Selected place has no geometry, ignoring.');
                            return;
                        }

                        // Use place.name (shop/company) if available, otherwise formatted_address
                        this.form.address = place.formatted_address || place.name || this.form.address;
                        this.form.city = this._extractCityFromComponents(place.address_components) || place.name ||
                            this.form.city;

                        const loc = place.geometry.location;
                        const lat = loc.lat();
                        const lng = loc.lng();

                        this._applyLatLng(lat, lng);
                        this.marker.setPosition(loc);
                        this.map.panTo(loc);
                        this.map.setZoom(15);
                    });

                    // Also update query model from input manually so binding stays in sync
                    input.addEventListener('input', (e) => {
                        this.form.query = e.target.value;
                    });
                },
                _setupTransliteration() {
                    // Optional: enable Google transliteration for the input (so typing Latin letters produces Tamil)
                    try {
                        const control = new google.elements.transliteration.TransliterationControl({
                            sourceLanguage: 'en',
                            destinationLanguage: ['ta'],
                            transliterationEnabled: true
                        });
                        control.makeTransliteratable([this.$refs.cityInput]);
                    } catch (err) {
                        // console.warn('Transliteration not available', err);
                    }
                },
                _applyLatLng(lat, lng) {
                    // Save with high precision as strings (or numbers) for your DB
                    this.form.latitude = parseFloat(Number(lat).toFixed(7));
                    this.form.longitude = parseFloat(Number(lng).toFixed(7));
                },
                _reverseGeocode(latLng) {
                    const that = this;
                    that.geocoder.geocode({
                        location: latLng
                    }, (results, status) => {
                        if (status === 'OK' && results && results.length) {
                            const best = results[0];
                            that.form.address = best.formatted_address || that.form.address;

                            // Try to extract city name
                            const city = that._extractAreaFromComponents(best.address_components);
                            const area = that._extractAreaFromComponents(best.address_components);

                            // Update city if available
                            if (city) {
                                that.form.city = city;
                                that.form.query = city;
                                if (that.$refs.cityInput) that.$refs.cityInput.value = city;
                            } else if (area) {
                                that.form.city = area;
                                that.form.query = area;
                                if (that.$refs.cityInput) that.$refs.cityInput.value = area;
                            }

                            if (that.form.city) {
                                that.form.query = that.form.city;
                            }
                        } else {
                            console.warn('Reverse geocode failed:', status);
                        }
                    });
                },
                _extractAreaFromComponents(components = []) {
                    if (!components || !components.length) return null;
                    const get = (types) => {
                        const comp = components.find(c => types.every(t => c.types.includes(t)));
                        return comp ? comp.long_name : null;
                    };
                    // Sub-locality or neighborhood fallback
                    return get(['sublocality_level_1']) ||
                        get(['sublocality']) ||
                        get(['neighborhood']) ||
                        get(['administrative_area_level_3']) ||
                        get(['locality']) ||
                        get(['postal_town']) ||
                        get(['administrative_area_level_2']) ||
                        get(['administrative_area_level_1']) ||
                        get(['sublocality_level_1']) ||
                        null;
                },
                submitForm() {
                    this.errors = {};

                    if (!this.form.city && !this.form.address) {
                        this.errors.city = 'Please choose a valid location from suggestions or click on map.';
                        showToast("Please choose a valid location.", "error", 2500);
                        return;
                    }

                    // Prepare form data
                    const formEl = document.getElementById('shippingAddForm');
                    const formData = new FormData(formEl);

                    // Ensure status is submitted as 1 or 0
                    formData.set('status', this.form.status || 0);

                    // if address/lat/lng updated from models, ensure they are in formData
                    formData.set('address', this.form.address || '');
                    formData.set('city', this.form.city || this.form.query || '');
                    formData.set('latitude', this.form.latitude || '');
                    formData.set('longitude', this.form.longitude || '');

                    // sendRequest is your existing ajax helper — keep it or replace as needed
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
                                $.each(err.errors, function(k, v) {
                                    msg += v[0] + "<br>";
                                });
                                showToast(msg, "error", 2200);
                            } else {
                                showToast(err.message || "Unexpected error", "error", 2200);
                            }
                        }
                    );
                }
            };
        }
    </script>
</x-layouts.app>
