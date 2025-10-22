$(function () {

    $(document).on("submit", "#userAddForm", function (e) {
        e.preventDefault();
        let isValid = true;

        const fields = [
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

        let formData = new FormData(this);
        sendRequest(
            "/admin/users/save_user",
            formData,
            "POST",
            function (res) {
                if (res.success) {
                    showToast("User saved successfully!", "success", 2000);
                    setTimeout(() => {
                        let modalScope = document.querySelector(
                            "#userCreateModal"
                        ).__x.$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false; // close modal
                        }
                        // Reset form
                        document.getElementById("productAddForm").reset();
                        $.get("/admin/users/users", function (html) {
                            let $tbody = $(html)
                                .find("#userListTableBody")
                                .html();
                            $("#userListTableBody").html($tbody);
                        });
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

    $(document).on("change", "#plan_id", function (e) {
    e.preventDefault();

    let subs_id = $(this).val();

    $.ajax({
        url: "users/get_subscription",
        type: "GET",
        data: {
            subs_id: subs_id,
            get_custom_subscription: true
        },
        success: function (response) {

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
                                ${days.map(day => `<option value="${day}">${day} Days</option>`).join('')}
                            </select>
                        `;

                        // Append to div
                        $("#custom_plan_days").append(selectHtml);
                    }
                } catch (err) {
                    console.error("Invalid JSON in response.delivery_days:", err);
                }
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
        },
    });
});
});


