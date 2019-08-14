$('#modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');

    if (typeName == 'user-choose-language') {
        $(this).find('.modal-content').load("{'user_language_chooser'|alias}");
    } else if (typeName == 'user-update-email') {
        $(this).find('.modal-content').load("{'user_update_email'|alias}");
    }
})
