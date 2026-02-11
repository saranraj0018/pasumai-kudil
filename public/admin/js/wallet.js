$(function () {
    $(document).on("submit", "#walletAddForm", function (e) {
        e.preventDefault();
        let isValid = true;
        let $saveBtn = $("#save_wallet");

        const fields = [
            {
                id: "#type",
                condition: (val) => val === "",
                message: "Type is required",
            },
            {
                id: "#amount",
                condition: (val) => val === "",
                message: "Amount is required",
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
            "/admin/users/add_wallet",
            formData,
            "POST",
            function (res) {
                 hideLoader();
                if (res.success) {
                    showToast("Wallet added successfully!", "success", 2000);
                    setTimeout(() => {
                        let modalScope =
                            document.querySelector("#addWalletModal").__x.$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false; // close modal
                        }
                        // Reset form
                        document.getElementById("walletAddForm").reset();
                        window.location.reload();
                    }, 500);
                } else if (res.success == 'fasle') {
                    showToast("Something went wrong!", "error", 2000);
                } else {
                    showToast(res.message, "error", 2000);
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

    $(document).on("submit", "#accountAddForm", function (e) {
        e.preventDefault();
        let isValid = true;
        let $saveBtn = $("#save_account");

        const fields = [
            {
                id: "#account_holder_name",
                condition: (val) => val === "",
                message: "Account Holder Name is required",
            },
            {
                id: "#bank_name",
                condition: (val) => val === "",
                message: "Bank Name is required",
            },
            {
                id: "#account_number",
                condition: (val) => val === "",
                message: "Account Number is required",
            },
            {
                id: "#confirm_account_number",
                condition: (val) => val === "",
                message: "Confirm Account Number is required",
            },
            {
                id: "#ifsc_code",
                condition: (val) => val === "",
                message: "IFSC Code is required",
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
            "/admin/users/add_user_account",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast("Account Details added successfully!", "success", 2000);
                    setTimeout(() => {
                        let modalScope =
                            document.querySelector("#addAccountModal").__x.$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false; // close modal
                        }
                        // Reset form
                        document.getElementById("accountAddForm").reset();
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

    $(document).on("click", ".update_account", function (e) {
        let account = {
            id: $(this).data("id"),
            account_holder_name: $(this).data("account_holder_name"),
            account_number: $(this).data("account_number"),
            ifsc_code: $(this).data("ifsc_code"),
            bank_name: $(this).data("bank_name"),
            upi: $(this).data("upi"),
        };
        // Show modal
        $("#addAccountModal").css("display", "flex");

        let modal = document.getElementById("addAccountModal");
        let alpine = modal.__x.$data;
        alpine.open = true;
        alpine.modalTitle = "Edit Account Details";
        alpine.buttonText = "Update";
        alpine.form.account_holder_name = account.account_holder_name || "";
        alpine.form.account_number = account.account_number || "";
        alpine.form.confirm_account_number = account.account_number || "";
        alpine.form.ifsc_code = account.ifsc_code || "";
        alpine.form.bank_name = account.bank_name || "";
        alpine.form.upi = account.upi || "";
    });

    $(document).on("click", ".add_account", function (e) {
        $("#addAccountModal").css("display", "flex");
        let modal = document.getElementById("addAccountModal");
        let alpine = modal.__x.$data;
        alpine.open = true;
        alpine.modalTitle = "Add Account Details";
        alpine.buttonText = "Save";
    });

    $(document).on("submit", "#subscriptionCancelForm", function (e) {
        e.preventDefault();
        let isValid = true;
        let $saveBtn = $("#save_subscription");

        const fields = [
            {
                id: "#status",
                condition: (val) => val === "",
                message: "Status field is required",
            },
            {
                id: "#description",
                condition: (val) => val === "",
                message: "Description field is required",
            },
        ];

        fields.forEach((field) => {
            if (!validateField(field)) isValid = false;
        });

        if (!isValid) return;
     $saveBtn.prop("disabled", true).removeClass("opacity-50 cursor-not-allowed").text("Saving....");
        let formData = new FormData(this);
        showLoader();
        sendRequest(
            "/admin/users/subscription_cancel",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast("Subscription cancelled successfully!", "success", 2000);
                    setTimeout(() => {
                        let modalScope = document.querySelector(
                            "#addSubscriptionModal"
                        ).__x.$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false; // close modal
                        }
                        // Reset form
                        document.getElementById("subscriptionCancelForm").reset();
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
                    window.location.reload();
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

    $(document).on("submit", "#revokeForm", function (e) {
        e.preventDefault();
        let $saveBtn = $("#revoke_form");
        $saveBtn
                .prop("disabled", true)
                .removeClass("opacity-50 cursor-not-allowed")
                .text("Saving....");
        let formData = new FormData(this);
        showLoader();
        sendRequest(
            "/admin/users/revoke",
            formData,
            "POST",
            function (res) {
                 hideLoader();
                if (res.success) {
                    showToast("Cancellation revoked successfully!", "success", 2000);
                    setTimeout(() => {
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
                    window.location.reload();
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


    $(document).on("submit", "#modifySubscriptionForm", function (e) {
        e.preventDefault();
        let isValid = true;
        let $saveBtn = $("#save_modify");
        const fields = [
            {
                id: "#date_range",
                condition: (val) => val === "",
                message: "Date field is required",
            },
            {
                id: "#description",
                condition: (val) => val === "",
                message: "Description field is required",
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
            "/admin/users/modify_subscription",
            formData,
            "POST",
            function (res) {
                 hideLoader();
                if (res.success) {
                    showToast(
                        "Subscription date has been modified successfully!",
                        "success",
                        2000
                    );
                    setTimeout(() => {
                        let modalScope = document.querySelector(
                            "#modifySubscriptionModal"
                        ).__x.$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false; // close modal
                        }
                        // Reset form
                        document
                            .getElementById("modifySubscriptionForm")
                            .reset();
                        window.location.reload();
                    }, 500);
                } else {
                    showToast(res.message, "error", 2000);
                    setTimeout(() => {
                        let modalScope = document.querySelector(
                            "#modifySubscriptionModal"
                        ).__x.$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false; // close modal
                        }
                        // Reset form
                        document
                            .getElementById("modifySubscriptionForm")
                            .reset();
                        window.location.reload();
                    }, 500);
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
                    window.location.reload();
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

    document.addEventListener("alpine:init", () => {
        Alpine.data("dateRangePicker", () => ({
            init() {
                flatpickr("#date_range", {
                    mode: "range",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "F j, Y",
                    allowInput: true,
                    onChange: function (selectedDates, dateStr) {
                        // Optional: console log selected range

                    },
                });
            },
        }));
    });

    $("#removeWalletBtn").click(function () {
        var id = new URLSearchParams(window.location.search).get("id");
        $.confirm({
            title: 'Confirm!',
            content: 'Are you sure you want to remove your previous wallet amount?',
            type: 'orange',

            boxWidth: '500px',     // 👈 CUSTOM POPUP WIDTH
            useBootstrap: false,   // 👈 IMPORTANT otherwise width won't work

            buttons: {
                yes: {
                    text: 'Yes, Remove',
                    btnClass: 'btn-red',
                    action: function () {
                        $.ajax({
                            url: "/admin/users/remove-previous-wallet",
                            type: "POST",
                            data: { id:id },
                            success: function (res) {
                                if (res.success) {
                                    $.alert({
                                        title: 'Success!',
                                        type: 'green',
                                        content: 'Previous wallet amount removed successfully!',
                                        boxWidth: '450px',
                                        useBootstrap: false,
                                        buttons: {
                                            ok: {
                                                text: 'OK',
                                                action: function () {
                                                    location.reload(); // 🔥 Reload page on OK
                                                }
                                            }
                                        }
                                    });
                                } else {
                                    $.alert({
                                        title: 'Error!',
                                        type: 'red',
                                        content: res.message,
                                        boxWidth: '450px',
                                        useBootstrap: false
                                    });
                                }
                            },
                            error: function () {
                                $.alert({
                                    title: 'Error!',
                                    type: 'red',
                                    content: 'Something went wrong. Try again.',
                                    boxWidth: '350px',
                                    useBootstrap: false
                                });
                            }
                        });

                    }
                },
                no: {
                    text: 'No',
                    action: function () {}
                }
            }
        });

    });

});
