{extends file='base.tpl'}

{\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS)}
{\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_JS)}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL)}
{if GK_GOOGLE_RECAPTCHA_JS_URL}{\Assets::instance()->addJs(GK_GOOGLE_RECAPTCHA_JS_URL)}{/if}

{block name=content}
{include file='banners/geokret_adopt.tpl'}
    <div id="geokretAvatar" class="{if $geokret->isOwner()}dropzone{/if}">
{include file='blocks/geokret/details.tpl'}
{include file='blocks/geokret/pictures.tpl'}
    </div>
{include file='blocks/geokret/mission.tpl'}
{include file='blocks/geokret/found_it.tpl'}
{include file='blocks/geokret/actions.tpl'}
{include file='blocks/geokret/map.tpl'}
    <hr/>
{include file='blocks/geokret/moves.tpl'}
{/block}

{block name=javascript}
{if $geokret->caches_count}
{include file='js/_map_init.tpl.js'}
  initializeMap();
  // TODO load moves as geojson
{/if}

// Bind modal
{include 'js/dialogs/dialog_move_delete.tpl.js'}
{include 'js/dialogs/dialog_move_comment.tpl.js'}
{include 'js/dialogs/dialog_contact_user.tpl.js'}
{include 'js/dialogs/dialog_offer_for_adoption.tpl.js'}
{include 'js/dialogs/dialog_picture_actions.tpl.js'}
{*{include 'js/dialogs/dialog_geokret_avatar_upload.tpl.js'}*}

{if $geokret->isOwner()}
{include 'js/geokrety/geokret_avatar_upload.tpl.js'}
{/if}

{/block}
