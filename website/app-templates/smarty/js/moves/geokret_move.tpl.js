// ----------------------------------- JQUERY - RUCHY - BEGIN

moment.locale('{\Multilang::instance()->current}')
moment.tz.setDefault(moment.tz.guess());
console.log("Detected Timezone:", moment().tz());

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

// Bind SimpleMDE editor
var inscrybmde = new InscrybMDE({
    element: $("#comment")[0],
    hideIcons: ['side-by-side', 'fullscreen', 'quote'],
    autoDownloadFontAwesome: false,
    forceSync: true,
    promptURLs: true,
    spellChecker: false,
	renderingConfig: {
		singleLineBreaks: false,
	},
    status: ["lines", "words", {
		className: "characters",
		defaultValue: function(el) {
			el.innerHTML = 0;
		},
		onUpdate: function(el) {
			el.innerHTML = $("#comment").val().length;
		}
	}],
});
inscrybmde.codemirror.on("change", function(){
    $("#comment").parsley().validate();
});

// Automatic scroll on panel open
$("#movePanelGroup div.panel-collapse").on("shown.bs.collapse", function(e) {
    var $panel = $(this).closest(".panel");
    $("html,body").animate({
        scrollTop: $panel.offset().top - 105
    }, 250);
});

// Force accordion collapse, useful when one panel is dynamically removed
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

// Force inscrybmde refresh on location show
$('#collapseMessage').on('shown.bs.collapse', function() {
    inscrybmde.codemirror.refresh();
})

// Initialize map
{include 'js/_map_init.tpl.js'}
// The marker on the map
let cacheMarker;

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
    // Prevent update loop if manually updated
    if ($("#latlon").val() !== "" && $("#latlon").is(":visible")) {
        return;
    }

    let latlngString = coordinates[0] + ' ' + coordinates[1];
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
    dropMarker();
}

// Display the marker on map
function showMarker(coordinates) {
    if (map === undefined) {
        return
    }
    if (cacheMarker === undefined) {
        cacheMarker = L.marker([0, 0]).addTo(map);
    }
    if (coordinates.length > 1) {
        cacheMarker.setLatLng(coordinates);
        map.setView(coordinates, 6);
    }
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

let wptHomeButtonToggled = false;
// Toggle log at home
function toggleHome() {
    wptHomeButtonToggled = !wptHomeButtonToggled;
    if (wptHomeButtonToggled) {
        // prepare display
        $("#wpt").val();
        showMap();
        showCoordinatesField();
        $("#latlon").val(''); // Reset latlon for successful positionUpdate()
        {if isset($current_user) and !is_null($current_user->home_latitude) and !is_null($current_user->home_longitude)}
        positionUpdate([{$current_user->home_latitude}, {$current_user->home_longitude}]);
        {/if}
    } else {
        // clear display
        hideCoordinatesField();
        positionClear();
    }
    // Disable some form elements
    $("#wptHomeButton").button('toggle');
    $("#wptSearchButton").prop('disabled', wptHomeButtonToggled)
    $("#wptSearchByNameButton").prop('disabled', wptHomeButtonToggled)
    $("#panelLocation input").each(function() {
        $(this).prop('disabled', wptHomeButtonToggled);
    });
    $("#latlon").prop('disabled', false); // Need to be enabled to have the value posted
    $("#latlon").prop('readonly', wptHomeButtonToggled); // Need to be enabled to have the value posted
    $("#locationHeader").html('');
}

// Show coordinates field
function toggleCoordinatesField() {
    if ($("#wpt").val().substring(0, 2).toUpperCase() != 'GC') {
        hideCoordinatesField();
    } else {
        showCoordinatesField();
    }
}
// Bind coordinates edit button
$("#mapField .panel-heading").on('click', function() {
    showCoordinatesField();
})

// Show coordinates field
function showCoordinatesField() {
    $("#coordinateField").removeClass("coordinates-togglable");
    $("#latlon").parsley().reset();
}
// Hide coordinates field
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
}

function isPanelGroupValid(element) {
    var panel = element.closest(".panel");
    return panel.hasClass("panel-success");
}

// Check if Waypoint is GC
function isWaypointGC() {
    return ($("#wpt").val().substring(0, 2).toUpperCase() !== 'GC');
}

// Check if a move type require coordinates
function isLocationNeeded() {
    var logtype = $("input[type=radio][name='logtype']:checked", '#moveForm').val();
    return logtype === undefined ? true : ['0', '3', '5'].includes(logtype);
}

