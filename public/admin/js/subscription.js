$(function () {

    const minValues = { days: 1, months: 1 };
    const maxValues = { days: 365, months: 12 };

    // ===== INLINE VALIDATION HELPERS =====
    function showFieldError(fieldId, message) {
        $(fieldId).addClass("border-red-500");
        $(fieldId + "-error").remove();
        $(fieldId).after('<p id="' + fieldId.substring(1) + '-error" class="text-red-500 text-sm mt-1">' + message + '</p>');
    }

    function clearFieldError(fieldId) {
        $(fieldId).removeClass("border-red-500");
        $(fieldId + "-error").remove();
    }

    function clearAllFieldErrors() {
        $("#subscriptionForm input, #subscriptionForm select").each(function () {
            clearFieldError("#" + $(this).attr("id"));
        });
    }

    function validateField(field){
        let val = $(field.id).val();
        if(field.condition(val)){
            showFieldError(field.id, field.message);
            return false;
        } else {
            clearFieldError(field.id);
            return true;
        }
    }

    // ===== DURATION VALIDATION =====
    function validateDuration() {
        const min = parseInt($("#min_duration").val());
        const max = parseInt($("#max_duration").val());
        const unit = $("#plan_duration_unit").val();

        let valid = true;

        if(isNaN(min)){
            showFieldError("#min_duration", "Minimum duration is required");
            valid = false;
        } else if(unit && min < minValues[unit]){
            showFieldError("#min_duration", `Min ${unit} must be at least ${minValues[unit]}`);
            valid = false;
        } else {
            clearFieldError("#min_duration");
        }

        if(isNaN(max)){
            showFieldError("#max_duration", "Maximum duration is required");
            valid = false;
        } else if(unit && max > maxValues[unit]){
            showFieldError("#max_duration", `Max ${unit} cannot exceed ${maxValues[unit]}`);
            valid = false;
        } else {
            clearFieldError("#max_duration");
        }

        if(valid && min > max){
            showFieldError("#min_duration", "Min cannot be greater than Max");
            showFieldError("#max_duration", "Max cannot be less than Min");
            valid = false;
        }

        if(unit === ""){
            showFieldError("#plan_duration_unit", "Please select a unit");
            valid = false;
        } else {
            clearFieldError("#plan_duration_unit");
        }

        return valid;
    }

    // ===== OPEN CREATE SUBSCRIPTION MODAL =====
    $(document).on("click", "#createSubscriptionBtn", function () {
        document.getElementById("subscriptionForm").reset();
        $("#subscription_id").val("");
        $("#subscription_label").text("Add Subscription");
        $("#save_subscription").text("Save");
        clearAllFieldErrors();

        let modal = document.getElementById("subscriptionModal");
        modal.__x.$data.open = true;
    });

    // ===== OPEN EDIT SUBSCRIPTION MODAL =====
    $(document).on("click", ".editSubscriptionBtn", function () {
        $("#subscription_id").val($(this).data("id"));
        $("#plan_pack").val($(this).data("pack"));
        $("#plan_type").val($(this).data("type"));
        $("#plan_amount").val($(this).data("amount"));
        $("#min_duration").val($(this).data("min_duration"));
        $("#max_duration").val($(this).data("max_duration"));
        $("#plan_duration_unit").val($(this).data("duration_unit") || "");
        $("#plan_details").val($(this).data("details"));
        $("#status").val($(this).data("status"));

        $("#subscription_label").text("Edit Subscription");
        $("#save_subscription").text("Update");
        clearAllFieldErrors();

        let modal = document.getElementById("subscriptionModal");
        modal.__x.$data.open = true;
    });

    // ===== SUBSCRIPTION FORM SUBMIT =====
    $(document).on("submit", "#subscriptionForm", function (e) {
        e.preventDefault();
        clearAllFieldErrors();

        let fields = [
            { id: "#plan_pack", condition: (val) => val === "", message: "Plan is required" },
            { id: "#plan_type", condition: (val) => val === "", message: "Plan type is required" },
            { id: "#plan_amount", condition: (val) => val === "" || val <= 0, message: "Amount is required" },
        ];

        let isValid = true;
        for(const field of fields){
            if(!validateField(field)) isValid = false;
        }

        // ✅ Validate min/max duration
        if(!validateDuration()) isValid = false;

        if(!isValid) return;

        let formData = new FormData(this);
        sendRequest(
            "/admin/milk/save",
            formData,
            "POST",
            function(res){
                if(res.success){
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        let modalScope = document.querySelector('#subscriptionModal').__x.$data;
                        modalScope.open = false;
                        document.getElementById("subscriptionForm").reset();
                        reloadSubscriptionList();
                    }, 500);
                } else if(res.errors){
                    $.each(res.errors, function(k,v){
                        showFieldError("#" + k, v[0]);
                    });
                } else {
                    showToast("Something went wrong!", "error", 2000);
                }
            },
            function(err){
                if(err.errors){
                    $.each(err.errors, function(k,v){
                        showFieldError("#" + k, v[0]);
                    });
                } else {
                    showToast(err.message || "Unexpected error", "error", 2000);
                }
            }
        );
    });

    // ===== DELETE LOGIC =====
    $(document).on("click", ".btnDeleteSubscription", function () {
        let id = $(this).data("id");
        let modalScope = document.querySelector('#deleteSubscriptionModal').__x.$data;
        modalScope.deleteId = id;
        modalScope.open = true;
    });

    window.deleteSubscription = function(id){
        sendRequest(
            "/admin/milk/delete",
            { id: id },
            "POST",
            function(res){
                if(res.success){
                    showToast("Subscription deleted successfully!", "success", 2000);
                    reloadSubscriptionList();
                } else {
                    showToast(res.message, "error", 2000);
                }
                document.querySelector('#deleteSubscriptionModal').__x.$data.open = false;
            },
            function(err){
                showToast(err.message || "Delete failed", "error", 2000);
                document.querySelector('#deleteSubscriptionModal').__x.$data.open = false;
            }
        );
    };

    // ===== HELPERS =====
    function reloadSubscriptionList(){
        $.get("/admin/milk/subscription", function(html){
            let $tbody = $(html).find("#subscriptionTableBody").html();
            $("#subscriptionTableBody").html($tbody);
        });
    }

});
