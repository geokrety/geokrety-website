$('#modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');

    if (typeName == 'login') {
        var id = button.data('id');
        $(this).find('.modal-content').load("{login_link}");
    } else if (typeName == 'logout') {
        var id = button.data('id');
        $(this).find('.modal-content').load("{'logout'|alias}");
    }
})
