$('#modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');
    var id = button.data('id');

    if (typeName == 'terms-of-use') {
        $(this).find('.modal-content').load("{'terms_of_use'|alias}");
    }
});

// change header checkbox
$("body").on('click', "#termsOfUseAcceptButton", function() {
    $('#termsOfUseInput').prop("checked", true);
    $('#modal').modal('hide')
});
