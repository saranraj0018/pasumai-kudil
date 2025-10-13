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
});
