// ----------------------------------- JQUERY - RUCHY - BEGIN

moment.locale('en'); // TODO: load from user settings

// Bind tooltip on NR field: Display label example
$(".tooltip_large").tooltip({
    placement: "top",
    template: '<div class="tooltip" role="tooltip" style="width:200px; height:148px;"><div class="tooltip-arrow"></div><div class="tooltip-inner large"></div></div>'
});

// Bind datepicker
$("#datetimepicker").datetimepicker({
    ignoreReadonly: true,
    collapse: false,
    sideBySide: true,
    showTodayButton: true,
    format: 'llll',
    locale: moment.locale()
});

// // Automatic scroll on panel open
// $("#movePanelGroup div.panel-collapse").on("shown.bs.collapse", function(e) {
//     var $panel = $(this).closest(".panel");
//     $("html,body").animate({
//         scrollTop: $panel.offset().top - 105
//     }, 250);
// });

// Force accordion collapse, usefull when one panel is dynamically removed
$('#movePanelGroup div.panel-collapse').on('show.bs.collapse', function() {
    $('div.panel-collapse').each(function() {
        $(this).collapse('hide');
    });
})

// Force map refresh on location show
$('#collapseLocation').on('shown.bs.collapse', function() {
    if ($('#wpt').val().length > 0) {
        showMap();
    }
})

// Initialize map
{include 'js/_map_init.tpl.js'}
// The map object
var map;
// The marker on the map
var cacheMarker;

// Display the map
function showMap() {
    $("#mapField").removeClass("map-togglable");
    if (map === undefined) {
        map = initializeMap();
    } else {
        map.invalidateSize();
    }
}

// Fill the coordinates
function positionUpdate(coordinates) {
    if (!coordinates[0] || !coordinates[1]) {
        return;
    }
    var latlngString = coordinates[0] + ' ' + coordinates[1];
    if ($("#latlon").val() != latlngString) {
        $("#latlon").val(latlngString);
        $("#latlon").parsley().validate();
    }
}

// Clear the coordinates
function positionClear() {
    if ($("#latlon").val() != '') {
        $("#latlon").val('').trigger("change");
    }
    isValidLatlon = false;
}

// Display the marker on map
function showMarker(coordinates) {
    // var latlng = L.latLng(coordinates);
    if (cacheMarker === undefined) {
        cacheMarker = L.marker([0, 0]).addTo(map);
    }
    cacheMarker.setLatLng(coordinates);
    map.setView(coordinates, 6);
}

// Remove the marker from map
function dropMarker() {
    if (map === undefined) {
        return;
    }
    if (cacheMarker !== undefined) {
        map.removeLayer(cacheMarker);
    }
    cacheMarker = undefined;
    map.setView(PARIS, 3);
}

// Show coordinates field
function toggleCoordinatesField() {
    if ($("#wpt").val().substring(0, 2).toUpperCase() != 'GC') {
        hideCoordinatesField();
    } else {
        $("#coordinateField").removeClass("coordinates-togglable");
        $("#latlon").parsley().reset();
    }
}
// Show coordinates field
function hideCoordinatesField() {
    $("#coordinateField").addClass("coordinates-togglable");
}

// Colorize a panel group if fields have errors
function colorizeParentPanel(element, valid) {
    var panel = element.closest(".panel");
    if (valid) {
        panel.addClass("panel-success")
            .removeClass("panel-default")
            .removeClass("panel-danger");
    } else {
        panel.addClass("panel-danger")
            .removeClass("panel-default")
            .removeClass("panel-success");
    }
};

// Check if Waypoint is GC
function isWaypointGC() {
    return ($("#wpt").val().substring(0, 2).toUpperCase() != 'GC');
}

// Check if a move type require coordinates
function isLocationNeeded() {
    var logtype = $('input[name=logtype]:checked', '#moveForm').val();
    return logtype === undefined ? true : ['0', '3', '5'].includes(logtype);
}

