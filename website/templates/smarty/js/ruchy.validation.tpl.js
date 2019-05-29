// ----------------------------------- JQUERY - VALIDATION RULES - BEGIN

// var formInstance = $('#moveForm').parsley({
//     priority-enabled: false,
// });

$('input[type=radio][name=logtype]').change(function() {
  // scroll to top
  $([document.documentElement, document.body]).animate({
        scrollTop: $("#infoLogtypeFormGroup").offset().top - 100
    }, 250);
  // Show selection in pannel header
  $("#logTypeHeader").html("Grab");
});

window.Parsley.addAsyncValidator('checkNr', function (xhr) {
    var valid = 200 === xhr.status;
    if (valid) {
        $("#nrResult").html(xhr.responseText).show().removeClass("hidden");
        $("#nrError").hide();
        // Show selection in pannel header
        $("#geokretHeader").html("GKB65C");
    } else {
        $("#nrError").html(xhr.responseText).show().removeClass("hidden");
        $("#nrResult").hide();
    }
    return 200 === xhr.status;
}, '/check_nr.php');

$('#nr').parsley().on('field:ajaxoptions', function(instance, ajaxOptions) {
    ajaxOptions['type'] = 'POST';
});

$('#nr').parsley().on('field:success', function() {
  $(':focus').blur();
});

$('#wpt').parsley().on('field:success', function() {
  $(':focus').blur();
  // Show selection in pannel header
  $("#locationHeader").html($('#wpt').val());
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
