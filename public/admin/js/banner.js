$(function () {

    // ==== CREATE BANNER ====
    $(document).on("click", "#createBannerBtn", function () {
        document.getElementById("bannerForm").reset();
        let modal = document.getElementById("bannerModal");
        let alpine = modal.__x.$data;
        alpine.previewUrl = '';
        alpine.existing_image = '';
        alpine.form.type = 'GroceryMain';
        alpine.form.banner_id = 0;
        $("#bannerModal").css("display", "flex");
        $("#banner_label").text("Add Banner");
        $("#save_banner").text("Save");
    });

    // ==== EDIT BANNER ====
    $(document).on("click", ".editBannerBtn", function () {
        let id = $(this).data("id");
        let type = $(this).data("type");
        let image = $(this).data("image");

        $("#bannerModal").css("display", "flex");
        $("#banner_label").text("Edit Banner");
        $("#save_banner").text("Update");

        let modal = document.getElementById("bannerModal");
        let alpine = modal.__x.$data;
        alpine.previewUrl = image || '';
        alpine.existing_image = image || '';
        alpine.form.type = type;
        alpine.form.banner_id = id;
    });

    // ==== SAVE BANNER ====
    $(document).on("submit", "#bannerForm", function (e) {
        e.preventDefault();

        // Basic validation
        let fields = [
            { id: "#banner_type", condition: val => val === "", message: "Banner type is required" },
            { id: "#banner_image", condition: val => val === "", message: "Please upload an image" }
        ];

        if ($('#existing_image').val()) {
            fields = fields.filter(field => field.id !== "#banner_image");
        }

        let isValid = true;
        for (const field of fields) {
            const result = validateField(field);
            if (!result) isValid = false;
        }
        if (!isValid) return;
         showLoader();
        let formData = new FormData(this);
        sendRequest(
            "/admin/banner/save",
            formData,
            "POST",
            function (res) {
                 hideLoader();
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        $('#bannerModal').hide();
                        let modal = document.querySelector('#bannerModal');
                        let alpine = modal.__x.$data;
                        alpine.form = { type: 'main', banner_id: 0 };
                        alpine.previewUrl = null;
                        document.getElementById("bannerForm").reset();

                        // Reload table
                        $.get("/admin/banner/list", function (html) {
                            let $tbody = $(html).find("#bannerTableBody").html();
                            $("#bannerTableBody").html($tbody);
                        });
                    }, 500);
                } else {
                    showToast("Something went wrong!", "error", 2000);
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

    // ==== DELETE BANNER ====
    $(document).on("click", ".btnDeleteBanner", function () {
        let id = $(this).data("id");
        let modalScope = document.querySelector('#deleteBannerModal').__x.$data;
        modalScope.deleteId = id;
        modalScope.open = true;
    });

    window.deleteBanner = function (id) {
         showLoader();
        sendRequest(
            "/admin/banner/delete",
            { id: id },
            "POST",
            function (res) {
                 hideLoader();
                if (res.success) {
                    showToast("Banner deleted successfully!", "success", 2000);
                    reloadBannerList();
                } else {
                    showToast(res.message, "error", 2000);
                }
                document.querySelector('#deleteBannerModal').__x.$data.open = false;
            },
            function (err) {
                 hideLoader();
                showToast(err.message || "Delete failed", "error", 2000);
                document.querySelector('#deleteBannerModal').__x.$data.open = false;
            }
        );
    };

    // ==== HELPERS ====
    function reloadBannerList() {
        $.get("/admin/banner/list", function (html) {
            let $tbody = $(html).find("#bannerTableBody").html();
            $("#bannerTableBody").html($tbody);
        });
    }
});
