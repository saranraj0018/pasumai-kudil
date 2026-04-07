$(function () {
    $(document).on("click", "#createFaqBtn", function () {
        document.getElementById("faqForm").reset();
        let modal = document.getElementById("faqModal");
        let alpine = modal.__x.$data;
        alpine.form.question = "";
        alpine.form.answer = "";
        alpine.form.faq_id = 0;
        $("#faqModal").css("display", "flex");
        $("#faq_label").text("Add FAQ");
        $("#save_faq").text("Save");
    });

    $(document).on("click", ".editFaqBtn", function () {
        let id = $(this).data("id");
        let question = $(this).data("question");
        let answer = $(this).data("answer");
        let sort_order = $(this).data("sort_order");
        let faq_status = $(this).data("faq_status");
        $("#faqModal").css("display", "flex");
        $("#faq_label").text("Edit FAQ");
        $("#save_faq").text("Update");
        let modal = document.getElementById("faqModal");
        let alpine = modal.__x.$data;
        alpine.form.faq_id = id;
        alpine.form.question = question;
        alpine.form.answer = answer;
        alpine.form.sort_order = sort_order;
        alpine.form.faq_status = faq_status;
    });

    // Use event delegation because #faqForm   may not exist initially
    $(document).on("submit", "#faqForm", function (e) {
        e.preventDefault();

        let saveBtn = $("#save_faq");
        // Fields to validate
        let fields = [
            {
                id: "#faq_question",
                condition: (val) => val === "",
                message: "Question is required",
            },
            {
                id: "#faq_answer",
                condition: (val) => val === "",
                message: "Answer is required",
            },
            {
                id: "#sort_order",
                condition: (val) => val === "" || parseInt(val) < 1,
                message: "Sort Order must be a positive number and is required",
            },
            {
                id: "#faq_status",
                condition: (val) => val === "",
                message: "Please select a status",
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
            "/admin/faq/save",
            formData,
            "POST",
            function (res) {
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        $("#faqModal").hide();
                        let modal = document.querySelector("#faqModal");
                        let alpine = modal.__x.$data;
                        alpine.form = { question: "", answer: "", faq_id: 0 };
                        alpine.previewUrl = null;
                        document.getElementById("faqForm").reset();

                        $.get("/admin/faq/list", function (html) {
                            let $tbody = $(html).find("#faqTableBody").html();
                            $("#faqTableBody").html($tbody);
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

    $(document).on("click", ".btnDeleteFaq", function () {
          let id = $(this).data("id");
          let modalScope = document.querySelector("#deleteFaqModal").__x.$data;
          modalScope.deleteId = id;
          modalScope.open = true;
    });

    window.deleteFaq = function (id) {
        sendRequest(
            "/admin/faq/delete",
            { id: id },
            "POST",
            function (res) {
                if (res.success) {
                    showToast("FAQ deleted successfully!", "success", 2000);
                    reloadFaqList();
                } else {
                    showToast(res.message, "error", 2000);
                }
                document.querySelector("#deleteFaqModal").__x.$data.open =
                    false;
            },
            function (err) {
                showToast(err.message || "Delete failed", "error", 2000);
                document.querySelector("#deleteFaqModal").__x.$data.open =
                    false;
            },
        );
    };

    // ===== Helpers =====
    function reloadFaqList() {
        $.get("/admin/faq/list", function (html) {
            let $tbody = $(html).find("#faqTableBody").html();
            $("#faqTableBody").html($tbody);
        });
    }
});
