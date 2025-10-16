$(function () {
    $(document).on("submit", "#walletAddForm", function (e) {
        e.preventDefault();
        let isValid = true;

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

        let formData = new FormData(this);
        sendRequest(
            "/admin/users/add_wallet",
            formData,
            "POST",
            function (res) {
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
                } else {
                    showToast("Something went wrong!", "error", 2000);
                }
            },
            function (err) {
                console.log(err.errors);
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

    $(document).on("submit", "#accountAddForm", function (e) {
        e.preventDefault();
        let isValid = true;

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

        let formData = new FormData(this);
        sendRequest(
            "/admin/users/add_user_account",
            formData,
            "POST",
            function (res) {
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
            },
            function (err) {
                console.log(err.errors);
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

    $(document).on("click", ".update_account", function (e) {
        let account = {
            id: $(this).data("id"),
            account_holder_name: $(this).data("account_holder_name"),
            account_number: $(this).data("account_number"),
            ifsc_code: $(this).data("ifsc_code"),
            bank_name: $(this).data("bank_name"),
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
    });

    $(document).on("click", ".add_account", function (e) {
        $("#addAccountModal").css("display", "flex");
        let modal = document.getElementById("addAccountModal");
        let alpine = modal.__x.$data;
        alpine.open = true;
        alpine.modalTitle = "Add Account Details";
        alpine.buttonText = "Save";
    });
});
