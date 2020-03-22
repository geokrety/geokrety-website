$('#modal').on('show.bs.modal', function (event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');
    let id = button.data('id');

    if (typeName == 'picture-delete') {
        modalLoad("{'picture_delete'|alias:'key=%KEY%'}".replace('%KEY%', id));
    } else if (typeName == 'picture-edit') {
        modalLoad("{'picture_edit'|alias:'key=%KEY%'}".replace('%KEY%', id));
    } else if (typeName == 'define-as-main-avatar') {
        modalLoad("{'picture_define_as_main_avatar'|alias:'key=%KEY%'}".replace('%KEY%', id));
    }
});
