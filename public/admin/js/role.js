$(function () {
    $(document).on("click", "#createRole", function () {
        document.getElementById("roleForm").reset();
        let modal = document.getElementById("rolesModal");
        let alpine = modal.__x.$data;
        alpine.form.name = "";
        alpine.form.role_id = 0;
        $("#rolesModal").css("display", "flex");
        $("#role_label").text("Add Role");
        $("#save_role").text("Save");
    });

    $(document).on("click", ".editRoleBtn", function () {
        let id = $(this).data("id");
        let name = $(this).data("name");

        // open modal
        $("#rolesModal").css("display", "flex");
        $("#role_label").text("Edit Role");
        $("#save_role").text("Update");

        // update Alpine state for preview
        let modal = document.getElementById("rolesModal");
        let alpine = modal.__x.$data;
        alpine.form.name = name;
        alpine.form.role_id = id;
    });

    // Use event delegation because #categoryForm may not exist initially
    $(document).on("submit", "#roleForm", function (e) {
        e.preventDefault();
        let $saveBtn = $("#save_role");

        let fields = [
            {
                id: "#role_name",
                condition: (val) => val === "",
                message: "Role name is required",
            },
        ];

        let isValid = true;
        for (const field of fields) {
            const result = validateField(field); // synchronous, so no async/await needed
            if (!result) isValid = false;
        }

        if (!isValid) return;
        $saveBtn
            .prop("disabled", true)
            .removeClass("opacity-50 cursor-not-allowed")
            .text("Saving....");
        showLoader();
        let formData = new FormData(this);
        sendRequest(
            "/admin/roles-save",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        $("#rolesModal").hide();
                        // Reset the form so next time it's clean
                        let modal = document.querySelector("#rolesModal");
                        let alpine = modal.__x.$data;
                        alpine.form = { name: "" };
                        document.getElementById("roleForm").reset();

                        $.get("/admin/roles-list", function (html) {
                            let $tbody = $(html).find("#roleTableBody").html();
                            $("#roleTableBody").html($tbody);
                        });
                    }, 500);
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
            },
        );
    });

    // Global Select All
    document
        .getElementById("globalSelectAll")
        .addEventListener("change", function () {
            let checked = this.checked;
            document
                .querySelectorAll(
                    ".ability-checkbox, .main-menu-checkbox, .select-all-submenu",
                )
                .forEach((cb) => {
                    cb.checked = checked;
                });
        });

    // Main menu permission also controls child permissions
    document.querySelectorAll(".main-menu-checkbox").forEach((menu) => {
        menu.addEventListener("change", function () {
            let menuId = this.dataset.menu;
            let checked = this.checked;
            document.querySelectorAll(".child-" + menuId).forEach((child) => {
                child.checked = checked;
            });
            document.querySelectorAll(
                '.select-all-submenu[data-menu="' + menuId + '"]',
            )[0].checked = checked;
        });
    });

    // Select all submenu permissions
    document.querySelectorAll(".select-all-submenu").forEach((selectAll) => {
        selectAll.addEventListener("change", function () {
            let menuId = this.dataset.menu;
            let checked = this.checked;
            document.querySelectorAll(".child-" + menuId).forEach((child) => {
                child.checked = checked;
            });
        });
    });
});
