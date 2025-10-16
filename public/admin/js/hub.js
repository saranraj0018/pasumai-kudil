$(function () {
    $(document).on("click", "#create_hub", function () {
        openModal()
        // document.getElementById("categoryForm").reset();
        // let modal  = document.getElementById("categoryModal");
        // let alpine = modal.__x.$data;
        // alpine.previewUrl = '';
        // alpine.exiting_image = '';
        // alpine.form.name = '';
        // alpine.form.status = 1;
        // alpine.form.cat_id = 0;
        // $("#category_label").text("Add Category");
        // $("#save_cat").text("Save");
    });

    function openModal() {
        $("#hub_model").show();
    }
});


