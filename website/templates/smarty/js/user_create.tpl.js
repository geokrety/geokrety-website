// ------------------------------- JQUERY - USER UPDATE PASSWORD - BEGIN

$('#inputPassword').strengthify({
    zxcvbn: '{$strengthify}'
});
$("#createUser").validate({

    rules: {
        inputUsername: {
            required: true,
            minlength: 3,
            maxlength: 30
        },
        inputPassword: {
            required: true,
            minlength: 5,
            maxlength: 80
        },
        inputPasswordConfirm: {
            equalTo: "#inputPassword",
            minlength: 5,
            maxlength: 80
        },
        inputEmail: {
            email: true,
        }
    },


    errorElement: "em",
    errorPlacement: function(error, element) {
        // Add the `help-block` class to the error element
        error.addClass("help-block");

        if (element.prop("type") === "checkbox") {
            error.insertAfter(element.parent("label"));
        } else {
            error.insertAfter(element);
        }
    },
    highlight: function(element, errorClass, validClass) {
        $(element).parents(".col-sm-10").addClass("has-error").removeClass("has-success");
    },
    unhighlight: function(element, errorClass, validClass) {
        $(element).parents(".col-sm-10").addClass("has-success").removeClass("has-error");
    }
});

// ------------------------------- JQUERY - USER UPDATE PASSWORD - END
