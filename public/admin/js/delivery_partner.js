$(function () {
    $(document).on("submit", "#deliveryPartnerAddForm", function (e) {
        e.preventDefault();
        let isValid = true;
        let $saveBtn = $("#save_product");
        const fields = [
            {
                id: "#name",
                condition: (val) => val === "",
                message: "Name is required",
            },
            {
                id: "#area_name",
                condition: (val) => val === "",
                message: "Please select Area Name",
            },
            {
                id: "#mobile_number",
                condition: (val) => val === "",
                message: "Mobile Number is required",
            },
        ];

        fields.forEach((field) => {
            if (!validateField(field)) isValid = false;
        });

        if (!isValid) return;
        $saveBtn
            .prop("disabled", true)
            .removeClass("opacity-50 cursor-not-allowed")
            .text("Saving....");
        let formData = new FormData(this);
        showLoader();
        sendRequest(
            "/admin/delivery_partner/save-delivery-partner",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(
                        "Delivery Partner saved successfully!",
                        "success",
                        2000,
                    );
                    setTimeout(() => {
                        let modalScope = document.querySelector(
                            "#deliveryPartnerCreateModal",
                        ).__x.$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false; // close modal
                        }
                        // Reset form
                        document
                            .getElementById("deliveryPartnerAddForm")
                            .reset();
                            window.location.reload();
                        reloadDeliveryPartnerList();
                    }, 500);
                } else {
                    showToast("Something went wrong!", "error", 2000);
                }
                $saveBtn
                    .prop("disabled", false)
                    .removeClass("opacity-50 cursor-not-allowed")
                    .text("Save");
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
                $saveBtn
                    .prop("disabled", false)
                    .removeClass("opacity-50 cursor-not-allowed")
                    .text("Save");
            },
        );
    });

    $(document).on("click", ".add_delivery_partner", function (e) {
        $("#deliveryPartnerCreateModal").css("display", "flex");
        let modal = document.getElementById("deliveryPartnerCreateModal");
        let alpine = modal.__x.$data;
        alpine.open = true;
        alpine.modalTitle = "Add Delivery Partner";
        alpine.buttonText = "Save";
    });

    $(document).on("click", ".editDeliveryPartner", function () {
        let delivery_partner = {
            id: $(this).data("id"),
            name: $(this).data("name"),
            area_name: $(this).data("area_name"),
            mobile_number: $(this).data("mobile_number"),
        };
        // Show modal
        $("#deliveryPartnerCreateModal").css("display", "flex");
        let modal = document.getElementById("deliveryPartnerCreateModal");
        let alpine = modal.__x.$data;
        alpine.open = true;
        console.log(delivery_partner);
        alpine.modalTitle = "Edit Delivery Partner";
        alpine.buttonText = "Update";
        alpine.form.delivery_partner_id = delivery_partner.id || "";
        alpine.form.name = delivery_partner.name || "";
        alpine.form.area_name = delivery_partner.area_name || "";
        alpine.form.mobile_number = delivery_partner.mobile_number || "";
    });

    $(document).on("click", ".deleteDeliveryPartner", function () {
        let id = $(this).data("id");
        let modalScope = document.querySelector("#deleteDeliveryPartnerModal")
            .__x.$data;
        modalScope.deleteId = id;
        modalScope.open = true;
    });

    window.deleteDeliveryPartner = function (id) {
        showLoader();
        sendRequest(
            "/admin/delivery_partner/delete-delivery-partner",
            { id: id },
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(
                        "Delivery Partner deleted successfully!",
                        "success",
                        2000,
                    );
                    window.location.reload();
                    reloadDeliveryPartnerList();
                } else {
                    showToast(res.message, "error", 2000);
                }
                document.querySelector(
                    "#deleteDeliveryPartnerModal",
                ).__x.$data.open = false;
            },
            function (err) {
                hideLoader();
                showToast(err.message || "Delete failed", "error", 2000);
                document.querySelector(
                    "#deleteDeliveryPartnerModal",
                ).__x.$data.open = false;
            },
        );
    };
    // ===== Helpers =====
    function reloadDeliveryPartnerList() {
        $.get("/admin/delivery_partner/delivery-partner", function (html) {
            let $tbody = $(html).find("#deliveryPartnerTableBody").html();
            $("#deliveryPartnerTableBody").html($tbody);
        });
    }

    $(document).on("change", "#area_name", function () {
        let selected = $(this).find(":selected");
        $("#hub_id").val(selected.data("hub_id"));
        $("#city_id").val(selected.data("city_id"));
        $("#hub_name_display").val(selected.data("hub_name"));
    });
});
