// ----------------------------------- JQUERY - RUCHY - BEGIN

// Bind tooltip on NR field: Display label example
$(".tooltip_large").tooltip({
    placement : "top",
    template: '<div class="tooltip" role="tooltip" style="width:200px; height:148px;"><div class="tooltip-arrow"></div><div class="tooltip-inner large"></div></div>'
});

// Bind datepicker
$("#datetimepicker").datetimepicker({
    ignoreReadonly: true,
});
$("#datetimepicker").data("DateTimePicker").date(moment());

// Automatic scroll on panel open
$("#movePanelGroup div.panel-collapse").on("shown.bs.collapse", function(e) {
    var $panel = $(this).closest(".panel");
    $("html,body").animate({
        scrollTop: $panel.offset().top - 105
    }, 250);
});

// Force accordion collapse, usefull when one panel is dynamically removed
$('#movePanelGroup div.panel-collapse').on('show.bs.collapse', function () {
    $('div.panel-collapse').each(function() {
        $(this).collapse('hide');
    });
})

// Initialize map
{include 'js/_map_init.tpl.js'}
// The map object
var map;
// The marker on the map
var cacheMarker;

// Display the map
function showMap(coordinates) {
    $("#mapField").removeClass("map-togglable");
    if (map === undefined) {
        map = initializeMap();
    } else {
        map.invalidateSize();
    }
    var latlng = L.latLng(coordinates.split(' '));
    if (cacheMarker === undefined) {
        cacheMarker = L.marker([0, 0]).addTo(map);
    }
    cacheMarker.setLatLng(latlng);
    map.flyTo(latlng, 6);
}

// // // geolocation parameters
// // var geolocationOptions = {
// //   enableHighAccuracy: true,
// //   timeout: 5000,
// //   maximumAge: 0
// // };


// function isFieldValid(fieldElement) {
//     // if (!$(this).valid()) {
//     //     errorCount = errorCount + 1;
//     // }
//     return true;
// }
//
// // Colorize a panel group if fields have errors
// function _colorizePanelOnValidation(panelGroup = "") {
//     $(panelGroup).each(function(index) {
//         var errorCount = 0;
//         $(this).find("input[required]").each(function(index) {
//             isFieldValid(this)
//         })
//         if (errorCount) {
//             $(this).addClass("panel-danger")
//                 .removeClass("panel-standard")
//                 .removeClass("panel-success");
//         } else {
//             $(this).addClass("panel-success")
//                 .removeClass("panel-standard")
//                 .removeClass("panel-danger");
//         }
//     })
// };
// // Colorize all pannel
// function colorizePanelsOnValidation() {
//     $(".panel-group > div.panel").each(function() {
//         _colorizePanelOnValidation(this);
//     });
// };
// // Pannel colors - single
// function colorizeParentPanel(element) {
//     var panel = $(element).closest(".panel");
//     _colorizePanelOnValidation(panel);
// };

// Load GK preview
function loadGKFromNr(nr) {
    console.log("loadGKFromNr");
    if ($( "#nr" ).parsley().isValid()) {
        console.log("loadGKFromNr: Valid");
        $.post( "check_nr.php", { nr: nr })
        .done(function( data ) {
            $("#nrResult").html(data);
        });
    } else {
        console.log("loadGKFromNr: Invalid");
        $("#nrResult").html("");
    }
}

// Load coordinates from a waypoint
function loadCoordinatesFromWpt(wpt) {
    if ($( "#wpt" ).parsley().isValid()) {
        var latlon = $("#latlon");
        $.post( "check_wpt.php", { wpt: wpt, coordinates: latlon.val() })
        .done(function( data ) {
            latlon.val(data);
            // validator.check($("#latlon"));
            showMap(data);
        });
    // } else {
    //     $("#latlon").val("");
    }
    if ($("#wpt").val().substring(0, 2).toUpperCase() != 'GC') {
        $("#coordinateField").addClass("coordinates-togglable");
    } else {
        $("#coordinateField").removeClass("coordinates-togglable");
    }
}

// // Convert coordinates format
// function convertCoordinates(coordinates) {
//     if ($( "#latlon" ).valid()) {
//         var latlon = $("#latlon");
//         $.post( "check_coordinates.php", { latlon: coordinates })
//         .done(function( data ) {
//             $("#latlon").val(data);
//             showMap(data);
//             validator.resetElements($("#wpt"));
//             colorizePanelOnValidation($("#latlon").closest("div.panel"));
//         });
//     }
// }

// Check if a move type require coordinates
function isLocationNeeded() {
    var logtype = $('input[name=logtype]:checked', '#moveForm').val();
    return logtype === undefined ? true : ['0', '3', '5'].includes(logtype);
}

// Toggle location panel based on move type requirement
function toggleLocationSubfrom() {
    if (isLocationNeeded()) {
        $("#panelLocation").show();
    } else {
        $("#panelLocation").hide();
    }
}

// bind radio buttons
$("#moveForm input[type=radio]").change(function() {
    toggleLocationSubfrom();
});

// bind on submit
$("#submitButton").click(function() {
    // colorizePanelsOnValidation();
    // if ($("#moveForm").valid()) {
        $("#moveForm").submit();
    // }
});

// // bind on Next buttons
// $("button[data-toggle]").click(function() {
//     colorizeParentPanel(this);
// });
// Special case for NR one
$("#nextButtonNR").click(function() {
    if (isLocationNeeded()) {
        $('#collapseLocation').collapse('show');
    } else {
        $('#collapseMessage').collapse('show');
    }
});



// // bind radio buttons
// $("#moveForm input[type=text], #moveForm input[type=radio]").change(function() {
//     colorizeParentPanel(this);
// });
//
// // bind text buttons
// $("#moveForm input[type=text]").keyup(function() {
//     colorizeParentPanel(this);
// });

// bind nrSearchButton
$("#nrSearchButton").bind("click", function() {
    loadGKFromNr($("#nr").val());
});

// bind wptSearchButton
$("#wptSearchButton").bind("click", function() {
    loadCoordinatesFromWpt($("#wpt").val());
});

// bind coordinatesSearchButton
$("#coordinatesSearchButton").bind("click", function() {
    // convertCoordinates($("#latlon").val());
    var latlng = $("#latlon").val().split(' ');
    console.log("LatLng:"+latlng);
    map.flyTo(latlng, 17);
});

// // bind geolocationButton
// $( "#geolocationButton" ).bind("click", function() {
//     window.navigator.geolocation.getCurrentPosition(function(position) {
//         var coordinates = position.coords.latitude + " " + position.coords.longitude;
//         $("#latlon").val(coordinates);
//         convertCoordinates(coordinates);
//     }, function(error) {
//         console.log(error);
//     }, geolocationOptions);
//
// });

// bind datetimepicker
$("#inputDate").click(function() {
    $("#datetimepicker").data("DateTimePicker").show();
});

{include file="js/ruchy.validation.tpl.js"}
// ----------------------------------- JQUERY - RUCHY - END
