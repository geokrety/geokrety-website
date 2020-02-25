$('#modal').on('show.bs.modal', function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');
    let id = button.data('id');

    if (typeName === 'news-subscription') {
        // $(this).find('.modal-content').load("{'news_subscription'|alias:'newsid=%ID%'}".replace('%ID%', id));
        modalLoad("{'news_subscription'|alias:'newsid=%ID%'}".replace('%ID%', id));
    }
});
