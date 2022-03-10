// ----------------------------------- JQUERY - GEOKRET DETAILS MAP - BEGIN

{include file='js/_map_init.tpl.js'}
fitBound = true;
map = initializeMap();
let geoJsonLayer;

function moveTypeIcon(move_type) {
    return '<img src="{'GK_CDN_IMAGES_URL'|constant}/log-icons/{$geokret->type->getTypeId()}/'+move_type+'.png" title="'+moveTypeText(move_type)+'">';
}

function moveTypeText(move_type) {
    switch(move_type) {
        case 0:
            return '{t}Dropped{/t}';
            break;
        case 1:
            return '{t}Grabbed{/t}';
            break;
        case 2:
            return '{t}Comment{/t}';
            break;
        case 3:
            return '{t}Seen{/t}';
            break;
        case 4:
            return '{t}Archived{/t}';
            break;
        case 5:
            return '{t}Dipped{/t}';
            break;
    }
}

function OpenPopupForStep(step) {
    geoJsonLayer.eachLayer(function (feature){
        if (feature.feature.properties.step == step) {
            feature.openPopup();
        }
    });
}

function onEachFeature(feature, layer) {
    let author = feature.properties.author_username;
    if (feature.properties.author !== null) {
        let user_link = "{'user_details'|alias:'userid=%USERID%'}".replace('%USERID%', feature.properties.author);
        author = `<a href="${ user_link }">${ author }</a>`;
    } else {
        author = `<em class="user-anonymous">${ author }</em>`;
    }
    layer.bindPopup(`
        <h4>${ moveTypeIcon(feature.properties.move_type) } ${ moveTypeText(feature.properties.move_type) }</h4>
        <h5>{t}Move details:{/t}</h5>
        <dl class="dl-horizontal">
            <dt>{t}Author:{/t}</dt><dd>${ author }</dd>
            <dt>{t}Waypoint:{/t}</dt><dd>${ feature.properties.waypoint === null ? feature.properties.lat + '/' + feature.properties.lon : feature.properties.waypoint }</dd>
            <dt>{t}Distance:{/t}</dt><dd>${ feature.properties.distance } km</dd>
            <dt>{t}Country:{/t}</dt><dd><span class="flag-icon flag-icon-${ feature.properties.country }" title="${ feature.properties.country }"></span></dd>
            <dt>{t}Elevation:{/t}</dt><dd>${ feature.properties.elevation } m</dd>
            <dt>{t}Date:{/t}</dt><dd title="${ moment.utc(feature.properties.moved_on_datetime).local() }">${ moment.utc(feature.properties.moved_on_datetime).local().fromNow() }</dd>
        </dl>
        <div class="text-center">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default btn-xs popup-move-navigate" title="{t}Previous move{/t}" data-id="${ feature.properties.previous_step }">
                    {fa icon="arrow-left"}
                </button>
                <a href="#log${ feature.properties.move_id }" class="btn btn-default btn-xs popup-move-navigate" title="{t}Show move{/t}" data-id="${ feature.properties.step +1 }">
                    {t}Show move{/t}
                </a>
                <button type="button" class="btn btn-default btn-xs popup-move-navigate" title="{t}Next move{/t}" data-id="${ feature.properties.next_step }">
                    {fa icon="arrow-right"}
                </button>
            </div>
        </div>
        <div class="text-center">
            <small>#${ feature.properties.step }</small>
        </div>
    `);
}

jQuery.ajax({
    dataType: "json",
    url: "{'geokret_moves_geojson_paginate'|alias:sprintf('@gkid=%s,@page=%d', $geokret->gkid, $f3->get('PARAMS.page'))}",
    success: function (data) {
        geoJsonLayer = L.geoJson(data, {
            onEachFeature: onEachFeature,
        });
        geoJsonLayer.addTo(map);
        map.fitBounds(geoJsonLayer.getBounds(), { padding: [50, 50] });
    }
});

map.on('popupopen', function (e) {
    let px = map.project(e.target._popup._latlng); // find the pixel location on the map where the popup anchor is
    px.y -= e.target._popup._container.clientHeight/2; // find the height of the popup container, divide by 2, subtract from the Y axis of marker location
    map.panTo(map.unproject(px), { animate: true }); // pan to new center
});

$('body').on('click', 'button.popup-move-navigate', function (event) {
    let button = $(event.currentTarget);
    let id = button.data('id');
    OpenPopupForStep(id);

    // Scroll to map
    $([document.documentElement, document.body]).animate({
        scrollTop: $("#mapid").offset().top - 100
    }, 250);
});

// ----------------------------------- JQUERY - GEOKRET DETAILS MAP - END
