$('#modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');

    if (typeName == 'move-comment-delete') {
        var id = button.data('id');
        $(this).find('.modal-content').load("{'move_comment_delete'|alias:'movecommentid=%ID%'}".replace('%ID%', id));
    }
})
