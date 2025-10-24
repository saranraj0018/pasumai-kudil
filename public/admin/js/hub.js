$(function () {
    $(document).on("click", "#create_hub", function () {
        openModal()
    });



    function openModal() {
        $("#hub_model").fadeIn(200, function () {
            // Trigger your custom modal event after visible
            $(this).trigger('showModal');
        });
        setTimeout(() => {
            $('#hub_name').focus(); // Ensure the input is focused
        }, 500);
    }
    $(document).on("click", "#cancel_hub_Modal", function () {
    $('#hub_model').fadeOut(200);
    });

    let map, marker, autocomplete;
    $('#hub_model').on('showModal', function () {
        initializeGoogleMap();
        initializeAutocomplete();
        setTimeout(() => {
            if (!map) {
                initializeGoogleMap();
                initializeAutocomplete();
            } else {
                google.maps.event.trigger(map, 'resize'); // Resize the map
                map.setCenter(marker.getPosition()); // Center the map on the marker
            }
        }, 300); // Delay to allow modal rendering
    });

    function initializeGoogleMap() {
        let latitude = $('#latitude').val()  ? parseFloat($('#latitude').val()) : 11.0168 ;
        let longitude = $('#longitude').val() ? parseFloat($('#longitude').val()) : 76.9558;
        const defaultLocation = { lat: latitude, lng: longitude};

        map = new google.maps.Map(document.getElementById('hub_map'), {
            center: defaultLocation,
            zoom: 10,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
        });

        marker = new google.maps.Marker({
            position: defaultLocation,
            map: map,
            draggable: true,
        });

        google.maps.event.addListener(marker, 'dragend', function () {
            updateLatLng(marker.getPosition());
        });
    }

    function initializeAutocomplete() {
        const input = document.getElementById('hub_name');
        autocomplete = new google.maps.places.Autocomplete(input);

        autocomplete.setOptions({
            componentRestrictions: { country: "in" }, // Replace "us" with your country code
        });

        autocomplete.bindTo('bounds', map);

        input.addEventListener('click', function () {
            const event = new KeyboardEvent('keydown', { key: 'ArrowDown' });
            input.dispatchEvent(event);
        });

        // Handle place selection
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) {
                alert("No details available for the input: '" + input.value + "'");
                return;
            }

            const location = place.geometry.location;
            map.setCenter(location);
            map.setZoom(15);
            marker.setPosition(location);
            updateLatLng(location);
        });

    }
    function updateLatLng(location) {
        document.getElementById('latitude').value = location.lat();
        document.getElementById('longitude').value = location.lng();
    }


    $(document).on("submit", "#hub_form", function (e) {
        e.preventDefault();


        // Fields to validate
        let fields = [
            { id: "#hub_name", condition: (val) => val === "", message: "City Name is required" },
            { id: "#type", condition: (val) => val === "", message: "Type is required" },
        ];

        let isValid = true;
        for (const field of fields) {
            const result = validateField(field); // synchronous, so no async/await needed
            if (!result) isValid = false;
        }

        if (!isValid) return;

        let formData = new FormData(this);
        sendRequest(
            "/admin/hub/city/save",
            formData,
            "POST",
            function(res){
                if(res.success){
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        $('#hub_model').fadeOut(200);
                        // Reset the form so next time it's clean
                        document.getElementById("hub_form").reset();
                        initializeGoogleMap();
                        initializeAutocomplete();
                        reloadList()
                    }, 500);

                } else {
                    showToast("Something went wrong!", "error", 2000);
                }
            },
            function(err){
                if(err.errors){
                    let msg = "";
                    $.each(err.errors, function(k,v){ msg+=v[0]+"<br>"; });
                    showToast(msg, "error", 2000);
                } else {
                    showToast(err.message || "Unexpected error", "error", 2000);
                }
            }
        );
    });

    $(document).on("click", ".editHubBtn", function () {

        let btn = $(this);
        // open modal
        openModal()
        $("#hub_title").text("Edit Hub");
        $("#save_hub").text("Update");
        $("#hub_id").val(btn.data("id"));
        $("#hub_name").val(btn.data("name"));
        $("#type").val(btn.data("type"));
        $("#status").val(btn.data("status"));
        $("#latitude").val(btn.data("latitude"));
        $("#longitude").val(btn.data("longitude"));
        initializeGoogleMap()
        setTimeout(() => {
            $('#hub_name').focus();
        }, 500);

    });
    let delete_id = 0;
    $(document).on("click", ".deleteHubBtn", function () {
        delete_id = $(this).data("id");
        $("#delete_hub_modal").show();

    });

    $(document).on("click", "#cancel_hub_btn", function () {
        $("#delete_hub_modal").hide();
    });

    $(document).on("click", "#delete_hub_Btn", function () {
        if (!delete_id) return;
        sendRequest(
            "/admin/hub/delete",
            { id: delete_id },
            "Delete",
            function (res) {
                console.log(res)
                if (res.success) {
                    showToast(
                        "City deleted successfully!",
                        "success",
                        2000
                    );
                    reloadList();
                } else {
                    showToast(res.message, "error", 2000);
                }
                delete_id = null;
                $("#delete_hub_modal").hide();
            },
            function (err) {
                showToast(err.message || "Delete failed", "error", 2000);
                delete_id = null;
                $("#delete_hub_modal").hide();
            }
        );
    });
    function reloadList() {
        $.get("/admin/hub/list", function (html) {
            let $tbody = $(html).find("#hubTableBody").html();
            $("#hubTableBody").html($tbody);
        });
    }
});


