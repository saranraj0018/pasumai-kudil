$(function () {
    $(document).on("keyup", ".search_product", function (e) {
        let query = $(this).val();

        $.ajax({
            url: "search_product",
            type: "GET",
            data: {
                query: query,
            },
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
                id: ".regularPriceInput",
                condition: (val) => val === "",
                message: "Regular Price is required",
            },
            {
                id: ".purchasePriceInput",
                condition: (val) => val === "",
                message: "Purchase Price is required",
            },
            {
                id: ".stock",
                condition: (val) => val === "",
                message: "Stock is required",
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
        let product = {
            id: $(this).data("id"),
            name: $(this).data("name"),
            category: $(this).data("category"),
            description: $(this).data("description"),
            benefits: $(this).data("benefits"),
            sale_price: $(this).data("sale_price"),
            regular_price: $(this).data("regular_price"),
            purchase_price: $(this).data("purchase_price"),
            weight: $(this).data("weight"),
            weight_unit: $(this).data("weight_unit"),
            stock: $(this).data("stock"),
            tax_type: $(this).data("tax_type"),
            tax_percentage: $(this).data("tax_percentage"),
            is_featured: $(this).data("is_featured"),
            image: $(this).data("image"),
        };
        // Show modal
        $("#productCreateModal").css("display", "flex");
        $("#product_label").text("Edit Product");
        $("#save_product").text("Update");
        let modal = document.getElementById("productCreateModal");
        let alpine = modal.__x.$data;
        alpine.open = true;
        alpine.form.product_id = product.id || "";
        alpine.form.name = product.name || "";
        alpine.form.category_id = product.category || "";
        alpine.description = product.description || "";
        alpine.form.benefits = product.benefits || "";
        alpine.form.sale_price = product.sale_price || "";
        alpine.form.regular_price = product.regular_price || "";
        alpine.form.purchase_price = product.purchase_price || "";
        alpine.form.weight = product.weight || "";
        alpine.form.weight_unit = product.weight_unit || "";
        alpine.form.stock = product.stock || "";
        alpine.form.tax_type = product.tax_type || "";
        alpine.form.tax_percentage = product.tax_percentage || "";
        alpine.form.is_featured_product = product.is_featured || "";
        alpine.existing_image = product.image || "";
        alpine.previewUrl =  product.image || "";
        $("#variantContainer").empty();
        $.ajax({
            url: "edit_product",
            type: "GET",
            data: { product_id: product.id, edit_product: true },
            success: function (response) {
                if (response.success && response.product_details.length > 0) {
                    response.product_details.forEach((variant, index) => {
                        if (index === 0) return;
                        let variantRow = `
                    <div class="variantRow border rounded-xl p-4 mb-4 bg-gray-50 shadow-sm" data-index="${index}">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Sale Price</label>
                                <input type="number" step="0.01" class="salePrice salePriceInput border border-gray-300 rounded-lg w-full p-2" name="variants[${index}][sale_price]" value="${
                            variant.sale_price ?? ""
                        }">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Regular Price</label>
                                <input type="number" step="0.01" name="variants[${index}][regular_price]" class="regularPrice regularPriceInput border border-gray-300 rounded-lg w-full p-2" value="${
                            variant.regular_price ?? ""
                        }" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Purchase Price</label>
                                <input type="number" step="0.01" name="variants[${index}][purchase_price]" class="purchasePrice purchasePriceInput border border-gray-300 rounded-lg w-full p-2" value="${
                            variant.purchase_price ?? ""
                        }" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Weight</label>
                                <input type="number" class="weight weightInput border border-gray-300 rounded-lg w-full p-2" name="variants[${index}][weight]" value="${
                            variant.weight ?? ""
                        }">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Weight Unit</label>
                                <select name="variants[${index}][weight_unit]" class="weightUnit weightUnitSelect border border-gray-300 rounded-lg w-full p-2">
                                    <option value="">Select Unit</option>
                                    <option value="kg" ${
                                        variant.weight_unit === "kg"
                                            ? "selected"
                                            : ""
                                    }>kg</option>
                                     <option value="g" ${
                                         variant.weight_unit === "g"
                                             ? "selected"
                                             : ""
                                     }>g</option>
                                     <option value="ml" ${
                                         variant.weight_unit === "ml"
                                             ? "selected"
                                             : ""
                                     }>ml</option>
                                    <option value="l" ${
                                        variant.weight_unit === "l"
                                            ? "selected"
                                            : ""
                                    }>l</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tax Type</label>
                                <select name="variants[${index}][tax_type]" class="taxType taxTypeSelect border border-gray-300 rounded-lg w-full p-2">
                                    <option value="">Select Type</option>
                                    <option value="0" ${
                                        variant.tax_type == "0"
                                            ? "selected"
                                            : ""
                                    }>Zero</option>
                                    <option value="1" ${
                                        variant.tax_type == "1"
                                            ? "selected"
                                            : ""
                                    }>Include Tax</option>
                                    <option value="2" ${
                                        variant.tax_type == "2"
                                            ? "selected"
                                            : ""
                                    }>Exclude Tax</option>
                                </select>
                            </div>
                            <div class="taxPercentageDiv" style="${
                                variant.tax_type == "0" || !variant.tax_type
                                    ? "display:none;"
                                    : ""
                            }">
                                <label class="block text-sm font-medium text-gray-700">Tax Percentage</label>
                                <input name="variants[${index}][tax_percentage]" type="number" step="0.01" class="taxPercentage taxPercentageInput border border-gray-300 rounded-lg w-full p-2" value="${
                            variant.tax_percentage ?? ""
                        }">
                            </div>
                            <div>
                             <label class="block text-sm font-medium text-gray-700">Stock</label>
                             <input name="variants[${index}][stock]" type="number" step="0.01" class="stock border border-gray-300 rounded-lg w-full p-2" value="${
                            variant.stock ?? ""
                        }">
                            </div>
                            </div>
                            <div class="flex justify-end mt-3">
                                    <button type="button" class="removeVariantBtn text-red-600 hover:text-red-800">Remove Variant</button>
                            </div>
                    </div>`;
                        $("#variantContainer").append(variantRow);
                    });
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            },
        });
    });

    // ===== Coupon DELETE =====
    $(document).on("click", ".deleteProduct", function () {
        let id = $(this).data("id");
        let modalScope = document.querySelector("#deleteProductModal").__x
            .$data;
        modalScope.deleteId = id;
        modalScope.open = true;
    });

    window.deleteProduct = function (id) {
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

    let variantIndex = 1;

    // Add Variant
    $(document).on("click", "#addVariantBtn", function () {
        let variantRow = `
    <div class="variantRow border rounded-xl p-4 mb-4 bg-gray-50 shadow-sm" data-index="${variantIndex}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700">Sale Price</label>
                <input type="number" step="0.01" name="variants[${variantIndex}][sale_price]" class="salePrice mt-1 block w-full border rounded-md p-2"/>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Regular Price</label>
                <input type="number" step="0.01" name="variants[${variantIndex}][regular_price]" class="regularPrice regularPriceInput mt-1 block w-full border rounded-md p-2" required/>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Purchase Price</label>
                <input type="number" step="0.01" name="variants[${variantIndex}][purchase_price]" class="purchasePrice purchasePriceInput mt-1 block w-full border rounded-md p-2" required/>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Weight</label>
                <input type="number" step="0.01" name="variants[${variantIndex}][weight]" class="weight mt-1 block w-full border rounded-md p-2"/>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Weight Unit</label>
                <select name="variants[${variantIndex}][weight_unit]" class="weightUnit mt-1 block w-full border rounded-md p-2">
                    <option value="kg">kg</option>
                    <option value="g">g</option>
                    <option value="ml">ml</option>
                    <option value="l">l</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tax Type</label>
                <select name="variants[${variantIndex}][tax_type]" class="taxType taxTypeSelect mt-1 block w-full border rounded-md p-2">
                    <option value="">Select</option>
                    <option value="0">Zero</option>
                    <option value="1">Inclusive</option>
                    <option value="2">Exclusive</option>
                </select>
            </div>

            <div class="taxPercentageDiv" style="display:none;">
                <label class="block text-sm font-medium text-gray-700">Tax Percentage (%)</label>
                <input type="number" step="0.01" name="variants[${variantIndex}][tax_percentage]" class="taxPercentage taxPercentageInput mt-1 block w-full border rounded-md p-2"/>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Stock</label>
                <input type="number" step="1" name="variants[${variantIndex}][stock]" class="stock mt-1 block w-full border rounded-md p-2"/>
            </div>

        </div>

        <div class="flex justify-end mt-3">
            <button type="button" class="removeVariantBtn text-red-600 hover:text-red-800">Remove Variant</button>
        </div>
    </div>
    `;
        $("#variantContainer").append(variantRow);
        variantIndex++;
    });

    $(document).on("click", ".removeVariantBtn", function () {
        $(this).closest(".variantRow").remove();

        if ($(".variantRow").length === 0) {
            $("#noVariantMsg").show();
        }
    });

    $(document).on("change", ".taxTypeSelect", function () {
        let val = $(this).val();
        let taxDiv = $(this).closest(".grid").find(".taxPercentageDiv");
        if (val === "0" || val === "") {
            taxDiv.hide();
            taxDiv.find(".taxPercentageInput").val("");
        } else {
            taxDiv.show();
        }
    });

   $(document).on("click", "#productCreateModal [x-show='stepNumber < steps.length - 1']", function () {
    setTimeout(() => {
        if ($("[x-show='stepNumber === 2']").is(":visible")) {
            variantList = [];
               $(".variantRow").each(function (index) {
                variantList.push({
                    sale_price: $(this).find(".salePrice").val(),
                    regular_price: $(this).find(".regularPrice").val(),
                    purchase_price: $(this).find(".purchasePrice").val(),
                    weight: $(this).find(".weight").val(),
                    weight_unit: $(this).find(".weightUnit").val(),
                    tax_type: $(this).find(".taxType").val(),
                    tax_percentage: $(this).find(".taxPercentage").val(),
                    stock: $(this).find(".stock").val(),
                });
            });

            renderVariantReview();
        }
    }, 200);
    });

function renderVariantReview() {
    if (variantList.length === 0) {
        $("#viewVariantProducts").html(
            `<p class="text-gray-500 italic">No variant details available.</p>`
        );
        return;
    }

    let html = `
        <h4 class="text-md font-semibold mb-3">Variant Details</h4>
        <div>
    `;

    variantList.forEach((v, i) => {
        html += `
        <div class="bg-gray-50 rounded-lg p-4 shadow-sm mt-2">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="font-semibold">Sale Price:</span>
                    <p class="text-gray-700">${v.sale_price ? '$' + v.sale_price : '-'}</p>
                </div>
                <div>
                    <span class="font-semibold">Regular Price:</span>
                    <p class="text-gray-700">${v.regular_price ? '$' + v.regular_price : '-'}</p>
                </div>
                <div>
                    <span class="font-semibold">Purchase Price:</span>
                    <p class="text-gray-700">${v.purchase_price ? '$' + v.purchase_price : '-'}</p>
                </div>
                <div>
                    <span class="font-semibold">Weight:</span>
                    <p class="text-gray-700">${v.weight ? v.weight + ' ' + v.weight_unit : '-'}</p>
                </div>
                <div>
                    <span class="font-semibold">Tax Type:</span>
                    <p class="text-gray-700">${v.tax_type || '-'}</p>
                </div>
                <div>
                    <span class="font-semibold">Tax Percentage:</span>
                    <p class="text-gray-700">${v.tax_percentage ? v.tax_percentage + '%' : '-'}</p>
                </div>
                <div>
                    <span class="font-semibold">Stock:</span>
                    <p class="text-gray-700">${v.stock || '-'}</p>
                </div>
            </div>
        </div>`;
    });

    html += `</div>`;
    $("#viewVariantProducts").html(html);
}

});
