<!-- Shipping Modal -->
<div id="shippingModal" x-data="shippingModal()"
    x-init="initAutocomplete()"
    x-cloak
>
    <!-- Overlay -->
    <div 
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40"
        @click="closeModal()"
    ></div>

    <!-- Modal Box -->
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeModal()">
        <div  class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl p-6 relative overflow-y-auto max-h-[90vh]">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800 text-center" id="shipping_label">
                Add Shipping Info
            </h2>
            <form class="space-y-5" id="shippingAddForm" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="latitude" x-model="form.latitude">
                <input type="hidden" name="longitude" x-model="form.longitude">
                <input type="hidden" name="shipping_id" x-model="form.shipping_id" id="shipping_id"/>
                <!-- Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea x-model="form.address" rows="2" name="address"
                        class="w-full rounded-lg border border-gray-300 p-2 text-gray-700 focus:border-[#ab5f00] focus:ring-[#ab5f00]/30"
                        placeholder="Enter address"></textarea>
                </div>
                <!-- City Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" x-model="form.city" name="city" x-ref="cityInput" placeholder="Search city..."
                        class="w-full rounded-lg border border-gray-300 p-2 text-gray-700 focus:border-[#ab5f00] focus:ring-[#ab5f00]/30" />
                </div>
                <!-- Map -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Map</label>
                    <div id="map" class="w-full h-64 rounded-lg border border-gray-300"></div>
                </div>
                <!-- Free Shipping -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Free Shipping (km)</label>
                        <input type="number" x-model="form.free_shipping"
                            placeholder="5" name="free_hipping"
                            class="w-full rounded-lg border border-gray-300 p-2 text-gray-700 focus:border-[#ab5f00] focus:ring-[#ab5f00]/30" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Extra Kilometer Fee</label>
                        <input type="number" x-model="form.extra_km" name="extra_kilometer"
                            placeholder="2"
                            class="w-full rounded-lg border border-gray-300 p-2 text-gray-700 focus:border-[#ab5f00] focus:ring-[#ab5f00]/30" />
                    </div>
                </div>
                <!-- Buttons -->
                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" @click="closeModal()"
                        class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                    <button type="submit"
                        class="bg-[#ab5f00] text-white px-4 py-2 rounded-lg hover:bg-[#924f00]" id="save_shipping">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

 

<!-- Google Maps Script -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places"></script>

<script>
function shippingModal() {
    return {
        open: false,
        form: {
            address: '',
            city: '',
            latitude: '',
            longitude: '',
            free_shipping: '',
            extra_km: ''
        },
        map: null,
        marker: null,

        // Initialize autocomplete + map
        initAutocomplete() {
            const interval = setInterval(() => {
                if (window.google && this.$refs.cityInput) {
                    clearInterval(interval);

                    const autocomplete = new google.maps.places.Autocomplete(this.$refs.cityInput, {
                        types: ['(cities)']
                    });

                    autocomplete.addListener('place_changed', () => {
                        const place = autocomplete.getPlace();
                        if (!place.geometry) return;

                        this.form.city = place.name;
                        this.form.latitude = place.geometry.location.lat();
                        this.form.longitude = place.geometry.location.lng();
                        this.updateMap(place.geometry.location);
                    });

                    // Default India center
                    this.initMap({ lat: 20.5937, lng: 78.9629 });
                }
            }, 300);
        },

        initMap(center) {
            this.map = new google.maps.Map(document.getElementById('map'), {
                center,
                zoom: 5,
                mapTypeControl: false,
                streetViewControl: false,
            });
            this.marker = new google.maps.Marker({
                map: this.map,
                position: center
            });
        },

        updateMap(location) {
            this.map.setCenter(location);
            this.map.setZoom(10);
            this.marker.setPosition(location);
        },

        openModal() {
            this.open = true;
            document.body.classList.add('overflow-hidden'); 
        },

        closeModal() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        }
    };
}
</script>
