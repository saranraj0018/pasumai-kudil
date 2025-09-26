$(function () {
    // Coupon form submit
    $(document).on("submit", "#couponForm", function (e) {
        e.preventDefault();
        let isValid = true;

        // Fields to validate
        const fields = [
            { id: "#coupon_code", condition: (val) => val === "", message: "Coupon code is required" },
            { id: "#discount_type", condition: (val) => val === "", message: "Please select discount type" },
            { id: "#discount_value", condition: (val) => val === "" || val <= 0, message: "Discount value is required" },
            { id: "#description", condition: (val) => val === "", message: "Description is required" },
            { id: "#apply_for", condition: (val) => val === "", message: "Please select apply for" },
            { id: "#expires_at", condition: (val) => val === "", message: "Expiry date is required" },
            { id: "#status", condition: (val) => val === "", message: "Please select status" },
        ];

        fields.forEach((field) => {
            if (!validateField(field)) isValid = false;
        });

        if (!isValid) return;

        let formData = new FormData(this);
        sendRequest(
            "/admin/coupon/save",
            formData,
            "POST",
            function (res) {
                if (res.success) {
                    showToast("Coupon saved successfully!", "success", 2000);
                    setTimeout(() => {
                        let modalScope = document.querySelector('#couponModal').__x.$data;
                        if (modalScope.hasOwnProperty('open')) {
                            modalScope.open = false; // close modal
                        }
                        // Reset form
                        document.getElementById("couponForm").reset();
                        $.get("/admin/coupon/list", function (html) {
                            let $tbody = $(html).find("#couponTableBody").html();
                            $("#couponTableBody").html($tbody);
                        });
                    }, 500);
                } else {
                    showToast("Something went wrong!", "error", 2000);
                }
            },
            function (err) {
                if (err.errors) {
                    let msg = "";
                    $.each(err.errors, function (k, v) { msg += v[0] + "<br>"; });
                    showToast(msg, "error", 2000);
                } else {
                    showToast(err.message || "Unexpected error", "error", 2000);
                }
            }
        );
    });
   

    // ===== Coupon DELETE =====
    $(document).on("click", ".btnDeleteCoupon", function () {
        let id = $(this).data("id");
        let modalScope = document.querySelector('#deleteCouponModal').__x.$data;
        modalScope.deleteId = id;
        modalScope.open = true;
    });

    window.deleteCoupon = function (id) {
        sendRequest(
            "/admin/coupon/delete",
            { id: id },
            "POST",
            function (res) {
                if (res.success) {
                    showToast("Coupon deleted successfully!", "success", 2000);
                    reloadCouponList();
                } else {
                    showToast(res.message, "error", 2000);
                }
                document.querySelector('#deleteCouponModal').__x.$data.open = false;
            },
            function (err) {
                showToast(err.message || "Delete failed", "error", 2000);
                document.querySelector('#deleteCouponModal').__x.$data.open = false;
            }
        );
    };


    // ===== Helpers =====
    function reloadCouponList() {
        $.get("/admin/coupon/list", function (html) {
            let $tbody = $(html).find("#couponTableBody").html();
            $("#couponTableBody").html($tbody);
        });
    }

});


