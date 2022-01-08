var inputCoordinates = $("#inputCoordinates");
var inputRadius = $("#inputRadius");

var circle = L.circle([48.85, 2.35], {
    color: 'red',
});
circle.addTo(map);
setRadius(parseInt(inputRadius.val()));

// Load user coords
centerMap(inputCoordinates.val().split(" "));

inputRadius.change(function() {
    setTimeout(function() { setRadius(parseInt(inputRadius.val())) }, {GK_OBSERVATION_AREA_RADIUS_TIMEOUT})
});

// Watch map move
map.on("moveend", function() {
    var center = map.getCenter();
    var coordinates = center.lat.toFixed(5) + ' ' + center.lng.toFixed(5);
    inputCoordinates.attr('value', coordinates); // Used in the qa-tests
    inputCoordinates.val(coordinates);
    circle.setLatLng(center);
});

function setRadius(radius) {
    if (radius >= 0 && radius <= {GK_USER_OBSERVATION_AREA_MAX_KM}) {
        circle.setRadius(parseInt(radius) * 1000);
        if (radius <=0) {
            circle.setStyle({ color: 'red' })
        } else {
            circle.setStyle({ color: 'green' })
        }
    } else {
        inputRadius.val(5);
        setRadius(5);
    }
}

function centerMap(coordinates) {
    if (coordinates[0] == 0 && coordinates[1] == 0) {
        return;
    }
    var latLon = new L.LatLng(coordinates[0], coordinates[1]);
    var bounds = latLon.toBounds(circle.getRadius() + 10000);
    map.panTo(latLon).fitBounds(bounds);
    circle.setLatLng(latLon);
}

// Validate Coordinates
window.Parsley.addAsyncValidator('checkCoordinates', function(xhr) {
    var valid = 200 === xhr.status;
    var data = $.parseJSON(xhr.responseText);
    var latlon = $('#inputCoordinates').parsley();
    this.removeError('errorLatlon');
    if (valid) {
        centerMap([data.lat, data.lon]);
    } else {
        this.addError('errorLatlon', { message: data.error })
    }
    return valid;
}, "{'validate_coordinates'|alias}");
