// ----------------------------------- JQUERY - USER OWNED GEOKRET MAP - BEGIN

{include file='js/_map_init.tpl.js'}
fitBound = true;
map = initializeMap();
{include file='js/map_geojson_loader.tpl.js'}


function buildurl() {
    let bounds = map.getBounds();
    return "{'user_owned_geokrety_geojson'|alias}"
        .replace('@xmin', bounds.getWest())
        .replace('@ymin', bounds.getSouth())
        .replace('@xmax', bounds.getEast())
        .replace('@ymax', bounds.getNorth());
}

// ----------------------------------- JQUERY - USER OWNED GEOKRET MAP - END
