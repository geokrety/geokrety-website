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
$(".panel-collapse").on("shown.bs.collapse", function(e) {
    var $panel = $(this).closest(".panel");
    $("html,body").animate({
        scrollTop: $panel.offset().top - 105
    }, 250);
});

// geolocation parameters
var geolocationOptions = {
  enableHighAccuracy: true,
  timeout: 5000,
  maximumAge: 0
};

// Initialize map
{include 'js/_map_init.tpl.js'}
var map;

var cacheMarker;

// Form validation
jQuery.validator.setDefaults({
    ignore: [],
    rules: {
        logtype: {
            required: true
        },
        nr: {
            required: true,
            minlength: 6,
            maxlength: 6,
            remote: {
                url: "check_nr.php",
                type: "post",
                data: {
                    validateOnly: true
                },
                complete: function(data) {
                    loadGKFromNr($("#nr").val());
                }
            }
        },
        wpt: {
            required: true,
            minlength: 4,
            maxlength: 20,
            remote: {
                url: "check_wpt.php",
                type: "post",
                data: {
                    validateOnly: true,
                    coordinates: function() {
                        return $("#latlon").val();
                    }
                },
                complete: function(data) {
                    if (!$("#latlon").val()) {
                        loadCoordinatesFromWpt($("#wpt").val());
                    }
                }
            }

        },
        latlon: {
            required: true,
            minlength: 4,
            remote: {
                url: "check_coordinates.php",
                type: "post",
                data: {
                    validateOnly: true
                },
                complete: function(data) {
                    convertCoordinates($("#latlon").val())
                }
            }
        },
        data: {
            required: true
        },
        username: {
            required: true
        },
    },

    {include 'js/_jsValidationFixup.tpl.js'}
});
var validator = $("#moveForm").validate();

// Panel colors
function colorizePanelOnValidation(panelGroup = "") {
    // return;
    $(panelGroup).each(function(index) {
        var errorCount = 0;
        $(this).find("input[required]").each(function(index) {
            if (!$(this).valid()) {
                errorCount = errorCount + 1;
            }
        })
        if (errorCount) {
            $(this).addClass("panel-danger")
                .removeClass("panel-standard")
                .removeClass("panel-success");
        } else {
            $(this).addClass("panel-success")
                .removeClass("panel-standard")
                .removeClass("panel-danger");
        }
    })
};
// Pannel colors - all
function colorizePanelsOnValidation() {
    $(".panel-group > div.panel").each(function() {
        colorizePanelOnValidation(this);
    });
};
// Pannel colors - all
function colorizeParentPanel(element) {
    var panel = $(element).closest(".panel");
    colorizePanelOnValidation(panel);
};

function loadGKFromNr(nr) {
    if ($( "#nr" ).valid()) {
        $.post( "check_nr.php", { nr: nr })
        .done(function( data ) {
            $("#nrResult").html(data);
        });
    } else {
        $("#nrResult").html("");
    }
}

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

function loadCoordinatesFromWpt(wpt) {
    if ($( "#wpt" ).valid()) {
        var latlon = $("#latlon");
        $.post( "check_wpt.php", { wpt: wpt, coordinates: latlon.val() })
        .done(function( data ) {
            latlon.val(data);
            validator.check($("#latlon"));
        });
    } else {
        $("#latlon").val("");
    }
    if ($("#wpt").val().substring(0, 2).toUpperCase() != 'GC') {
        $("#coordinateField").addClass("coordinates-togglable");
    } else {
        $("#coordinateField").removeClass("coordinates-togglable");
    }
}

function convertCoordinates(coordinates) {
    if ($( "#latlon" ).valid()) {
        var latlon = $("#latlon");
        $.post( "check_coordinates.php", { latlon: coordinates })
        .done(function( data ) {
            $("#latlon").val(data);
            showMap(data);
            validator.resetElements($("#wpt"));
            colorizePanelOnValidation($("#latlon").closest("div.panel"));
        });
    }
}

function isLocationNeeded() {
    var logtype = $('input[name=logtype]:checked', '#moveForm').val();
    return logtype === undefined ? true : ['0', '3', '5'].includes(logtype);
}

function toggleLocationSubfrom() {
    if (isLocationNeeded()) {
        $("#panelLocation").show();
    } else {
        $("#panelLocation").hide();
    }
}

// bind on submit
$("#submitButton").click(function() {
    colorizePanelsOnValidation();
    if ($("#moveForm").valid()) {
        $("#moveForm").submit();
    }
});

// bind on Next buttons
$("button[data-toggle]").click(function() {
    colorizeParentPanel(this);
});
// Special case for NR one
$("#nextButtonNR").click(function() {
    if (isLocationNeeded()) {
        $('#collapseLocation').collapse('show');
    } else {
        $('#collapseMessage').collapse('show');
    }
    //  data-toggle="collapse" data-parent="#accordion" href="#collapseLocation" aria-expanded="true" aria-controls="collapseLocation"
});
$('#collapseLocation, #collapseMessage').on('shown.bs.collapse', function () {
    console.log("COUCOU");
    $('#accordion > div.panel').collapse('hide');
})
// $('#collapseMessage').on('hidden.bs.collapse', function () {
//     $('div[data-parent="#accordion"]').collapse('hide');
// })

// bind radio buttons
$("#moveForm input[type=radio]").change(function() {
    toggleLocationSubfrom();
});

// bind radio buttons
$("#moveForm input[type=text], #moveForm input[type=radio]").change(function() {
    colorizeParentPanel(this);
});

// bind text buttons
$("#moveForm input[type=text]").keyup(function() {
    colorizeParentPanel(this);
});

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
    convertCoordinates($("#latlon").val());
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
// ----------------------------------- JQUERY - RUCHY - END
