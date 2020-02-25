// ----------------------------------- JQUERY - DIALOG_LOGIN - BEGIN

let loginParsleyForm;

$('#modal').on('show.bs.modal', function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');

    if (typeName === 'form-login') {
        showLoginForm();
    }
});

$('#modal').on('hide.bs.modal', function(event) {
    if (loginParsleyForm !== undefined) {
        loginParsleyForm.destroy();
    }
});

function showLoginForm() {
    $('#modal').find('.modal-content').load("{login_link}", function() {
        loginParsleyForm = $('#modal form').parsley();
    });
}

// ----------------------------------- JQUERY - DIALOG_LOGIN - END
