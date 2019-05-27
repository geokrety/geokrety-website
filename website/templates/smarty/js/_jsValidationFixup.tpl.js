    errorElement: "em",
    errorPlacement: function(error, element) {
        // Add the `help-block` class to the error element
        error.addClass("help-block");

        if (element.prop("type") === "checkbox") {
            error.insertAfter(element.parent("label"));
        } else if (element.prop("type") === "radio") {
            error.insertAfter(element.closest("div").children("label").last());
        } else if (element.parents("div").hasClass("input-group")) {
            error.insertAfter(element.parent("div.input-group"));
        } else {
            error.insertAfter(element);
        }
    },
    highlight: function(element, errorClass, validClass) {
        $(element).parents(".col-sm-10, .col-sm-6, .col-sm-4").addClass("has-error").removeClass("has-success");
    },
    unhighlight: function(element, errorClass, validClass) {
        $(element).parents(".col-sm-10, .col-sm-6, .col-sm-4").addClass("has-success").removeClass("has-error");
    }
