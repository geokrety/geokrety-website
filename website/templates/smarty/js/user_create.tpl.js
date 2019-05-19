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
    {include 'js/_jsValidationFixup.tpl.js'}
});

// ------------------------------- JQUERY - USER UPDATE PASSWORD - END
