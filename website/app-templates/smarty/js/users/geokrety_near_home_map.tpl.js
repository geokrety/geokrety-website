// ----------------------------------- JQUERY - USER GEOKRET NEAR HOME MAP - BEGIN

{include file='js/_map_init.tpl.js'}
map = initializeMap();
{include file='js/map_geojson_loader.tpl.js'}

function buildurl() {
    let bounds = map.getBounds();
    return "{'user_geokrety_near_home_geojson'|alias}"
        .replace('@xmin', bounds.getWest())
        .replace('@ymin', bounds.getSouth())
        .replace('@xmax', bounds.getEast())
        .replace('@ymax', bounds.getNorth());
}

function onEachFeature(feature, layer) {
    let geokret_link = "{'geokret_details'|alias:'gkid=%GKID%'}".replace('%GKID%', feature.properties.gkid);
    layer.bindPopup(`
                <div class="scaled">
                    <h4><a href="${ geokret_link }">${ feature.properties.name }</a></h4>
                    <dl class="dl-horizontal">
                        <dt title="{t}Waypoint:{/t}">{t}Waypoint:{/t}</dt><dd>${ feature.properties.waypoint === null ? feature.properties.lat + '/' + feature.properties.lon : feature.properties.waypoint }</dd>
                        <dt>{t}Date:{/t}</dt><dd title="${ moment.utc(feature.properties.moved_on_datetime).local() }">${ moment.utc(feature.properties.moved_on_datetime).local().fromNow() }</dd>
                    </dl>
                </div>
            `, {
        maxWidth: "auto"
    });
}

// ----------------------------------- JQUERY - USER GEOKRET NEAR HOME MAP - END
