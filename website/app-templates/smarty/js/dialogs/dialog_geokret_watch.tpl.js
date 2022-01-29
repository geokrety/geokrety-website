$('#modal').on('show.bs.modal', function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');
    let id = button.data('id');

    if (typeName === 'geokret-watch') {
        modalLoad("{'geokret_watch'|alias:'gkid=%ID%'}".replace('%ID%', id));
    } else if (typeName === 'geokret-unwatch') {
        modalLoad("{'geokret_unwatch'|alias:'gkid=%ID%'}".replace('%ID%', id));
    }
});
