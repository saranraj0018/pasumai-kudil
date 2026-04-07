$(function () {
    $(document).on("click", "#createUnitBtn", function () {
        document.getElementById("unitForm").reset();
        let modal = document.getElementById("unitModal");
        let alpine = modal.__x.$data;
        alpine.form.unit_name = "";
        alpine.form.unit_status = 1;
        alpine.form.unit_id = 0;
        alpine.form.unit_short_name = "";
        $("#unitModal").css("display", "flex");
        $("#unit_label").text("Add Unit");
        $("#save_unit").text("Save");
    });

    $(document).on("click", ".editUnitBtn", function () {
        let id = $(this).data("id");
        let unit_name = $(this).data("name");
        let unit_status = $(this).data("status");
        let unit_short_name = $(this).data("short_name");
        $("#unitModal").css("display", "flex");
        $("#unit_label").text("Edit Unit");
        $("#save_unit").text("Update");
        let modal = document.getElementById("unitModal");
        let alpine = modal.__x.$data;
        alpine.form.unit_name = unit_name;
        alpine.form.unit_status = unit_status;
        alpine.form.unit_id = id;
        alpine.form.unit_short_name = unit_short_name;
    });

    // Use event delegation because #unitForm may not exist initially
    $(document).on("submit", "#unitForm", function (e) {
        e.preventDefault();

        let saveBtn = $("#save_unit");
        // Fields to validate
        let fields = [
            {
                id: "#unit_name",
                condition: (val) => val === "",
                message: "Unit name is required",
            },
            {
                id: "#unit_status",
                condition: (val) => val === "",
                message: "Please select status",
            },
            {
                id: "#unit_short_name",
                condition: (val) => val === "",
                message: "Please enter a short name",
            },
        ];

        let isValid = true;
        for (const field of fields) {
            const result = validateField(field);
            if (!result) isValid = false;
        }

        if (!isValid) return;

        let formData = new FormData(this);
        saveBtn
            .prop("disabled", true)
            .removeClass("opacity-50 cursor-not-allowed")
            .text("Saving...");
        sendRequest(
            "/admin/unit/save",
            formData,
            "POST",
            function (res) {
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        $("#unitModal").hide();
                        let modal = document.querySelector("#unitModal");
                        let alpine = modal.__x.$data;
                        alpine.form = { name: "", status: "1" };
                        alpine.previewUrl = null;
                        document.getElementById("unitForm").reset();

                        $.get("/admin/unit/list", function (html) {
                            let $tbody = $(html).find("#unitTableBody").html();
                            $("#unitTableBody").html($tbody);
                        });
                    }, 500);
                } else {
                    showToast("Something went wrong!", "error", 2000);
                }
                saveBtn
                    .prop("disabled", false)
                    .removeClass("opacity-50 cursor-not-allowed")
                    .text("Saving...");
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
                saveBtn
                    .prop("disabled", false)
                    .removeClass("opacity-50 cursor-not-allowed")
                    .text("Saving...");
            },
        );
    });

    // ==== DELETE =====
    $(document).on("click", ".btnDeleteUnit", function () {
        let id = $(this).data("id");
        let modalScope = document.querySelector("#deleteUnitModal").__x.$data;
        modalScope.deleteId = id;
        modalScope.open = true;
    });

    window.deleteUnit = function (id) {
        sendRequest(
            "/admin/unit/delete",
            { id: id },
            "POST",
            function (res) {
                if (res.success) {
                    showToast("Unit deleted successfully!", "success", 2000);
                    reloadUnitList();
                } else {
                    showToast(res.message, "error", 2000);
                }
                document.querySelector("#deleteUnitModal").__x.$data.open =
                    false;
            },
            function (err) {
                showToast(err.message || "Delete failed", "error", 2000);
                document.querySelector("#deleteUnitModal").__x.$data.open =
                    false;
            },
        );
    };

    // ===== Helpers =====
    function reloadUnitList() {
        $.get("/admin/unit/list", function (html) {
            let $tbody = $(html).find("#unitTableBody").html();
            $("#unitTableBody").html($tbody);
        });
    }
});
