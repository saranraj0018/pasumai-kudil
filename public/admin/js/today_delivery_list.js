$(function () {
    $(document).on("click", ".todayDeliveryList", function () {
        let delivery = {
            delivery_partner_id: $(this).data("delivery_partner_id"),
            extra_quantity: $(this).data("extra_quantity"),
            damage_quantity: $(this).data("damage_quantity"),
            returned_quantity: $(this).data("returned_quantity"),
        };

        // Show modal
        $("#stockmaintainModal").css("display", "flex");
        let modal = document.getElementById("stockmaintainModal");
        let alpine = modal.__x.$data;
        alpine.open = true;
        alpine.form.delivery_partner_id = delivery.delivery_partner_id || "";
        alpine.form.extra_quantity = delivery.extra_quantity || "";
        alpine.form.damage_quantity = delivery.damage_quantity || "";
        alpine.form.returned_quantity = delivery.returned_quantity || "";
    });

    $(document).on("submit", "#deliveryTrackForm", function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        showLoader();
        sendRequest(
            "/admin/today_delivery/stock-maintain-save",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(
                        "Delivery Status saved successfully!",
                        "success",
                        2000
                    );
                    setTimeout(() => {
                        let modalScope = document.querySelector(
                            "#stockmaintainModal"
                        ).__x.$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false;
                        }
                        document.getElementById("deliveryTrackForm").reset();
                        window.location.reload();
                    }, 500);
                } else {
                    showToast("Something went wrong!", "error", 2000);
                }
            },
            function (err) {
                hideLoader();
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
    });
    
});

