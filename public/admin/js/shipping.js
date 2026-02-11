$(function () {
    $(document).on("submit", "#shippingAddForm", function (e) {
        e.preventDefault();
        let isValid = true;
        let $saveBtn = $("#shipping");
        const fields = [
            {
                id: "#city",
                condition: (val) => val === "",
                message: "City is required",
            }
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
            "/admin/shipping/save-shipping",
            formData,
            "POST",
            function (res) {
                 hideLoader();
                if (res.success) {
                    showToast("Shipping saved successfully!", "success", 2000);
                    setTimeout(() => {
                        let modalScope =
                            document.querySelector("#shippingModal").__x.$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false; // close modal
                        }
                        // Reset form
                        document.getElementById("shippingAddForm").reset();
                        window.location.reload();
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
            }
        );
    });
});