// Check if a move type optionally require coordinates
function isLocationOptional() {
    var logtype = $("input[type=radio][name='logtype']:checked", '#moveForm').val();
    return logtype === undefined ? false : ['3'].includes(logtype);
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
    toggleRequiredCoordinates()
    if (isLocationNeeded()) {
        $('#additionalDataNumber').html('4');
        $("#panelLocation").show();
        if (!wptHomeButtonToggled) {
            $("#panelLocation input").each(function() {
                $(this).prop('disabled', false);
            })
        }
        $('#collapseLocation').collapse('show');
        // Allow empty waypoint
        if (isLocationOptional()) {
            $('#wpt').removeAttr('data-parsley-validate-if-empty');
        } else {
            $('#wpt').attr('data-parsley-validate-if-empty', '')
        }
    } else {
        $("#panelLocation").hide();
        $('#additionalDataNumber').html('3');
        $("#panelLocation input").each(function() {
            $(this).prop('disabled', true);
        })
        $('#collapseMessage').collapse('show');
    }
}

// Toggle searchByName button
function toggleSearchByNameButton() {
    if (isWaypointGC()) {
        $("#wptSearchByNameButton").show();
    } else {
        $("#wptSearchByNameButton").hide();
    }
}

// Toggle homeCoordinates button
function toggleHomeCoordinatesButton() {
    if ($("#wpt").val().length) {
        $("#wptHomeButton").hide();
    } else {
        $("#wptHomeButton").show();
    }
}

// Toggle required coordinates
function toggleRequiredCoordinates() {
    if (!isLocationNeeded()) {
        $("#latlon").removeAttr("required");
        return;
    }
    if (!isLocationOptional()) {
        $("#latlon").attr("required", "");
        return
    }
    if ($("#wpt").val() !== "") {
        $("#latlon").attr("required", "");
        return
    }
    $("#latlon").removeAttr("required");
}

// bind on submit
$("#submitButton").on('click', function() {
    $("#moveForm").submit();
    var firstError = $("#movePanelGroup div.panel.panel-danger:first div.panel-collapse");
    if (firstError) {
        firstError.collapse('show');
    }
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
    toggleHomeCoordinatesButton()
    toggleRequiredCoordinates()
});

// bind wptSearchByNameButton
$("#wptSearchByNameButton").bind("click", function() {
    $("#findbyCacheName").toggle().removeClass("hidden");
});

// bind wptHomeButton
$("#wptHomeButton").bind("click", function() {
    toggleHome();
});

// bind datetimepicker
$("#inputDate").click(function() {
    $("#datetimepicker").data("DateTimePicker").show();
});

// bind findbyCacheNameInput. typeahead for lookup by cache name
var findbyCacheNameInput = $("#findbyCacheNameInput");
findbyCacheNameInput.typeahead({
    items: {GK_CHECK_WAYPOINT_NAME_COUNT -1},
    minLength: {GK_CHECK_WAYPOINT_NAME_MIN_LENGTH},
    source: function(text, callback) {
        return $.post("{'validate_waypoint_name'|alias}", { 'name': text })
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

{include file = "js/moves/geokret_move.validation.tpl.js"}
{include file = "js/moves/geokret_move.inventory.tpl.js"}

// Convert parsed date to legacy format (date + hour + minute)
function dateToLegacyFormat() {
    date = $("#datetimepicker").data("DateTimePicker").date();
    $("#inputHiddenDate").val(date.format('YYYY-MM-DD'));
    $("#inputHiddenHour").val(date.hour());
    $("#inputHiddenMinute").val(date.minute());
    $("#inputHiddenSecond").val(date.second());
    $("#inputHiddenTimezone").val(date.format('Z'));
}

function clearLegacyFormat() {
    $("#inputHiddenDate").val('');
    $("#inputHiddenHour").val('');
    $("#inputHiddenMinute").val('');
    $("#inputHiddenTimezone").val('');
}

// Initialize date time
$("#datetimepicker").data("DateTimePicker").date({if $move->id}moment.utc("{$move->moved_on_datetime->format('Y-m-d H:i:s')}"){else}moment(){/if}.local());
dateToLegacyFormat();

// Initialize tracking code
if ($('#nr').val().length > 0) {
    $("#nr").trigger("focusout");
}

// Initialize logtype
if ($("input[type=radio][name='logtype']:checked", '#moveForm').val() != undefined) {
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

// Initialize username
{if !$f3->get('SESSION.CURRENT_USER')}
if ($('#username').val().length > 0) {
    $("#username").trigger("focusout");
}
{/if}

// Initialize comment
if ($('#comment').val().length > 0) {
    $("#comment").trigger("focusout");
}

// ----------------------------------- JQUERY - RUCHY - END
