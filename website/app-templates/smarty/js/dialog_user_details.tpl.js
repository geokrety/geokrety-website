var inscrybmde = null;

$('#modal').on('shown.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');

    if (typeName == 'user-choose-language') {
        $(this).find('.modal-content').load("{'user_language_chooser'|alias}");
    } else if (typeName == 'user-update-email') {
        $(this).find('.modal-content').load("{'user_update_email'|alias}", function() {
            $('#update-email').parsley();
        });
    } else if (typeName == 'user-update-password') {
        $(this).find('.modal-content').load("{'user_update_password'|alias}", function() {
            $('#update-password').parsley();
            $('#inputPasswordNew').strengthify({
                zxcvbn: '{GK_CDN_ZXCVBN_JS}'
            });
        });
    } else if (typeName == 'user-refresh-secid') {
        $(this).find('.modal-content').load("{'user_refresh_secid'|alias}");
    }
});
