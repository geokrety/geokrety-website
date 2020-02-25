$('#modal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    var typeName = button.data('type');

    if (typeName === 'select-from-inventory') {
        modalLoad("{'geokrety_move_select_from_inventory'|alias}", checkAlreadyAddedTrackingCode);
    }
});
