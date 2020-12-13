// ----------------------------------- JQUERY - DIALOG_LOGIN - BEGIN

let loginParsleyForm;

$('#modal').on('show.bs.modal', function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');

    if (typeName === 'form-login') {
        modalLoad("{'login'|login_link nofilter}", function() {
            loginParsleyForm = $('#modal form').parsley();
        });
    }
});

$('#modal').on('hide.bs.modal', function(event) {
    if (loginParsleyForm !== undefined) {
        loginParsleyForm.destroy();
    }
});

// ----------------------------------- JQUERY - DIALOG_LOGIN - END
