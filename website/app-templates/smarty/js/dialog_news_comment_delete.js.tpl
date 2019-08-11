$('#modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');

    if (typeName == 'news-comment-delete') {
        var id = button.data('id');
        $(this).find('.modal-content').load("{'news_comment_delete'|alias:'newscommentid=%ID%'}".replace('%ID%', id));
    }
})
