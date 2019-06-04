// ----------------------------------- JQUERY - VALIDATION RULES - BEGIN

// var formInstance = $('#moveForm').parsley({
//     priority-enabled: false,
// });

$('input[type=radio][name=logtype]').change(function() {
    // scroll to top
    $([document.documentElement, document.body]).animate({
        scrollTop: $("#infoLogtypeFormGroup").offset().top - 100
    }, 250);

    // Force validate
    $("#moveForm").parsley().whenValidate({
        group: "logtype"
    });
});

// Validate Tracking code
window.Parsley.addAsyncValidator('checkNr', function(xhr) {
    var valid = 200 === xhr.status;
    if (valid) {
        var data = JSON.parse(xhr.responseText);
        // Display fetched GK infos
        $("#nrResult").html(data['html']).show().removeClass("hidden");
        $("#nrError").hide();
        // Show selection in pannel header
        $("#geokretHeader").html(data['gkid']);
    } else {
        $("#nrError").html(xhr.responseText).show().removeClass("hidden");
        $("#nrResult").hide();
        $("#locationHeader").html('');
    }
    return 200 === xhr.status;
}, '/check_nr.php');

// Validate Waypoint
window.Parsley.addAsyncValidator('checkWpt', function(xhr) {
    var valid = 200 === xhr.status;
    var coordinates;
    showMap();
    if (valid) {
        var data = JSON.parse(xhr.responseText);
        // Display fetched Waypoint infos
        $("#wptResult").html(data['html']).show().removeClass("hidden");
        $("#wptError").hide();
        // Show selection in pannel header
        $("#locationHeader").html(data['waypoint']);
        // Fill coordinates field
        coordinates = [data['latitude'], data['longitude']];
        positionUpdate(coordinates);
        hideCoordinatesField();
    } else {
        $("#wptError").html(xhr.responseText).show().removeClass("hidden");
        $("#wptResult").hide();
        $("#locationHeader").html('');
        toggleCoordinatesField();
    }
    return 200 === xhr.status;
}, '/check_wpt.php');

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
    $(':focus').blur();
    colorizeParentPanel($('#wpt'), true);
}).on('field:error', function() {
    colorizeParentPanel($('#wpt'), false);
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
