$('#modal').on('show.bs.modal', function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');
    let id = button.data('id');

    if (typeName === 'move-comment') {
        var moveType = button.data('move-comment-type');
        if (moveType === 'missing') {
            // $(this).find('.modal-content').load("{'move_comment_create_missing'|alias:'moveid=%ID%'}".replace('%ID%', id));
            modalLoad("{'move_comment_create_missing'|alias:'moveid=%ID%'}".replace('%ID%', id));
        } else {
            // $(this).find('.modal-content').load("{'move_comment_create'|alias:'moveid=%ID%'}".replace('%ID%', id));
            modalLoad("{'move_comment_create'|alias:'moveid=%ID%'}".replace('%ID%', id));
        }
    } else if (typeName === 'move-comment-delete') {
        // $(this).find('.modal-content').load("{'move_comment_delete'|alias:'movecommentid=%ID%'}".replace('%ID%', id));
        modalLoad("{'move_comment_delete'|alias:'movecommentid=%ID%'}".replace('%ID%', id));
    }
});
