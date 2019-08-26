{extends file='base.tpl'}

{block name=css}
<link rel="stylesheet" href="{GK_CDN_LEAFLET_CSS}">
<link rel="stylesheet" href="{GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL}">
{/block}

{block name=js}
<script type="text/javascript" src="{GK_CDN_LEAFLET_JS}"></script>
<script type="text/javascript" src="{GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL}"></script>
{if GK_GOOGLE_RECAPTCHA_JS_URL}<script type="text/javascript" src="{GK_GOOGLE_RECAPTCHA_JS_URL}"></script>{/if}
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
{if $geokret->caches_count}
{include file='js/_map_init.tpl.js'}
initializeMap();
// TODO load moves as geojson
{/if}

// Bind modal
{include 'js/dialog_move_comment.js.tpl'}
{include 'js/dialog_contact_user.tpl.js'}
{include 'js/dialog_offer_for_adoption.tpl.js'}
{/block}
