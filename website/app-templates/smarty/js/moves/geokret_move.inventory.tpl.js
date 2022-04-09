// ----------------------------------- JQUERY - RUCHY INVENTORY - BEGIN

// bind nrSearchButton
$("#nrSearchButton").bind("click", function() {
    $("#nr").parsley().validate();
});

// change header checkbox
$("body").on('change', "#geokretySelectAll", function() {
    var checked = $(this).is(":checked");
    if (checked) {
        var inventory = $("#geokretyListTable tr:not(.hidden) [name*='geokretySelected']").slice(0, {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS});
        inventory.each(function() {
            if ($("#geokretyListTable [name*='geokretySelected']:checkbox:checked").length >= {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}) {
                $(this).prop("#geokretyListTable", false);
                return false;
            }
            this.checked = checked;
        })
    } else {
        $("#geokretyListTable tr [name*='geokretySelected']").each(function() {
            $(this).prop("checked", false);
        })
    }
    toggleAlertMaxGKReached();

// change one GeoKret checkbox
}).on('change', "#geokretyListTable [name*='geokretySelected']", function() {
    var inventory = $("#geokretyListTable [name*='geokretySelected']:checkbox:checked");
    if (inventory.length > {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}) {
        $("#geokretySelectAll").prop("checked", false);
        $(this).prop("checked", false);
    }
    toggleAlertMaxGKReached();

// GK specific choose button
}).on('click', "#geokretyListTable [name*='btnChooseGK']", function(event) {
    event.preventDefault();
    var inventory = $("#geokretyListTable [name*='geokretySelected']:checkbox:checked");
    if (inventory.length <= {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}) {
        var trackingCode = $(this).data('trackingcode');
        fillTrackingCode(trackingCode);
    }

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

// Filter the inventory
}).on('keyup', "#gk-filter", function(event) {
    var filter = $("#gk-filter").val().toLowerCase();
    $("#geokretyListTable .gk-name").each(function() {
        var title = $(this).attr('title');
        var gkid = $(this).text();
        var tr = $(this).closest('tr');
        if (~latinize(title.toLowerCase()).indexOf(latinize(filter)) || ~latinize(gkid.toLowerCase()).indexOf(latinize(filter))) {
            tr.removeClass('hidden');
        } else {
            tr.addClass('hidden');
        }
    });
    // var inventory = $("#geokretyListTable [name*='geokretySelected']");
    if ($("#geokretyListTable tr").length > {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}) {
        $("#geokretySelectAll").prop("checked", false);
    }
})

// Check by already added GK
function checkAlreadyAddedTrackingCode() {
    var codes = $("#nr").val().split(',');
    codes.forEach(function(item) {
        $("#geokretyListTable input[data-trackingcode='"+item+"']").each(function() {
            $(this).prop("checked", true);
        })
    });
    toggleAlertMaxGKReached();
}

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
function toggleAlertMaxGKReached(el) {
    var count = $("#geokretyListTable [name*='geokretySelected']:checkbox:checked").length;
    if (count >= {GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS}) {
        $("#geokretySelectAll").prop("checked", false);
        showAlertMaxGKReached(true);
    } else {
        showAlertMaxGKReached(false);
    }
    $("#modalInventorySelectButton span.badge").text(count);
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
