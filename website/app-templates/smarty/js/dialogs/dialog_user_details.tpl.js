let inscrybmde = null;

$('#modal').on('shown.bs.modal', function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');

    if (typeName === 'user-choose-language') {
        modalLoad("{'user_language_chooser'|alias}");
    } else if (typeName === 'user-update-email') {
        modalLoad("{'user_update_email'|alias}", function() {
            $('#update-email').parsley();
        });
    } else if (typeName === 'user-update-password') {
        modalLoad("{'user_update_password'|alias}", function() {
            $('#update-password').parsley();
            $('#inputPasswordNew').strengthify({
                zxcvbn: '{GK_CDN_ZXCVBN_JS}'
            });
        });
    } else if (typeName === 'user-refresh-secid') {
        modalLoad("{'user_refresh_secid'|alias}");
    }
});
