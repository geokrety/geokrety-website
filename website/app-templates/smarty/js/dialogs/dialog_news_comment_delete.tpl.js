$('#modal').on('show.bs.modal', function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');

    if (typeName === 'news-comment-delete') {
        let id = button.data('id');
        // $(this).find('.modal-content').load("{'news_comment_delete'|alias:'newscommentid=%ID%'}".replace('%ID%', id));
        modalLoad("{'news_comment_delete'|alias:'newscommentid=%ID%'}".replace('%ID%', id));
    }
});
