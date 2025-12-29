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

$(document).on("submit", "#deliverystatusChangeForm", function (e) {
     e.preventDefault();
     let isValid = true;

     const fields = [
        //  {
        //      id: "#status",
        //      condition: (val) => val === "",
        //      message: "Status is required",
        //  },
         {
             id: "#image",
             condition: (val) => val === "",
             message: "Image is required",
         },
     ];

     fields.forEach((field) => {
         if (!validateField(field)) isValid = false;
     });

     if (!isValid) return;

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
                     2000
                 );
                 setTimeout(() => {
                     let modalScope = document.querySelector(
                         "#editdeliveryListModal"
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
         }
     );
});

 function reloadDeliveryList() {
     $.get("/admin/delivery_list/delivery-list", function (html) {
         let $tbody = $(html).find("#deliveryListTableBody").html();
         $("#deliveryListTableBody").html($tbody);
     });
 }

});
