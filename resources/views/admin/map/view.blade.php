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

<script async src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places,drawing&callback=initMap"></script>
<script>
    // Define initMap globally to be called by Google Maps API
    function initMap() {
        let map, drawingManager, marker, searchBox, polygons = [];

        // Initialize the map
        map = new google.maps.Map(document.getElementById('city_map'), {
            center: { lat: 11.0168, lng: 76.9558 }, // Coimbatore
            zoom: 12,
        });

        // Search box for city search
        const input = document.getElementById('search-city');
        searchBox = new google.maps.places.SearchBox(input);

        map.addListener('bounds_changed', function () {
            searchBox.setBounds(map.getBounds());
        });

        searchBox.addListener('places_changed', function () {
            const places = searchBox.getPlaces();
            if (places.length === 0) return;

            const city = places[0];
            map.setCenter(city.geometry.location);
            map.setZoom(14);

            if (marker) marker.setMap(null);
            marker = new google.maps.Marker({
                map: map,
                position: city.geometry.location,
                title: city.name
            });
        });

        // Drawing Manager
        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: google.maps.drawing.OverlayType.POLYGON,
            drawingControl: true,
            drawingControlOptions: {
                position: google.maps.ControlPosition.TOP_CENTER,
                drawingModes: ['polygon']
            },
            polygonOptions: {
                fillColor: '#FF0000',
                fillOpacity: 0.35,
                strokeWeight: 2,
                strokeColor: '#FF0000',
                editable: true,
                zIndex: 1
            }
        });
        drawingManager.setMap(map);

        // Event listener for completed polygon
        google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) {
            polygons.push(polygon);

            let coordinates = [];
            polygon.getPath().forEach(function(vertex) {
                coordinates.push({
                    lat: vertex.lat(),
                    lng: vertex.lng()
                });
            });

            polygon.coordinates = coordinates;
        });
        const cityCoords = @json($hub_list->mapWithKeys(function ($city) {
        return [$city->id => ['name' => $city->name, 'lat' => (float)$city->latitude, 'lng' => (float)$city->longitude]];
    }));

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
                                    paths: coords.map(coord => ({ lat: parseFloat(coord.lat), lng: parseFloat(coord.lng) })),
                                    strokeColor: '#FF0000',
                                    strokeOpacity: 0.8,
                                    strokeWeight: 2,
                                    fillColor: '#FF0000',
                                    fillOpacity: 0.35
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
        // Handle the clear button click
        $('#clear-polygons').click(function() {
            if (window.currentPolygons && window.currentPolygons.length > 0) {
                window.currentPolygons.forEach((polygon) => {
                    polygon.setMap(null); // Remove each polygon from the map
                });
                window.currentPolygons = []; // Reset the array
                showToast("All polygons have been cleared", "success", 2000);
            } else {
                showToast("No polygons to clear.!", "error", 2000);
            }
        });

        // Save all drawn polygons
        $('#save-area').click(function() {
            let selectedCity = $('#city').val();


            if (!selectedCity) {
                showToast("Please Select City!", "error", 2000);
                return;
            }

            if (polygons.length > 0) {
                const dataToSave = polygons.map(p => p.coordinates);

                $.ajax({
                    url: '/admin/map/save-area',
                    method: 'POST',
                    data: {
                        polygons: dataToSave,
                        hub_id: selectedCity
                    },
                    success: function() {
                        showToast("Area saved successfully!", "success", 2000);
                        window.location.reload();
                    },
                    error: function() {
                        showToast("Something went wrong!", "error", 2000);
                    }
                });
            } else {
                showToast("Please draw at least one polygon on the map.", "error", 2000);
            }
        });
    }
</script>
