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
  {include 'js/_jsValidationFixup.tpl.js'}
});

// ------------------------------- JQUERY - USER UPDATE PASSWORD - END
