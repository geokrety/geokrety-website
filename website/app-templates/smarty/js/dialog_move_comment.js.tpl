$('#modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');
    var id = button.data('id');

    if (typeName == 'move-comment') {
        var moveType = button.data('move-comment-type');
        if (moveType == 'missing') {
            $(this).find('.modal-content').load("{'move_comment_create_missing'|alias:'moveid=%ID%'}".replace('%ID%', id));
        } else {
            $(this).find('.modal-content').load("{'move_comment_create'|alias:'moveid=%ID%'}".replace('%ID%', id));
        }
    } else if (typeName == 'move-comment-delete') {
        $(this).find('.modal-content').load("{'move_comment_delete'|alias:'movecommentid=%ID%'}".replace('%ID%', id));
    }
})
