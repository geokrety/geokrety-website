{extends file='base.tpl'}

{block name=css}
<link rel="stylesheet" href="{GK_CDN_LEAFLET_CSS}">
{/block}

{block name=js}
<script type="text/javascript" src="{GK_CDN_LEAFLET_JS}"></script>
{/block}

{block name=content}
{include file='banners/geokret_adopt.tpl'}
{include file='blocks/geokret/details.tpl'}
{include file='blocks/geokret/mission.tpl'}
{include file='blocks/geokret/pictures.tpl'}
{include file='blocks/geokret/actions.tpl'}
{include file='blocks/geokret/map.tpl'}
<hr />
{include file='blocks/geokret/moves.tpl'}
{/block}

{block name=javascript}
{include file='js/_map_init.tpl.js'}

initializeMap();
// TODO load moves as geojson

// Bind modal
{include 'js/dialog_move_comment_delete.js.tpl'}
{/block}
