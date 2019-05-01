// ----------------------------------- JQUERY - VALIDATION RULES - BEGIN

var movedGeokret = null;
var isValidLatlon = false;
var isWaypointFound = false;
var birthdate = null

$('input[type=radio][name=logtype]').change(function() {
    // // scroll to top
    // $([document.documentElement, document.body]).animate({
    //     scrollTop: $("#infoLogtypeFormGroup").offset().top - 100
    // }, 250);

    // Force validate
    $("#moveForm").parsley().whenValidate({
        group: "logtype"
    });
});

// Convert geokret date to moment object
function geokretDateToMoment(data) {
    movedGeokret = data.map(function (geokret) {
        geokret.datePublished = moment.utc(geokret.datePublished);
        // Save youngest geokret
        if (birthdate < geokret.datePublished || birthdate == null) {
            birthdate = geokret.datePublished;
        }
        return geokret;
    });
}

// Force revalidate date if additional group has been validated before
function revalidateMoveDate() {
    if (!$("#additionalDataPanel.panel-default").length) {
        $('#inputDate').parsley().validate();
    }
}

// Convert parsed date to legacy format (date + hour + minute)
function dateToLegacyFormat() {
    date = $("#datetimepicker").data("DateTimePicker").date().utc();
    $("#inputHiddenDate").val(date.format('YYYY-MM-DD'));
    $("#inputHiddenHour").val(date.hour());
    $("#inputHiddenMinute").val(date.minute());
}

function clearLegacyFormat() {
    $("#inputHiddenDate").val('');
    $("#inputHiddenHour").val('');
    $("#inputHiddenMinute").val('');
}

// Validate Tracking code
window.Parsley.addAsyncValidator('checkNr', function(xhr) {
    var valid = 200 === xhr.status;
    var data = $.parseJSON(xhr.responseText);
    this.removeError('errorNr');
    if (valid) {
        var data = $.parseJSON(xhr.responseText);
        // Display fetched GK infos
        var result = '';
        var header = '';
        data.forEach(geokret => {
            result = result + '<li>'+geokret.html+'</li>';
            header = header + ' '+geokret.gkid;
        });
        if ($("#geokretHeader").html() != header) { // Prevent flickering
            $("#nrResult").html(result).show().removeClass("hidden");
            $("#geokretHeader").html(header);
        }

        geokretDateToMoment(data);
    } else {
        data.forEach(error => {
            this.addError('errorNr', { message: error });
        });
        $("#nrResult").hide();
        $("#geokretHeader").html('');
        movedGeokret = null;
    }
    // Force revalidate date
    revalidateMoveDate();
    return valid;
}, '/check_nr.php');

// Validate Waypoint
window.Parsley.addAsyncValidator('checkWpt', function(xhr) {
    var valid = 200 === xhr.status;
    var data = $.parseJSON(xhr.responseText);
    var coordinates;
    showMap();
    this.removeError('errorWaypoint');
    if (valid) {
        // Fill coordinates field
        positionUpdate([data.latitude, data.longitude]);
        hideCoordinatesField();
        $("#cacheName").html('<a href="' + data.link + '" target="_blank">' + (data.name ? data.name : data.waypoint) + ' <i class="fa fa-external-link" aria-hidden="true"></i></a>');
    } else if (isWaypointFound) {
        data.forEach(error => {
            this.addError('errorWaypoint', { message: error });
        });
        toggleCoordinatesField();
        positionClear();
        dropMarker();
        $("#cacheName").text('');
    } else if (isValidLatlon) {
        isWaypointFound = valid;
        return true;
    } else {
        data.forEach(error => {
            this.addError('errorWaypoint', { message: error });
        });
        toggleCoordinatesField();
        $("#cacheName").text('');
    }
    isWaypointFound = valid;
    return valid;
}, '/check_wpt.php');

// Validate Coordinates
window.Parsley.addAsyncValidator('checkCoordinates', function(xhr) {
    var valid = 200 === xhr.status;
    var data = $.parseJSON(xhr.responseText);
    var latlon = $('#latlon').parsley();
    this.removeError('errorLatlon');
    if (valid) {
        positionUpdate([data.lat, data.lon]);
    } else {
        this.addError('errorLatlon', { message: data.error })
    }
    return valid;
}, '/check_coordinates.php');

