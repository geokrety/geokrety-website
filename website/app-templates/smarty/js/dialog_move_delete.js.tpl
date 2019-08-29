$('#modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');
    var id = button.data('id');

    if (typeName == 'move-delete') {
        $(this).find('.modal-content').load("{'move_delete'|alias:'moveid=%ID%'}".replace('%ID%', id));
    }
})
