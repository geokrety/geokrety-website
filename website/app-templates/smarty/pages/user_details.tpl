{extends file='base.tpl'}

{block name=css}
<link rel="stylesheet" href="{GK_CDN_LEAFLET_CSS}">
<link rel="stylesheet" href="{GK_CDN_STRENGTHIFY_CSS}">
{/block}

{block name=js}
<script type="text/javascript" src="{GK_CDN_LEAFLET_JS}"></script>
<script type="text/javascript" src="{GK_CDN_LEAFLET_AJAX_JS}"></script>
<script type="text/javascript" src="{GK_CDN_STRENGTHIFY_JS}"></script>
{/block}

{block name=content}
<div class="row">
    <div class="col-xs-12 col-md-9">
        {include file='blocks/user/details.tpl'}
        {include file='blocks/user/awards.tpl'}
        {include file='blocks/user/badges.tpl'}
    </div>
    <div class="col-xs-12 col-md-3">
        {include file='blocks/user/actions.tpl'}
        {if $user->isCurrentUser()}
        {include file='blocks/user/map_home.tpl'}
        {/if}
        {include file='blocks/user/statpic.tpl'}
    </div>
</div>
{/block}

{block name=javascript}
{include file='js/_map_init.tpl.js'}

var map = initializeMap();

// Load GeoKrety near home position
var bounds = map.getBounds();
var geojsonLayer = new L.GeoJSON.AJAX("{GK_MAP_URL}/geojson?latTL="+bounds.getNorthWest().lat+"&lonTL="+bounds.getSouthEast().lng+"&latBR="+bounds.getSouthEast().lat+"&lonBR="+bounds.getNorthWest().lng+"&limit=500&json=1&daysFrom=0&daysTo=45");
geojsonLayer.addTo(map);

// Bind modal
{include 'js/dialog_user_details.js.tpl'}
{/block}
