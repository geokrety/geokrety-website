$('#modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');

    if (typeName == 'news-subscription') {
        var id = button.data('id');
        $(this).find('.modal-content').load("{'news_subscription'|alias:'newsid=%ID%'}".replace('%ID%', id));
    }
})
