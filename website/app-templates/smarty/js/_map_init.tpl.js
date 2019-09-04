// ----------------------------------- JQUERY - MAP INIT - BEGIN
var PARIS = new L.LatLng(48.85, 2.35);

function initializeMap() {
    var map = L.map('mapid', {
        worldCopyJump: true
    });
    var osmUrl = {literal}'https://tiles.wmflabs.org/hikebike/{z}/{x}/{y}.png';{/literal}
    // var osmUrl = {literal}'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';{/literal}
    var osmAttrib = 'Map data © <a href="https://www.openstreetmap.org">OpenStreetMap</a> contributors';
    var osm = new L.TileLayer(osmUrl, {
      minZoom: 0,
      // maxZoom: 12,
      attribution: osmAttrib
    });

    {if !isset($current_user) or is_null($current_user->home_latitude) or is_null($current_user->home_longitude)}
        var center = PARIS;
        var zoom = 3;
    {else}
        var center = new L.LatLng({$current_user->home_latitude}, {$current_user->home_longitude});
        var zoom = 6;
    {/if}

    // start the map
    map.setView(center, zoom);
    map.addLayer(osm);

    return map;
}
// ----------------------------------- JQUERY - MAP INIT - END
