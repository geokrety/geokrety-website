// ----------------------------------- JQUERY - USER GEOKRET NEAR HOME MAP - BEGIN

{include file='js/_map_init.tpl.js'}
map = initializeMap();

function onEachFeature(feature, layer) {
    layer.bindPopup(`
                <div class="scaled">
                    <h6>${ feature.properties.name }</h6>
                    <dl class="dl-horizontal">
                        <dt>{t}Waypoint:{/t}</dt><dd>${ feature.properties.waypoint === null ? feature.properties.lat + '/' + feature.properties.lon : feature.properties.waypoint }</dd>
                    </dl>
                </div>
            `, {
        maxWidth: "auto"
    });
}

// Load GeoKrety near home position
let geoJsonLayer = new L.GeoJSON.AJAX("{'user_geokrety_near_home_geojson'|alias}", {
    onEachFeature: onEachFeature,
    pointToLayer: pointToLayer,
});
geoJsonLayer.addTo(map);

// ----------------------------------- JQUERY - USER GEOKRET NEAR HOME MAP - END
