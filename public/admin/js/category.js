$(function () {
    // Use event delegation because #categoryForm may not exist initially
    $(document).on("submit", "#categoryForm", function (e) {
        e.preventDefault();
        let isValid = true;

        // Fields to validate
        const fields = [
            { id: "#category_name", condition: (val) => val === "", message: "Category name is required" },
            { id: "#category_status", condition: (val) => val === "", message: "Please select status" },
            { id: "#category_image", condition: (val) => val === "", message: "Please Image" },
        ];

        fields.forEach((field) => {
            if (!validateField(field)) isValid = false;
        });

        if (!isValid) return;
        let formData = new FormData(this);
        sendRequest(
            "/admin/category/save",
            formData,
            "POST",
            function(res){
                if(res.success){
                    showToast("Category saved successfully!", "success", 2000);
                    setTimeout(() => {
                        let modalScope = document.querySelector('#categoryModal').__x.$data;
                        if (modalScope.hasOwnProperty('open')) {
                            modalScope.open = false; // close modal
                        }
                        // Reset the form so next time it's clean
                        document.getElementById("categoryForm").reset();
                        $.get("/admin/category/list", function (html) {
                            let $tbody = $(html).find("#categoryTableBody").html();
                            $("#categoryTableBody").html($tbody);
                        });
                    }, 500);

                } else {
                    showToast("Something went wrong!", "error", 2000);
                }
            },
            function(err){
                if(err.errors){
                    let msg = "";
                    $.each(err.errors, function(k,v){ msg+=v[0]+"<br>"; });
                    showToast(msg, "error", 2000);
                } else {
                    showToast(err.message || "Unexpected error", "error", 2000);
                }
            }
        );
    });
});
