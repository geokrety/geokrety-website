// ----------------------------------- JQUERY - USER OWNED GEOKRET MAP - BEGIN

{include file='js/_map_init.tpl.js'}
map = initializeMap();

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

$('input[name="show-by"]').click(function() {
    geojsonLayer.refresh();
});

map.on('popupopen', function (e) {
    let px = map.project(e.target._popup._latlng); // find the pixel location on the map where the popup anchor is
    px.y -= e.target._popup._container.clientHeight/2; // find the height of the popup container, divide by 2, subtract from the Y axis of marker location
    map.panTo(map.unproject(px), { animate: true }); // pan to new center
});

// Load GeoKrety near home position
let geojsonLayer = L.geoJson.ajax("{'user_owned_geokrety_geojson'|alias}", {
    onEachFeature: onEachFeature,
    pointToLayer: pointToLayer,
});
geojsonLayer.addTo(map);

geojsonLayer.on('data:loaded', function () {
    map.fitBounds(geojsonLayer.getBounds(), { padding: [50, 50] });
});

// ----------------------------------- JQUERY - USER OWNED GEOKRET MAP - END
