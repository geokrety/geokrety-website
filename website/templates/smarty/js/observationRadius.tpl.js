{literal}

// ------------------------------- JQUERY - USER EDIT OBSERVATION AREA - BEGIN


var circle = L.circle([48.85, 2.35], {
    color: 'red',
});

var inputCoordinates = $("#inputCoordinates");
var inputRadius = $("#inputRadius");
var map = initMap();

// Load on user coords
centerMap(inputCoordinates.val().split(" "));

// Watch changes in input
inputCoordinates.change(function() {
    centerMap(inputCoordinates.val().split(" "));
});
inputRadius.change(function() {
    setRadius(parseInt(inputRadius.val()));
});

// Watch map move
map.on("moveend", function() {
    var center = map.getCenter();
    inputCoordinates.val(center.lat.toFixed(5) + ' ' + center.lng.toFixed(5))
    circle.setLatLng(center);
});

function setRadius(radius) {
    if (radius >= 0 && radius <= 10) {
        circle.setRadius(parseInt(radius) * 1000);
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

function initMap() {
    var map = L.map("mapid", {
        worldCopyJump: true
    });
    var osmUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
    var osmAttrib = "Map data © <a href=\"https://www.openstreetmap.org\">OpenStreetMap</a> contributors";
    var osm = new L.TileLayer(osmUrl, {
        minZoom: 0,
        maxZoom: 12,
        attribution: osmAttrib
    });

    // start the map in Paris
    map.setView(new L.LatLng(48.85, 2.35), 3);
    circle.addTo(map);
    setRadius(parseInt(inputRadius.val()));

    map.addLayer(osm);
    return map;
}



// ------------------------------- JQUERY - USER EDIT OBSERVATION AREA - END

{/literal}
