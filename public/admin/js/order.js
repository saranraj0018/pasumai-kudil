$(function () {

    // ===== OPEN VIEW ORDER MODAL =====
    $(document).on("click", ".viewOrderBtn", function () {

        let order = $(this).data("order");

        // Fill order data inside modal
        $("#orderModalTitle").text("Order #" + order.order_id);
        $("#orderCustomerName").text(order.user?.name ?? "Guest");
        $("#orderCustomerEmail").text(order.user?.email ?? "—");
        $("#orderCustomerMobile").text(order.phone ?? "—");
        $("#orderBillingAddress").text(order.address?.full_address ?? "—");

        $("#orderSubtotal").text("₹" + (order.net_amount ?? 0));
        $("#orderGST").text("₹" + (order.gst_amount ?? 0));
        $("#orderShipping").text("₹" + (order.shipping_amount ?? 0));
        $("#orderCoupon").text("-₹" + (order.coupon_amount ?? 0));
        $("#orderGrandTotal").text("₹" + (order.gross_amount ?? 0));

        // status & date
        $("#status").val(order.status);
        let statusDate = "";
        switch (order.status) {
            case 3: statusDate = order.shipped_at; break;
            case 4: statusDate = order.delivered_at; break;
            case 5: statusDate = order.cancelled_at; break;
            case 6: statusDate = order.refunded_at; break;
            default: statusDate = ""; break;
        }
        if (statusDate) {
        let dt = new Date(statusDate);
        let yyyy = dt.getFullYear();
        let mm = ("0" + (dt.getMonth() + 1)).slice(-2);
        let dd = ("0" + dt.getDate()).slice(-2);
        $("#statusDate").val(`${yyyy}-${mm}-${dd}`);
    } else {
        $("#statusDate").val("");
    }
        // Show modal
        $("#orderModal").fadeIn(200);
    });

    // ===== CLOSE MODAL =====
    $(document).on("click", "#closeModalBtn, #cancelModalBtn, #orderModalBackdrop", function () {
        $("#orderModal").fadeOut(200);
    });

    // ===== SAVE ORDER STATUS =====
    $(document).on("click", "#saveStatusBtn", function (e) {
        e.preventDefault();

        let orderTitle = $("#orderModalTitle").text();
        let orderId = orderTitle.replace("Order #", "").trim();

        let status = $("#status").val();
        let date = $("#statusDate").val();

        if (!status || !date) {
            showToast("Please select both status and date", "error", 2000);
            return;
        }

        let formData = new FormData();
        formData.append("order_id", orderId);
        formData.append("status", status);
        formData.append("date", date);

        sendRequest(
            "/admin/orders/update-status",
            formData,
            "POST",
            function (res) {
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        $("#orderModal").fadeOut(200);
                        reloadOrderList();
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

    // ===== HELPER: RELOAD ORDER LIST =====
    function reloadOrderList() {
        $.get("/admin/orders/list", function (html) {
            let $tbody = $(html).find("#orderTableBody").html();
            $("#orderTableBody").html($tbody);
        });
    }

});
