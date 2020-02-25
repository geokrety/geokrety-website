$('#modal').on('show.bs.modal', function (event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');
    let id = button.data('id');

    if (typeName == 'geokret-avatar-delete') {
        modalLoad("{'geokret_avatar_delete'|alias:'key=%KEY%'}".replace('%KEY%', id));
    } else if (typeName == 'geokret-avatar-edit') {
        modalLoad("{'geokret_avatar_edit'|alias:'key=%KEY%'}".replace('%KEY%', id));
    } else if (typeName == 'geokret-avatar-define') {
        modalLoad("{'geokret_avatar_define'|alias:'key=%KEY%'}".replace('%KEY%', id));
    }
});
