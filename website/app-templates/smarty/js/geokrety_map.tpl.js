// ----------------------------------- JQUERY - GKMAP - BEGIN

{include file='js/_map_init.tpl.js'}
map = initializeMap();
{include file='js/map_geojson_loader.tpl.js'}

function buildurl() {
    let bounds = map.getBounds();
    return "{'geokrety_map_geojson'|alias}"
        .replace('@xmin', bounds.getWest())
        .replace('@ymin', bounds.getSouth())
        .replace('@xmax', bounds.getEast())
        .replace('@ymax', bounds.getNorth());
}


// ----------------------------------- JQUERY - GKMAP - END
