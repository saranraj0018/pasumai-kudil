$(function () {
    $(document).on("submit", "#userAddForm", function (e) {
        e.preventDefault();

        let isValid = true;
        let $saveBtn = $("#save_edit_user");

        const fields = [
            {
                id: "#prefix",
                condition: (val) => val === "",
                message: "User Code Prefix is required",
            },
            {
                id: "#name",
                condition: (val) => val === "",
                message: "User Name is required",
            },
            {
                id: "#mobile_number",
                condition: (val) => val === "",
                message: "Mobile Number is required",
            },
            {
                id: "#plan_id",
                condition: (val) => val === "",
                message: "Plan Name is required",
            },
            {
                id: "#cityInput",
                condition: (val) => val === "",
                message: "City is required",
            },
        ];

        fields.forEach((field) => {
            if (!validateField(field)) isValid = false;
        });

        if (!isValid) return;
        $saveBtn.prop("disabled", true).addClass("opacity-50 cursor-not-allowed").text("Saving...");
        let formData = new FormData(this);
        showLoader();
        sendRequest(
            "/admin/users/save_user",
            formData,
            "POST",
            function (res) {
                 hideLoader();
                if (res.success) {
                    showToast("User saved successfully!", "success", 2000);
                    setTimeout(() => {
                        let modalScope =
                            document.querySelector("#userCreateModal").__x
                                .$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false; // close modal
                        }
                        // Reset form
                        document.getElementById("productAddForm").reset();
                        $.get("/admin/users/lists", function (html) {
                            let $tbody = $(html)
                                .find("#userTableBody")
                                .html();
                            $("#userTableBody").html($tbody);
                        });
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

    $(document).on("click", ".editUserBtn", function () {
        $("#edit_user_id").val($(this).data("id"));
        $("#prefix_id").val($(this).data("prefix_id"));
        $("#user_name").val($(this).data("name"));
        $("#user_email").val($(this).data("email"));

        $("#coupon_label").text("Edit Coupon");
        $("#save_coupon").text("Update");

        let modal = document.getElementById("editUserModal");
        let alpine = modal.__x.$data;
        alpine.open = true;
    });

    // ==== SAVE BANNER ====
    $(document).on("submit", "#userForm", function (e) {
        e.preventDefault();

        // Basic validation
        let fields = [
            { id: "#prefix_id", condition: val => val === "", message: "User Id is required" },
            { id: "#user_name", condition: val => val === "", message: "User Name is required" },
            { id: "#user_name", condition: val => val === "", message: "User Email is required" },
        ];

        let isValid = true;
        for (const field of fields) {
            const result = validateField(field);
            if (!result) isValid = false;
        }
        if (!isValid) return;
        showLoader();
        let formData = new FormData(this);
        sendRequest(
            "/admin/users/update",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        let modalScope = document.querySelector('#editUserModal').__x.$data;
                        modalScope.open = false; // close modal
                        document.getElementById("userForm").reset();
                        $.get("/admin/users/lists", function (html) {
                            let $tbody = $(html)
                                .find("#userTableBody")
                                .html();
                            $("#userTableBody").html($tbody);
                        });
                    }, 500);
                } else {
                    showToast(res.message, "error", 2000);
                }
            },
            function (err) {
                hideLoader();
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

    $(document).on("change", "#plan_id", function (e) {
        e.preventDefault();
       showLoader();
        let subs_id = $(this).val();
        $.ajax({
            url: "get_subscription",
            type: "GET",
            data: {
                subs_id: subs_id,
                get_custom_subscription: true,
            },
            success: function (response) {
                 hideLoader();
                $("#custom_plan_days").empty();
               if (response.subs.delivery_days) {
                   try {
                       let days = JSON.parse(response.subs.delivery_days);
                       if (Array.isArray(days) && days.length > 0) {
                           let selectHtml = `
                <label for="custom_days" class="block text-sm font-medium text-gray-700 mb-1">
                    Select Delivery Day
                </label>
                <select id="custom_days" name="custom_days" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">-- Select Day --</option>
                    ${days
                        .map(
                            (item) =>
                                `<option value="${item.days}">${item.days} Days - ₹${item.amount}</option>`
                        )
                        .join("")}
                </select>
            `;

                           // Clear existing dropdown before appending new one (optional)
                           $("#custom_plan_days").empty().append(selectHtml);
                       }
                   } catch (err) {
                       console.error(
                           "Invalid JSON in response.delivery_days:",
                           err
                       );
                   }
               }

            },
            error: function (xhr) {
                 hideLoader();
                console.error(xhr.responseText);
            },
        });
    });
});
