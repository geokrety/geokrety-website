
// Load GeoKrety near home position
let geoJsonAjax;
let geoJsonLayer;
let bounded = false;
let showBy = 'days';
let myRenderer = L.canvas({ padding: 0.5 });

let mode = 'individual';

let markers = L.markerClusterGroup({
    maxClusterRadius: 40,
    spiderfyOnMaxZoom: true
});

let geojsonMarkerOptions = {
    renderer: myRenderer,
    radius: 4,
    fillColor: "#ff7800",
    color: "#000",
    weight: 1,
    opacity: 1,
    fillOpacity: 0.8
};

retrieve();

function getColorDistance(x) {
    return  x < 500     ?    '#ffffb2':
            x < 1000    ?    '#fecc5c':
            x < 1500    ?    '#fd8d3c':
            x < 2000    ?    '#f03b20':
            '#bd0026' ;
}
function getColorCachesCount(x) {
    return  x < 10     ?    '#ffffb2':
            x < 50     ?    '#fecc5c':
            x < 100    ?    '#fd8d3c':
            x < 200    ?    '#f03b20':
            '#bd0026' ;
}
function getColorMovedDays(x) {
    return  x < 10     ?    '#258d03':
            x < 90     ?    '#68c742':
            x < 180    ?    '#e7f65f':
            x < 365    ?    '#fd8d3c':
            x < 730    ?    '#f03b20':
            '#84001a' ;
}

// mapCaptionAccordion
function pointToLayer(feature, latlng) {
    let marker = geojsonMarkerOptions;
    if (showBy === 'distance') {
        marker.fillColor = getColorDistance(feature.properties.distance)
    } else if (showBy === 'caches') {
        marker.fillColor = getColorCachesCount(feature.properties.caches_count)
    } else {
        marker.fillColor = getColorMovedDays(feature.properties.days)
    }
    return L.circleMarker(latlng, geojsonMarkerOptions);
}

function onEachFeature(feature, layer) {
    // TODO properly escape strings (XSS)

    let owner = feature.properties.owner_username;
    if (feature.properties.owner !== null) {
        let user_link = "{'user_details'|alias:'userid=%USERID%'}".replace('%USERID%', feature.properties.owner);
        owner = `<a href="${ user_link }">${ owner }</a>`;
    } else {
        owner = `<em class="user-anonymous">${ owner }</em>`;
    }

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
        <h5>{t}Informations:{/t}</h5>
        <dl class="dl-horizontal">
            <dt>{t}Owner:{/t}</dt><dd>${ owner }</dd>
            <dt>{t}Distance:{/t}</dt><dd>${ feature.properties.distance } km</dd>
            <dt>{t}Caches:{/t}</dt><dd>${ feature.properties.caches_count }</dd>
        </dl>
        <h5>{t}Actual position:{/t}</h5>
        <dl class="dl-horizontal">
            <dt>{t}Waypoint:{/t}</dt><dd>${ feature.properties.waypoint === null ? feature.properties.lat + '/' + feature.properties.lon : feature.properties.waypoint }</dd>
            <dt>{t}Author:{/t}</dt><dd>${ author }</dd>
            <dt>{t}Date:{/t}</dt><dd title="${ moment.utc(feature.properties.moved_on_datetime).local() }">${ moment.utc(feature.properties.moved_on_datetime).local().fromNow() }</dd>
            <dt>{t}Country:{/t}</dt><dd><span class="flag-icon flag-icon-${ feature.properties.country }" title="${ feature.properties.country }"></span></dd>
            <dt>{t}Elevation:{/t}</dt><dd>${ feature.properties.elevation } m</dd>
        </dl>
        <div class="text-center">
            ${ picture_element }
        </div>
    `);
}

function retrieve() {
    map.spin(true);
    geoJsonAjax = jQuery.ajax({
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
                if (fitBound && !bounded && geoJsonLayer.getLayers().length) {
                    map.fitBounds(geoJsonLayer.getBounds(), { padding: [50, 50] });
                    bounded = true;
                }
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

$('#mapCaptionAccordion').on('shown.bs.collapse', function () {
    showBy = $("#mapCaptionAccordion div.panel-collapse.in").data('sort');
    retrieve();
})

map.on('popupopen', function (e) {
    let px = map.project(e.target._popup._latlng); // find the pixel location on the map where the popup anchor is
    px.y -= e.target._popup._container.clientHeight/2; // find the height of the popup container, divide by 2, subtract from the Y axis of marker location
    map.panTo(map.unproject(px), { animate: true }); // pan to new center
});

map.on('zoomend', function() {
    retrieve();
});

map.on('dragend', function() {
    retrieve();
});
