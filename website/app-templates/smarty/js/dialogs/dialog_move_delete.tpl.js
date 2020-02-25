$('#modal').on('show.bs.modal', function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');
    let id = button.data('id');

    if (typeName === 'move-delete') {
        // $(this).find('.modal-content').load("{'move_delete'|alias:'moveid=%ID%'}".replace('%ID%', id));
        modalLoad("{'move_delete'|alias:'moveid=%ID%'}".replace('%ID%', id));
    }
});