window.Parsley.addValidator('datebeforenow', {
    validateString: function(value, format) {
        if (! value) {
            return true;
        }
        var date = moment(value, format, true);
        if (! date.isValid()) {
            return false;
        }
        return date.isBefore(moment());
    },
    messages: {
      en: 'The date cannot be in the future.',
      fr: 'La date ne peut pas etre dans le futur.'
    },
    priority: 256,
});

window.Parsley.addValidator('dateaftergkbirth', {
    validateString: function(value, format) {
        if (!value || movedGeokret === null) {
            return;
        }
        var date = moment(value, format, true);
        if (!date.isValid()) {
            return;
        }

        return birthdate <= date; // TODO: Born date include seconds, that may be a problem
    },
    messages: {
      en: 'The date cannot be before the GeoKret birthdate.',
      fr: 'La date ne peut pas etre anterieur à la naissance du GeoKret.'
    },
    priority: 256,
});

// Show selection in pannel header
$('#logType0').parsley().on('field:success', function() {
    var selectedLogType = $("input[type=radio][name='logtype']:checked").val();
    var selectedLogTypeText = logTypeToText(selectedLogType);
    $("#logTypeHeader").html(selectedLogTypeText);
    colorizeParentPanel($('#logType0'), true);
}).on('field:error', function() {
    colorizeParentPanel($('#logType0'), false);
});

// Events for NR
$('#nr').parsley().on('field:success', function() {
    if ($('#nr').val().length == 6) {
        $(':focus').blur();
    }
    colorizeParentPanel($('#nr'), true);
}).on('field:error', function() {
    colorizeParentPanel($('#nr'), false);
});

// Events for WPT
$('#wpt').parsley().on('field:success', function() {
    if (isWaypointFound) {
    // if (isWaypointFound && !$("#latlon").parsley().isValid()) {
        $(':focus').blur();
    }
    colorizeParentPanel($('#wpt'), true);
    $("#locationHeader").html($('#wpt').val().toUpperCase());
}).on('field:error', function() {
    colorizeParentPanel($('#wpt'), false);
    $("#locationHeader").html('');
});

// Events for LATLON
$('#latlon').parsley().on('field:success', function() {
    isValidLatlon = true;
    showMarker($("#latlon").val().split(' '));
}).on('field:validated', function() {
    if (!isWaypointFound) {
        $("#wpt").parsley().reset();
        $("#wpt").parsley().validate();
    }
}).on('field:error', function() {
    isValidLatlon = false;
    dropMarker();
});

// Events for DATE
$('#inputDate').parsley().on('field:success', function() {
    $("#additionalDataHeader").html($('#datetimepicker').data("DateTimePicker").date().fromNow());
    dateToLegacyFormat();
}).on('field:validated', function() {
    validateGroupAdditionalData();
}).on('field:error', function() {
    $("#additionalDataHeader").html('');
    clearLegacyFormat();
});

{* validate username on for anonymous *}
{if !$isLoggedIn}
// Events for DATE
$('#username').parsley().on('field:validated', function() {
    validateGroupAdditionalData();
});
{/if}

// Events for COMMENT
$('#comment').parsley().on('field:validated', function() {
    validateGroupAdditionalData();
});

function validateGroupAdditionalData() {
    $("#moveForm").parsley().whenValid({
        group: 'additionalData'
    }).done(function() {
        colorizeParentPanel($('#inputDate'), true);
    }).fail(function() {
        colorizeParentPanel($('#inputDate'), false);
    });
}

// Click on next validate group
$('#nrNextButton').on('click', function() {
    $("#moveForm").parsley().whenValidate({
        group: "trackingCode"
    });
});

// Special case when location is not necessary
// Click on next validate group
$("#logtypeNextButton").on('click', function() {
    if (isLocationNeeded()) {
        $('#collapseLocation').collapse('show');
    } else {
        $('#collapseMessage').collapse('show');
    }
    $("#moveForm").parsley().whenValidate({
        group: "logtype"
    });
});

// Special case when location is not necessary
// Click on next validate group
$('#locationNextButton').on('click', function() {
    $("#moveForm").parsley().whenValidate({
        group: "location"
    });
});

// ----------------------------------- JQUERY - VALIDATION RULES - END
