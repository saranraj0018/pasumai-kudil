$(document).ready(function () {
    let deliveryDayList = [];
    let deleteId = null;

    // ===== Helper functions =====
    function showFieldError(selector, message) {
        $(selector).addClass("border-red-500");
        $(selector + "-error").remove();
        $(selector).after(
            '<p id="' +
                selector.substring(1) +
                '-error" class="text-red-500 text-sm mt-1">' +
                message +
                "</p>"
        );
    }

    function clearFieldError(selector) {
        $(selector).removeClass("border-red-500");
        $(selector + "-error").remove();
    }

    function clearAllFieldErrors() {
        $(
            "#subscriptionForm input, #subscriptionForm select, #subscriptionForm textarea"
        ).each(function () {
            clearFieldError("#" + $(this).attr("id"));
        });
    }

    function validateForm() {
        let isValid = true;
        let planType = $("#plan_type").val();
        let planName = $("#plan_name").val();

        if (planType === "") {
            showFieldError("#plan_type", "Plan type is required");
            isValid = false;
        }
        if (
            $("#plan_amount").val() === "" ||
            parseFloat($("#plan_amount").val()) <= 0
        ) {
            showFieldError(
                "#plan_amount",
                "Amount is required and must be positive"
            );
            isValid = false;
        }
        if (
            $("#plan_duration").val() === "" ||
            parseInt($("#plan_duration").val()) <= 0
        ) {
            showFieldError(
                "#plan_duration",
                "Duration is required and must be positive"
            );
            isValid = false;
        }
        if (
            planType !== "Customize" &&
            ($("#plan_pack").val() === "" ||
                parseInt($("#plan_pack").val()) <= 0)
        ) {
            showFieldError(
                "#plan_pack",
                "Plan pack is required and must be positive"
            );
            isValid = false;
        }

        if(planName == ""){
            showFieldError("#plan_name", "Plan name is required");
            isValid = false;
        }

        return isValid;
    }

    // ===== Modal open/close =====
    function openModal() {
        $("#subscriptionModal").show();
    }

    function closeModal() {
        $("#subscriptionModal").hide();
        $("#subscriptionForm")[0].reset();
        $("#delivery_days_list").empty();
        deliveryDayList = [];
        $("#plan_pack_container").show();
        $("#delivery_days_container").hide();
        clearAllFieldErrors();
    }

    $("#createSubscriptionBtn, #cancelSubscriptionModal").click(closeModal);

    // ===== Plan type change =====
    $("#plan_type").on("change", function () {
        if ($(this).val() === "Customize") {
            $("#plan_pack_container").hide();
            $("#delivery_days_container").show();
        } else {
            $("#plan_pack_container").show();
            $("#delivery_days_container").hide();
            deliveryDayList = [];
            $("#delivery_days_list").empty();
        }
    });

    // ===== Add delivery day =====
    $("#add_delivery_day_btn").on("click", function () {
        let val = parseInt($("#delivery_days_input").val());
        if (!isNaN(val) && val > 0) {
            deliveryDayList.push(val);

            let $daySpan = $(`
            <span class="inline-flex items-center bg-gray-200 px-2 py-1 rounded m-1">
                ${val} Days
                <button type="button" class="ml-2 text-red-500 remove-delivery-day">&times;</button>
            </span>
        `);

            // Add remove handler
            $daySpan.find(".remove-delivery-day").on("click", function () {
                let index = deliveryDayList.indexOf(val);
                if (index > -1) deliveryDayList.splice(index, 1);
                $daySpan.remove();
            });

            $("#delivery_days_list").append($daySpan);
            $("#delivery_days_input").val("");
        }
    });

    // ===== Open create modal =====
    $("#createSubscriptionBtn").on("click", function () {
        closeModal();
        $("#subscription_label").text("Add Subscription");
        $("#save_subscription").text("Save");
        openModal();
    });

    // ===== Open edit modal =====
    $(document).on("click", ".editSubscriptionBtn", function () {
        closeModal();
        let btn = $(this);
        $("#subscription_title").text("Edit Subscription");
        $("#subscription_id").val(btn.data("id"));
        $("#plan_type").val(btn.data("type"));
        $("#plan_amount").val(btn.data("amount"));
        $("#plan_pack").val(btn.data("pack"));
        $("#plan_duration").val(btn.data("duration"));
        $("#plan_details").val(btn.data("details"));
        $("#quantity").val(btn.data("quantity"));
        $("#pack").val(btn.data("pack_details"));
        $("#plan_name").val(btn.data("plan_name"));
        var isShowMobile = btn.data("is_show_mobile");
        $("#is_show_mobile").prop('checked', isShowMobile == 1);
        // Delivery days fix for Customize
        deliveryDayList = [];
        $("#delivery_days_list").empty();
        if (
            btn.data("type") === "Customize" &&
            btn.attr("data-delivery_days")
        ) {
            let days = JSON.parse(btn.attr("data-delivery_days"));
            days.forEach((d) => {
                deliveryDayList.push(d);

                let $daySpan = $(`
            <span class="inline-flex items-center bg-gray-200 px-2 py-1 rounded m-1">
                ${d} Days
                <button type="button" class="ml-2 text-red-500 remove-delivery-day">&times;</button>
            </span>
        `);

                $daySpan.find(".remove-delivery-day").on("click", function () {
                    let index = deliveryDayList.indexOf(d);
                    if (index > -1) deliveryDayList.splice(index, 1);
                    $daySpan.remove();
                });

                $("#delivery_days_list").append($daySpan);
            });
            $("#plan_pack_container").hide();
            $("#delivery_days_container").show();
        }

        $("#subscription_label").text("Edit Subscription");
        $("#save_subscription").text("Update");
        openModal();
    });

    // ===== Form submit =====
    $(document).on("submit", "#subscriptionForm", function (e) {
        e.preventDefault();
        clearAllFieldErrors();

        if (!validateForm()) return;

        let formData = new FormData(this);
        formData.append("_token", $("input[name=_token]").val());

        if ($("#plan_type").val() === "Customize") {
            formData.set("delivery_days", JSON.stringify(deliveryDayList));
        }

        sendRequest(
            "/admin/milk/save",
            formData,
            "POST",
            function (res) {
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        closeModal();
                        reloadSubscriptionList();
                    }, 500);
                } else if (res.errors) {
                    $.each(res.errors, function (k, v) {
                        showFieldError("#" + k, v[0]);
                    });
                } else {
                    showToast("Something went wrong!", "error", 2000);
                }
            },
            function (err) {
                showToast(err.message || "Unexpected error", "error", 2000);
            }
        );
    });

    // ===== Delete subscription =====
    $(document).on("click", ".btnDeleteSubscription", function () {
        deleteId = $(this).data("id");
        $("#deleteSubscriptionModal").show();
    });

    $(document).on("click", "#cancelDeleteBtn", function () {
        deleteId = null;
        $("#deleteSubscriptionModal").hide();
    });

    $(document).on("click", "#confirmDeleteBtn", function () {
        if (!deleteId) return;
        sendRequest(
            "/admin/milk/delete",
            { id: deleteId },
            "POST",
            function (res) {
                if (res.success) {
                    showToast(
                        "Subscription deleted successfully!",
                        "success",
                        2000
                    );
                    reloadSubscriptionList();
                } else {
                    showToast(res.message, "error", 2000);
                }
                deleteId = null;
                $("#deleteSubscriptionModal").hide();
            },
            function (err) {
                showToast(err.message || "Delete failed", "error", 2000);
                deleteId = null;
                $("#deleteSubscriptionModal").hide();
            }
        );
    });

    // ===== Reload subscription list =====
    function reloadSubscriptionList() {
        $.get("/admin/milk/subscription", function (html) {
            let $tbody = $(html).find("#subscriptionTableBody").html();
            $("#subscriptionTableBody").html($tbody);
        });
    }
});
