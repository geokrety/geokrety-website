// ------------------------------- JQUERY - USER UPDATE PASSWORD - BEGIN

$('#inputPasswordNew').strengthify({
  zxcvbn: '{$strengthify}'
});
$("#passwordChangeForm").validate({

  rules: {
    inputPasswordOld: {
      required: true,
      minlength: 5
    },
    inputPasswordNew: {
      required: true,
      minlength: 5
    },
    inputPasswordConfirm: {
      minlength: 5,
      equalTo: "#inputPasswordNew"
    }
  },
  messages: {
    inputPasswordOld: {
      required: "{t}Please enter your old password{/t}",
    },
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
