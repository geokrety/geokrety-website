// ----------------------------------- JQUERY - GK EDIT - BEGIN

var map = L.map('mapid', {
    worldCopyJump: true
});
var osmUrl = {literal}'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';{/literal}
var osmAttrib = 'Map data © <a href="https://www.openstreetmap.org">OpenStreetMap</a> contributors';
var osm = new L.TileLayer(osmUrl, {
  minZoom: 0,
  maxZoom: 12,
  attribution: osmAttrib
});

if ({$user->latitude} == 0 && {$user->longitude} == 0) {
    var center = new L.LatLng(48.85, 2.35);
    var zoom = 3;
} else {
    var center = new L.LatLng({$user->latitude}, {$user->longitude});
    var zoom = 6;
}

// start the map
map.setView(center, zoom);
map.addLayer(osm);

var circle = L.circle(center, {
    color: 'red',
    radius: {$user->observationRadius * 1000}
}).addTo(map);

var bounds = map.getBounds();
var geojsonLayer = new L.GeoJSON.AJAX("https://api.geokretymap.org/geojson?latTL="+bounds.getNorthWest().lat+"&lonTL="+bounds.getSouthEast().lng+"&latBR="+bounds.getSouthEast().lat+"&lonBR="+bounds.getNorthWest().lng+"&limit=500&json=1&daysFrom=0&daysTo=45");
geojsonLayer.addTo(map);

// var bounds = [[44.31307, 4.70770], [44.31107, 4.70570]];
// L.rectangle(bounds, { color: "#ff7800", weight: 1}).addTo(map);

// ----------------------------------- JQUERY - GK EDIT - END
