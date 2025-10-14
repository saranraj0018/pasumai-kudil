$(function () {
    $(document).on("submit", "#shippingAddForm", function (e) {
        alert(1);
        e.preventDefault();
        let isValid = true;

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

        let formData = new FormData(this);
        sendRequest(
            "/admin/shipping/save-shipping",
            formData,
            "POST",
            function (res) {
                if (res.success) {
                    console.log("test");
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
            },
            function (err) {
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
