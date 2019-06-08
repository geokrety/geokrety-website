// ----------------------------------- JQUERY - VALIDATION RULES - BEGIN

var isValidLatlon = false;
var isWaypointFound = false;

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

// Validate Tracking code
window.Parsley.addAsyncValidator('checkNr', function(xhr) {
    var valid = 200 === xhr.status;
    var data = $.parseJSON(xhr.responseText);
    this.removeError('errorNr');
    if (valid) {
        var data = $.parseJSON(xhr.responseText);
        // Display fetched GK infos
        $("#nrResult").html(data.html).show().removeClass("hidden");
        $("#geokretHeader").html(data.gkid);
    } else {
        this.addError('errorNr', { message: data.error })
        $("#nrResult").hide();
        $("#geokretHeader").html('');
    }
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
    } else if (isWaypointFound) {
        this.addError('errorWaypoint', { message: data.error })
        toggleCoordinatesField();
        positionClear();
        dropMarker();
    } else if (isValidLatlon) {
        return true;
    } else {
        this.addError('errorWaypoint', { message: data.error })
        toggleCoordinatesField();
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

// Show selection in pannel header
$('#logType0').parsley().on('field:success', function() {
    var selectedLogType = $("input[type=radio][name='logtype']:checked").val();
    var selectedLogTypeText = logTypeToText(selectedLogType);
    $("#logTypeHeader").html(selectedLogTypeText);
    colorizeParentPanel($('#logType0'), true);
}).on('field:error', function() {
    colorizeParentPanel($('#logType0'), false);
});

$('#nr').parsley().on('field:success', function() {
    $(':focus').blur();
    colorizeParentPanel($('#nr'), true);
}).on('field:error', function() {
    colorizeParentPanel($('#nr'), false);
});

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

$('#latlon').parsley().on('field:success', function() {
    isValidLatlon = true;
    showMarker($("#latlon").val().split(' '));
}).on('field:validated', function() {
    $("#wpt").parsley().reset();
    $("#wpt").parsley().validate();
}).on('field:error', function() {
    isValidLatlon = false;
    dropMarker();
});

$('#nrNextButton').on('click', function() {
    $("#moveForm").parsley().whenValidate({
        group: "trackingCode"
    });
});

// Special case when location is not necessary
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

$('#locationNextButton').on('click', function() {
    $("#moveForm").parsley().whenValidate({
        group: "location"
    });
});

// window.Parsley
//     .addValidator('notInTheFuture', {
//             requirementType: 'integer',
//             validateNumber: function(value, requirement) {
//                 // is valid date?
//                 var timestamp = Date.parse(value),
//                     minTs = Date.parse(requirement);
//
//                 return isNaN(timestamp) ? false : timestamp > minTs);
//                 return 0 === value % requirement;
//         },
//         messages: {
//             en: 'This value should be a multiple of %s',
//             fr: 'Cette valeur doit être un multiple de %s'
//         }
//     }
// );

// ----------------------------------- JQUERY - VALIDATION RULES - END
