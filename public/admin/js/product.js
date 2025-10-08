$(function () {
    $(document).on("keyup", ".search_product", function (e) {
        let query = $(this).val();

        $.ajax({
            url: "search_product",
            type: "GET",
            data: { query: query },
            success: function (response) {
                if (response.success) {
                    $("#productTableBody").html(response.html);
                    $(".p-4").html(response.pagination);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            },
        });
    });

    $(document).on("submit", "#productAddForm", function (e) {
        e.preventDefault();
        let isValid = true;

        const fields = [
            {
                id: "#product_name",
                condition: (val) => val === "",
                message: "Product Name is required",
            },
            {
                id: "#category_id",
                condition: (val) => val === "",
                message: "Please select category",
            },
            {
                id: "#sale_price",
                condition: (val) => val === "",
                message: "Sale Price is required",
            },
            {
                id: "#regular_price",
                condition: (val) => val === "",
                message: "Regular Price is required",
            },
            {
                id: "#purchase_price",
                condition: (val) => val === "",
                message: "Purchase Price is required",
            },
        ];

        fields.forEach((field) => {
            if (!validateField(field)) isValid = false;
        });

        if (!isValid) return;

        let formData = new FormData(this);
        sendRequest(
            "/admin/products/save_product",
            formData,
            "POST",
            function (res) {
                if (res.success) {
                    showToast("Product saved successfully!", "success", 2000);
                    setTimeout(() => {
                        let modalScope = document.querySelector(
                            "#productCreateModal"
                        ).__x.$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false; // close modal
                        }
                        // Reset form
                        document.getElementById("productAddForm").reset();
                        $.get("/admin/products/lists", function (html) {
                            let $tbody = $(html)
                                .find("#productTableBody")
                                .html();
                            $("#productTableBody").html($tbody);
                        });
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

    $(document).on("click", ".editProduct", function () {
        let product_id = $(this).data("id");
        let product_name = $(this).data("name");
        let category = $(this).data("category");
        let description = $(this).data("description");
        let benefits = $(this).data("benefits");
        let sale_price = $(this).data("sale_price");
        let regular_price = $(this).data("regular_price");
        let purchase_price = $(this).data("purchase_price");
        let weight = $(this).data("weight");
        let weight_unit = $(this).data("weight_unit");
        let stock = $(this).data("stock");
        let tax_type = $(this).data("tax_type");
        let tax_percentage = $(this).data("tax_percentage");
        let is_featured = $(this).data("is_featured");
        let image = $(this).data("image");

        // open modal

        // update Alpine state for preview
        let modal = document.getElementById("productCreateModal");
        let alpine = modal.__x.$data;
        $("#productCreateModal").css("display", "flex");
        $("#product_label").text("Edit Category");
        $("#save_product").text("Update");

        alpine.open = true;
        alpine.exiting_image = image || "";
        alpine.form.name = product_name;
        alpine.form.category_id = category;
        alpine.description = description;
        alpine.form.benefits = benefits;
        alpine.form.sale_price = sale_price;
        alpine.form.regular_price = regular_price;
        alpine.purchase_price = purchase_price;
        alpine.form.weight = weight;
        alpine.form.weight_unit = weight_unit;
        alpine.form.tax_percentage = tax_percentage;
        alpine.form.is_featured_product = is_featured;
        alpine.form.tax_type = tax_type;
        alpine.form.product_id = product_id;
    });

    // ===== Coupon DELETE =====
    $(document).on("click", ".deleteProduct", function () {
        let id = $(this).data("id");
        let modalScope = document.querySelector("#deleteProductModal").__x
            .$data;
        modalScope.deleteId = id;
        modalScope.open = true;
    });

    window.deleteCoupon = function (id) {
        sendRequest(
            "/admin/products/delete_product",
            { id: id },
            "POST",
            function (res) {
                if (res.success) {
                    showToast("Product deleted successfully!", "success", 2000);
                    reloadProductList();
                } else {
                    showToast(res.message, "error", 2000);
                }
                document.querySelector(
                    "#deleteProductModal"
                ).__x.$data.open = false;
            },
            function (err) {
                showToast(err.message || "Delete failed", "error", 2000);
                document.querySelector(
                    "#deleteProductModal"
                ).__x.$data.open = false;
            }
        );
    };

    // ===== Helpers =====
    function reloadProductList() {
        $.get("/admin/products/lists", function (html) {
            let $tbody = $(html).find("#productTableBody").html();
            $("#productTableBody").html($tbody);
        });
    }
});
