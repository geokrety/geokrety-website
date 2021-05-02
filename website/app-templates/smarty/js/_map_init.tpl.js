// ----------------------------------- JQUERY - MAP INIT - BEGIN
let PARIS = new L.LatLng(48.85, 2.35);
let map;

function initializeMap() {
    let map = L.map('mapid', {
        worldCopyJump: true
    });
    let osmUrl = {literal}'https://tiles.wmflabs.org/hikebike/{z}/{x}/{y}.png';{/literal}
    let osmAttrib = 'Map data © <a href="https://www.openstreetmap.org">OpenStreetMap</a> contributors';
    let osm = new L.TileLayer(osmUrl, {
      minZoom: 0,
      // maxZoom: 12,
      attribution: osmAttrib
    });
    osm.on('load', function () { setTimeout(function () { $("#mapid").attr({ 'data-map-loaded': true }); }) });
    osm.on('loading', function () { $("#mapid").attr({ 'data-map-loaded': false }); });

    {if !isset($current_user) or is_null($current_user->home_latitude) or is_null($current_user->home_longitude)}
        let center = PARIS;
        let zoom = 5;
    {else}
        let center = new L.LatLng({$current_user->home_latitude}, {$current_user->home_longitude});
        let zoom = 10;
    {/if}

    // start the map
    map.setView(center, zoom);
    map.addLayer(osm);

    return map;
}

// ----------------------------------- JQUERY - MAP INIT - END
