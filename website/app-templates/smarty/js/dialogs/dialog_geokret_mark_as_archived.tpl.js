$('#modal').on('show.bs.modal', function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');
    let id = button.data('id');

    if (typeName === 'geokret-mark-archived') {
        modalLoad("{'geokret_mark_as_archived'|alias:'gkid=%ID%'}".replace('%ID%', id));
    }
});
