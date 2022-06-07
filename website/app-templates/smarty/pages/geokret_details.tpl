{extends file='base.tpl'}

{block name=title}{$geokret->gkid} - {$geokret->name}{/block}

{\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS) && ''}
{\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL) && ''}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_JS) && ''}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL) && ''}
{include file='macros/recaptcha.tpl'}

{block name=content}
{include file='banners/geokret_adopt.tpl'}
    <div id="geokretAvatar" class="{if $geokret->isOwner()}dropzone{/if}">
{include file='blocks/geokret/details.tpl'}
{include file='blocks/geokret/pictures.tpl'}
    </div>
{include file='blocks/geokret/mission.tpl'}
{include file='blocks/geokret/found_it.tpl'}
{if $f3->get('SESSION.CURRENT_USER')}
    {include file='blocks/geokret/actions.tpl'}
{/if}

    <a class="anchor" id="moves"></a>
{include file='blocks/geokret/map.tpl'}
    <hr/>
{include file='blocks/geokret/moves.tpl'}
{/block}

{block name=javascript}
{if $geokret->caches_count}
{include file='js/geokrety/geokrety_details_map.tpl.js'}
{if GK_DEVEL}
{* used by Tests-qa in Robot  Framework *}
$("#mapid").data({ map: map });
{/if}
{/if}

// Bind modal
{include 'js/dialogs/dialog_move_delete.tpl.js'}
{include 'js/dialogs/dialog_move_comment.tpl.js'}
{include 'js/dialogs/dialog_contact_user.tpl.js'}
{include 'js/dialogs/dialog_offer_for_adoption.tpl.js'}
{include 'js/dialogs/dialog_picture_actions.tpl.js'}
{include 'js/dialogs/dialog_geokret_mark_as_archived.tpl.js'}
{include 'js/dialogs/dialog_geokret_watch.tpl.js'}

{if $geokret->isOwner()}
{include 'js/geokrety/geokret_avatar_upload.tpl.js'}
{/if}
{include 'js/moves/move_picture_upload.tpl.js'}

{/block}
