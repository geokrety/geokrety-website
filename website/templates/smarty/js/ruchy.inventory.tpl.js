// ----------------------------------- JQUERY - RUCHY INVENTORY - BEGIN

// change header checkbox
$("body").on('change', "#geokretySelectAll", function() {
    var checked = $(this).is(":checked");
    var inventory = $("#geokretyListTable [name*='geokretySelected']");
    inventory.slice(0, {CHECK_NR_MAX_PROCESSED_ITEMS}).each(function() {
        this.checked = checked;
    })
    toggleAlertMaxGKReached(inventory.length, this);

// change one GeoKret checkbox
}).on('change', "#geokretyListTable [name*='geokretySelected']", function() {
    var inventory = $("#geokretyListTable [name*='geokretySelected']:checkbox:checked");
    if (!$(this).is(":checked") && inventory.length < {CHECK_NR_MAX_PROCESSED_ITEMS}) {
        $("#geokretySelectAll").prop("checked", false);
    }
    var selectedCount = inventory.length;
    toggleAlertMaxGKReached(inventory.length, this);

// GK specific choose button
}).on('click', "#geokretyListTable [name*='btnChooseGK']", function(event) {
    event.preventDefault();
    var trackingCode = $(this).data('trackingcode');
    fillTrackingCode(trackingCode);

// Select button from the modal
}).on('click', "#modalInventorySelectButton", function(event) {
    var trackingCodes = Array();
    $("#geokretyListTable [name*='geokretySelected']:checkbox:checked").each(function() {
        trackingCodes.push($(this).data('trackingcode'));
    })
    fillTrackingCode(trackingCodes);

// Status icon binding on GeoKrety result list (action: remove from selection)
}).on('click', "#nrResult [name*='gkStatusIcon']", function(event) {
    event.preventDefault();
    var trackingCode = $(this).data('trackingcode');
    removeTrackingCode(trackingCode);
})

// Add a tracking code to the list
function fillTrackingCode(trackingCodes) {
    var codes = [$("#nr").val(), trackingCodes].filter(function (el) { return el }).join(',');
    $("#nr").parsley().reset();
    $("#nr").val(codes).trigger("focusout");
    $('#modal').modal('hide');
}

// Remove a tracking code from the list
function removeTrackingCode(trackingCode) {
    var foundTrackingCodes = $("#nr").val().split(',').filter(function (el) { return el.toUpperCase() != trackingCode.toUpperCase() }).join(',');
    $("#nr").parsley().reset();
    $("#nr").val(foundTrackingCodes).trigger("focusout");
}

//
function toggleAlertMaxGKReached(count, el) {
    if (count > {CHECK_NR_MAX_PROCESSED_ITEMS}) {
        $(el).prop("checked", false);
        showAlertMaxGKReached(true);
        $("#modalInventorySelectButton span.badge").text({CHECK_NR_MAX_PROCESSED_ITEMS});
    } else {
        $("#modalInventorySelectButton span.badge").text(count);
    }
}

// Show/hide warning message
function showAlertMaxGKReached(show) {
    if (show) {
        $("#maxGKSelctionReached").show().removeClass("hidden");
    } else {
        $("#maxGKSelctionReached").hide();
    }
}

// ----------------------------------- JQUERY - RUCHY INVENTORY - END
