$(function () {
    $(document).on("click", ".editDeliveryList", function () {
        let delivery = {
            id: $(this).data("id"),
            status: $(this).data("status"),
            image: $(this).data("image"),
        };
        // Show modal
        $("#editdeliveryListModal").css("display", "flex");
        let modal = document.getElementById("editdeliveryListModal");
        let alpine = modal.__x.$data;
        alpine.open = true;
        alpine.form.delivery_id = delivery.id || "";
        alpine.form.status = delivery.status || "";
        alpine.existing_image = delivery.image || "";
        alpine.previewUrl = delivery.image || "";
    });


    let choicesInstance = null;

    function initUsersChoices() {
        const el = document.getElementById("users");

        if (!el) return;

        // Destroy old instance if exists
        if (choicesInstance) {
            choicesInstance.destroy();
        }

        choicesInstance = new Choices(el, {
            removeItemButton: true,
            placeholderValue: "Select users...",
            searchPlaceholderValue: "Search users...",
            shouldSort: false,
        });

        // Handle "All Users"
        el.addEventListener("change", function () {
            const selected = choicesInstance.getValue(true);

            if (selected.includes("all") && selected.length > 1) {
                const lastSelected = selected[selected.length - 1];

                if (lastSelected === "all") {
                    choicesInstance.removeActiveItems();
                    choicesInstance.setChoiceByValue("all");
                } else {
                    choicesInstance.removeActiveItemsByValue("all");
                }
            }
        });
    }

    $(document).on("click", "#changeDeliveryStatus", function () {
        const modal = document.querySelector("#changedeliveryStatusModal");
        modal.__x.$data.open = true;
        setTimeout(() => {
            initUsersChoices();
        }, 100);
    });


    function closeDeliveryModal() {
        const modal = document.querySelector("#changedeliveryStatusModal");
        modal.__x.$data.closeModal();
        $("#overallstatusChangeForm")[0].reset();
        $(".error-message").remove();
        if (choicesInstance) {
            choicesInstance.removeActiveItems();
        }
    }

   $(document).on("submit", "#overallstatusChangeForm", async function (e) {
       e.preventDefault();
       let isValid = true;
       $(".error-message").remove();
       const saveBtn = $("#save_product");
       const fromDate = $("#from_date").val();
       const toDate = $("#to_date").val();
       const status = $("#status").val();
       const selectedUsers = choicesInstance
           ? choicesInstance.getValue(true)
           : [];
       function showFieldError(selector, message) {
           $(selector).after(
               `<span class="error-message text-red-500 text-xs block mt-1">${message}</span>`,
           );
       }
       if (!fromDate) {
           showFieldError("#from_date", "From Date is required.");
           isValid = false;
       }
       if (!toDate) {
           showFieldError("#to_date", "To Date is required.");
           isValid = false;
       }
       if (fromDate && toDate && toDate < fromDate) {
           showFieldError(
               "#to_date",
               "To Date should be greater than or equal to From Date.",
           );
           isValid = false;
       }
       if (selectedUsers.length === 0) {
           showToast(
               "Please select at least one user or All Users.",
               "error",
               2000,
           );
           isValid = false;
       }
       if (!isValid) {
           return;
       }

           const result = await Swal.fire({
               title: "Confirm Delivery",
               html: `
            <div style="text-align:left">
                <p><strong>This action cannot be undone.</strong></p>
                <br>
                <p>
                    The delivery amount will be deducted from the wallet balance
                    of the selected user(s) for the selected delivery date(s).
                </p>
                <br>
                <p>Do you want to continue?</p>
            </div>
        `,
               icon: "warning",
               showCancelButton: true,
               confirmButtonText: "Yes, Continue",
               cancelButtonText: "Cancel",
               confirmButtonColor: "#ab5f00",
               cancelButtonColor: "#6b7280",
           });
           if (!result.isConfirmed) {
               return;
           }

       saveBtn
           .prop("disabled", true)
           .addClass("opacity-50 cursor-not-allowed")
           .text("Saving...");

       let formData = new FormData(this);

       formData.delete("users[]");

       if (selectedUsers.includes("all")) {
           formData.append("users", "all");
       } else {
           selectedUsers.forEach(function (id) {
               formData.append("users[]", id);
           });
       }

       showLoader();

       sendRequest(
           "/admin/delivery_list/overall-save",
           formData,
           "POST",

           function (res) {
               hideLoader();

               saveBtn
                   .prop("disabled", false)
                   .removeClass("opacity-50 cursor-not-allowed")
                   .text("Save");

               if (res.success) {
                   showToast(
                       res.message || "Delivery status updated successfully.",
                       "success",
                       2000,
                   );
                     window.location.reload();
               } else {
                   showToast(res.message, "error", 2000);
               }
           },

           function (err) {
               hideLoader();

               saveBtn
                   .prop("disabled", false)
                   .removeClass("opacity-50 cursor-not-allowed")
                   .text("Save");

               if (err.errors) {
                   $.each(err.errors, function (key, value) {
                       showToast(value[0], "error", 2000);
                   });
               } else {
                   showToast(
                       err.message || "Something went wrong.",
                       "error",
                       2000,
                   );
               }
           },
       );
   });


    $(document).on("submit", "#deliverystatusChangeForm", function (e) {
        e.preventDefault();
        let isValid = true;
        let $saveBtn = $("#save_product");
        const fields = [
            //  {
            //      id: "#status",
            //      condition: (val) => val === "",
            //      message: "Status is required",
            //  },
            //  {
            //      id: "#image",
            //      condition: (val) => val === "",
            //      message: "Image is required",
            //  },
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
            "/admin/delivery_list/status-save",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(
                        "Delivery Status saved successfully!",
                        "success",
                        2000,
                    );
                    setTimeout(() => {
                        let modalScope = document.querySelector(
                            "#editdeliveryListModal",
                        ).__x.$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false;
                        }
                        // Reset form
                        document
                            .getElementById("deliverystatusChangeForm")
                            .reset();
                        reloadDeliveryList();
                        window.location.reload();
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

    function reloadDeliveryList() {
        $.get("/admin/delivery_list/delivery-list", function (html) {
            let $tbody = $(html).find("#deliveryListTableBody").html();
            $("#deliveryListTableBody").html($tbody);
        });
    }
});