function logTypeToText(logtype) {
    var selectedLogTypeText = null;
    switch (logtype) {
        case "0":
            selectedLogTypeText = "{t}Dropped{/t}";
            break;
        case "1":
            selectedLogTypeText = "{t}Grabbed{/t}";
            break;
        case "3":
            selectedLogTypeText = "{t}Met{/t}";
            break;
        case "5":
            selectedLogTypeText = "{t}Dipped{/t}";
            break;
        case "2":
            selectedLogTypeText = "{t}Comment{/t}";
            break;
    }
    return selectedLogTypeText
}
// Toggle location panel based on move type requirement
function toggleLocationSubfrom() {
    if (isLocationNeeded()) {
        $("#panelLocation").show();
        $("#panelLocation input").each(function() {
            $(this).prop('disabled', false);
        })
    } else {
        $("#panelLocation").hide();
        $("#panelLocation input").each(function() {
            $(this).prop('disabled', true);
        })
    }
}

// Toggle location panel based on move type requirement
function toggleSearchByNameButton() {
    if (isWaypointGC()) {
        $("#wptSearchByNameButton").show();
    } else {
        $("#wptSearchByNameButton").hide();
    }
}

// bind radio buttons
$("#moveForm input[type=radio]").change(function() {
    toggleLocationSubfrom();
});

// bind on submit
$("#submitButton").on('click', function() {
    $("#moveForm").submit();
    var firstError = $("#movePanelGroup div.panel.panel-danger:first div.panel-collapse");
    if (firstError) {
        firstError.collapse('show');
    }
});

// bind nrSearchButton
$("#nrSearchButton").bind("click", function() {
    $("#nr").parsley().validate();
});

// bind coordinatesSearchButton
$("#coordinatesSearchButton").bind("click", function() {
    $("#latlon").parsley().validate();
});

// bind wptSearchButton
$("#wptSearchButton").bind("click", function() {
    $("#wpt").parsley().validate();
});

// Watch change on waypoint
$("#wpt").on('input', function() {
    toggleSearchByNameButton()
});

// bind wptSearchByNameButton
$("#wptSearchByNameButton").bind("click", function() {
    $("#findbyCacheName").toggle().removeClass("hidden");
});

// bind datetimepicker
$("#inputDate").click(function() {
    $("#datetimepicker").data("DateTimePicker").show();
});

// bind findbyCacheNameInput. typeahead for lookup by cache name
var findbyCacheNameInput = $("#findbyCacheNameInput");
findbyCacheNameInput.typeahead({
    items: 9,
    minLength: 4,
    source: function(text, callback) {
        return $.get("/check_wpt_name.php", { 'query': text })
            .done(function(data) {
                callback(data);
            })
    },
    matcher: function (item) {
        var it = this.displayText(item);
        return ~latinize(it.toLowerCase()).indexOf(latinize(this.query.toLowerCase()));
    },
    displayText: function (item) {
        var text = typeof item !== 'undefined' && typeof item.name != 'undefined' ? item.name : item;
        var waypoint = typeof item !== 'undefined' && typeof item.waypoint != 'undefined' ? item.waypoint + ' - ' : '';
        return waypoint + text;
    },
    updater: function (item) {
        if (typeof item !== 'undefined' && typeof item.waypoint != 'undefined') {
            $("#wpt").parsley().reset();
            $("#wpt").val(item.waypoint).trigger("focusout");
            $("#findbyCacheName").toggle();
        };
        return typeof item !== 'undefined' && typeof item.name != 'undefined' ? item.name : item;
    },
});

{include file = "js/ruchy.inventory.tpl.js"}
{include file = "js/ruchy.validation.tpl.js"}

// Initialize date time
$("#datetimepicker").data("DateTimePicker").date({{if $tripStep->ruchData}}moment.utc("{{$tripStep->getDate()}}"){{else}}moment(){{/if}}.local());
dateToLegacyFormat();

// Initialize tracking code
if ($('#nr').val().length > 0) {
    $("#nr").trigger("focusout");
}

// Initialize logtype
if ($('input[name=logtype]:checked', '#moveForm').val() != undefined) {
    $("#logType0").trigger("change");
}

// Initialize waypoint
if ($('#latlon').val().length > 0) {
    showMap();
    $("#latlon").trigger("focusout");
}
if ($('#wpt').val().length > 0) {
    $("#wpt").trigger("focusout");
}

{if !$isLoggedIn}
// Initialize username
if ($('#username').val().length > 0) {
    $("#username").trigger("focusout");
}
{/if}

// Initialize comment
if ($('#comment').val().length > 0) {
    $("#comment").trigger("focusout");
}

// ----------------------------------- JQUERY - RUCHY - END
