$('#modal').on('show.bs.modal', function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');

    if (typeName === 'terms-of-use') {
        // $(this).find('.modal-content').load("{'terms_of_use'|alias}");
        modalLoad("{'terms_of_use'|alias}");
    }
});

// change header checkbox
$("body").on('click', "#termsOfUseAcceptButton", function() {
    $('#termsOfUseInput').prop("checked", true);
    $('#modal').modal('hide')
});
