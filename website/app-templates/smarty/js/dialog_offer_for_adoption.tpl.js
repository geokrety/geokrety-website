$('#modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');
    var id = button.data('id');

    if (typeName == 'geokret-offer-for-adoption') {
        $(this).find('.modal-content').load("{'geokret_offer_for_adoption'|alias:'gkid=%ID%'}".replace('%ID%', id));
    }
});
