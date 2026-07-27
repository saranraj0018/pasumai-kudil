<x-layouts.app>
    <div class="max-w-8xl mx-auto px-4 mt-6">
        <!-- Page Title -->
        <h2 class="text-3xl font-semibold mb-6 text-gray-800 flex items-center gap-2">
            Draw Cities
        </h2>

        <!-- City Selection Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
            <!-- Choose City -->
            <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Choose a City<span class="text-red-500">*</span></label>
                <select name="city" id="city"
                        class="block w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-800 focus:border-[#ab5f00] focus:ring-2 focus:ring-[#ab5f00]/30 outline-none">
                    <option selected disabled>Select City</option>
                    @if (!empty($hub_list))

                        @foreach ($hub_list as $id => $list)
                            <option value="{{ $list['id'] }}"> {{ $list['type'] == 1 ? $list['name'] . '- Grocery' : $list['name'] . '- Milk' }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Search City -->
            <div>
                <label for="search-city" class="block text-sm font-medium text-gray-700 mb-2">City Name</label>
                <input type="text" name="search-city" id="search-city" placeholder="Search or enter city name"
                       class="block w-full rounded-lg border border-gray-300 p-3 text-gray-800 focus:border-[#ab5f00] focus:ring-2 focus:ring-[#ab5f00]/30 outline-none"
                       autocomplete="off">
            </div>
        </div>

        <!-- Map Section -->
        <div id="city_map" class="rounded-lg border border-gray-300" style="height: 500px;"></div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-4 pt-6 mt-3">
            <button type="button" id="finish-polygon"
                    class="px-5 py-2.5 bg-green-600 text-white rounded-lg">
                Finish Polygon
            </button>
            <button type="button" id="clear-polygons"
                    class="px-5 py-2.5 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                Clear Polygons
            </button>
            <button type="submit" id="save-area"
                    class="px-5 py-2.5 bg-[#ab5f00] text-white rounded-lg hover:bg-[#9c5200] transition">
                Save Area
            </button>
        </div>
    </div>
</x-layouts.app>

<script async src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&callback=initMap"></script>
<script>
    // Define initMap globally to be called by Google Maps API
    function initMap() {
        let map, marker, polygons = [];

        // Initialize the map
        map = new google.maps.Map(document.getElementById('city_map'), {
            center: { lat: 11.0168, lng: 76.9558 },
            zoom: 12,
            disableDoubleClickZoom: true
        });
        // Search box for city search
        const input = document.getElementById('search-city');
        const autocomplete = new google.maps.places.Autocomplete(input);

        autocomplete.addListener('place_changed', function () {

            const place = autocomplete.getPlace();

            if (!place.geometry) {
                return;
            }

            map.setCenter(place.geometry.location);
            map.setZoom(14);

            if (marker) {
                marker.setMap(null);
            }

            marker = new google.maps.Marker({
                map: map,
                position: place.geometry.location,
                title: place.name
            });
        });

        // Drawing Manager
        let currentPath = [];
        let currentPolygon = null;

        map.addListener('click', function(event) {

            currentPath.push({
                lat: event.latLng.lat(),
                lng: event.latLng.lng()
            });

            if (currentPolygon) {
                currentPolygon.setMap(null);
            }

            currentPolygon = new google.maps.Polygon({
                paths: currentPath,
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.35,
                editable: true
            });

            currentPolygon.setMap(map);
        });


        const cityCoords = @json($hub_list->mapWithKeys(function ($city) {
        return [$city->id => ['name' => $city->name, 'lat' => (float)$city->latitude, 'lng' => (float)$city->longitude]];
    }));
        $('#finish-polygon').click(function() {

            if (!currentPolygon || currentPath.length < 3) {
                showToast("Minimum 3 points required", "error", 2000);
                return;
            }

            polygons.push(currentPolygon);

            currentPolygon = null;
            currentPath = [];

            showToast("Polygon completed", "success", 2000);
        });
        // Dropdown to fetch and display saved polygons for a city
        $('#city').change(function() {
            let selectedCity = $(this).val();
            if (cityCoords[selectedCity]) {
                const coords = cityCoords[selectedCity];
                if (Number.isFinite(coords.lat) && Number.isFinite(coords.lng)) {
                    map.setCenter({lat: coords.lat, lng: coords.lng});
                    map.setZoom(12);
                }

                $.ajax({
                    url: '/admin/map/get-city-coordinates',
                    method: 'GET',
                    data: { city_id: selectedCity },
                    success: function(response) {
                        if (typeof response === 'string') response = JSON.parse(response);

                        if (Array.isArray(response?.data)) {
                            if (window.currentPolygons) window.currentPolygons.forEach(p => p.setMap(null));
                            window.currentPolygons = [];

                            response?.data.forEach(coords => {
                                const polygon = new google.maps.Polygon({
                                    paths: coords.map(coord => ({
                                        lat: parseFloat(coord.lat),
                                        lng: parseFloat(coord.lng)
                                    })),
                                    strokeColor: '#FF0000',
                                    strokeOpacity: 0.8,
                                    strokeWeight: 2,
                                    fillColor: '#FF0000',
                                    fillOpacity: 0.35,
                                    editable: true
                                });
                                polygon.setMap(map);
                                window.currentPolygons.push(polygon);
                            });
                        }
                    },
                });
            }
        });

        // Clear button functionality
        $('#clear-polygons').click(function() {

            if (window.currentPolygons) {
                window.currentPolygons.forEach(p => p.setMap(null));
                window.currentPolygons = [];
            }

            polygons.forEach(p => p.setMap(null));
            polygons = [];

            if (currentPolygon) {
                currentPolygon.setMap(null);
                currentPolygon = null;
            }

            currentPath = [];

            showToast("All polygons cleared", "success", 2000);
        });

        // Save all drawn polygons
        $('#save-area').click(function() {
            let selectedCity = $('#city').val();
            let cityName = $('#search-city').val().trim();

            if (!selectedCity) {
                showToast("Please Select City!", "error", 2000);
                return;
            }

            if (!cityName) {
                showToast("Please enter a city name!", "error", 2000);
                return;
            }

            // Auto-finish current drawing if not double-clicked
            if (currentPolygon && currentPath.length >= 3) {
                polygons.push(currentPolygon);
                currentPolygon = null;
                currentPath = [];
            }

            if (polygons.length === 0) {
                showToast("Please draw at least one new polygon on the map.", "error", 2000);
                return;
            }

            const dataToSave = polygons.map(function(polygon) {

                const coords = [];

                polygon.getPath().forEach(function(point) {
                    coords.push({
                        lat: point.lat(),
                        lng: point.lng()
                    });
                });

                return coords;
            });

            $.ajax({
                url: '/admin/map/save-area',
                method: 'POST',
                data: {
                    polygons: dataToSave,
                    hub_id: selectedCity,
                    city_name: cityName
                },
                success: function() {
                    showToast("Area saved successfully!", "success", 2000);
                    window.location.reload();
                },
                error: function() {
                    showToast("Something went wrong!", "error", 2000);
                }
            });
        });
    }
</script>
