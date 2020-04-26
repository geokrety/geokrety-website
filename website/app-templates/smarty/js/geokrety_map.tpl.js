// ----------------------------------- JQUERY - GKMAP - BEGIN

{include file='js/_map_init.tpl.js'}
map = initializeMap();

let mode = 'individual';

let markers = L.markerClusterGroup({
    maxClusterRadius: 40,
    spiderfyOnMaxZoom: true
});

function onEachFeature(feature, layer) {
    // TODO properly escape strings (XSS)
    let author = feature.properties.author_username;
    if (feature.properties.author !== null) {
        let user_link = "{'user_details'|alias:'userid=%USERID%'}".replace('%USERID%', feature.properties.author);
        author = `<a href="${ user_link }">${ author }</a>`;
    } else {
        author = `<em class="user-anonymous">${ author }</em>`;
    }
    let geokret_link = "{'geokret_details'|alias:'gkid=%GKID%'}".replace('%GKID%', feature.properties.gkid);
    let picture_url;
    let picture_element = '';
    if (feature.properties.avatar_key !== null) {
        picture_url = "{'picture_proxy_thumbnail'|alias:'key=%KEY%'}".replace('%KEY%', feature.properties.avatar_key);
        picture_element = `<img src="${ picture_url }" width="100px" height="100px">`;
    }
    layer.bindPopup(`
        <h4><a href="${ geokret_link }">${ feature.properties.name }</a></h4>
        <dl class="dl-horizontal">
            <dt>{t}Author:{/t}</dt><dd>${ author }</dd>
            <dt>{t}Waypoint:{/t}</dt><dd>${ feature.properties.waypoint === null ? feature.properties.lat + '/' + feature.properties.lon : feature.properties.waypoint }</dd>
            <dt>{t}Distance:{/t}</dt><dd>${ feature.properties.distance } km</dd>
            <dt>{t}Caches:{/t}</dt><dd>${ feature.properties.caches_count }</dd>
            <dt>{t}Country:{/t}</dt><dd><span class="flag-icon flag-icon-${ feature.properties.country }" title="${ feature.properties.country }"></span></dd>
            <dt>{t}Elevation:{/t}</dt><dd>${ feature.properties.elevation } m</dd>
            <dt>{t}Date:{/t}</dt><dd title="${ moment.utc(feature.properties.moved_on_datetime).local() }">${ moment.utc(feature.properties.moved_on_datetime).local().fromNow() }</dd>
        </dl>
        <div class="text-center">
            ${ picture_element }
        </div>
    `);
}

// Load GeoKrety near home position
function buildurl() {
    let bounds = map.getBounds();
    return "{'geokrety_map_geojson'|alias}"
        .replace('@xmin', bounds.getWest())
        .replace('@ymin', bounds.getSouth())
        .replace('@xmax', bounds.getEast())
        .replace('@ymax', bounds.getNorth());
}

let geoJsonLayer;
retrieve();

function retrieve() {
    map.spin(true);
    jQuery.ajax({
        dataType: "json",
        url: buildurl(),
        success: function (data) {
            if (map.hasLayer(geoJsonLayer)) {
                map.removeLayer(geoJsonLayer);
            }
            if (mode === 'individual') {
                geoJsonLayer = L.geoJson(data, {
                    onEachFeature: onEachFeature,
                    pointToLayer: pointToLayer,
                });
                geoJsonLayer.addTo(map);
            } else if (mode === 'cluster') {
                markers.addLayer(geoJsonLayer);
                layer = map.addLayer(markers);
            }
            map.spin(false);
        },
        error: function () {
            map.spin(false);
        }
    });
}

map.on('zoomend', function() {
    retrieve();
});

map.on('dragend', function() {
    retrieve();
});

// ----------------------------------- JQUERY - GKMAP - END
