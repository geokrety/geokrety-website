$('#modal').on('show.bs.modal', function(event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');
    let id = button.data('id');

    if (typeName === 'geokret-offer-for-adoption') {
        // $(this).find('.modal-content').load("{'geokret_offer_for_adoption'|alias:'gkid=%ID%'}".replace('%ID%', id));
        modalLoad("{'geokret_offer_for_adoption'|alias:'gkid=%ID%'}".replace('%ID%', id));
    }
});
