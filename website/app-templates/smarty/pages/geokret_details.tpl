{extends file='base.tpl'}

{\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS)}
{\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_JS)}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL)}
{if GK_GOOGLE_RECAPTCHA_JS_URL}{\Assets::instance()->addJs(GK_GOOGLE_RECAPTCHA_JS_URL)}{/if}

{block name=content}
{include file='banners/geokret_adopt.tpl'}
{include file='blocks/geokret/details.tpl'}
{include file='blocks/geokret/mission.tpl'}
{include file='blocks/geokret/found_it.tpl'}
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
{include 'js/dialog_move_delete.js.tpl'}
{include 'js/dialog_move_comment.js.tpl'}
{include 'js/dialog_contact_user.tpl.js'}
{include 'js/dialog_offer_for_adoption.tpl.js'}
{/block}
