{extends file='base.tpl'}

{block name=title}{t}Log a GeoKret{/t}{/block}

{\GeoKrety\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS) && ''}
{\GeoKrety\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL) && ''}
{\GeoKrety\Assets::instance()->addCss(GK_CDN_BOOTSTRAP_DATETIMEPICKER_CSS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LEAFLET_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LATINIZE_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_BOOTSTRAP_DATETIMEPICKER_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_BOOTSTRAP_3_TYPEAHEAD_JS) && ''}
{\GeoKrety\Assets::instance()->addCss(GK_CDN_TOM_SELECT_CSS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_TOM_SELECT_JS) && ''}
{if !$f3->get('SESSION.CURRENT_USER')}
{include file='macros/recaptcha.tpl'}
{/if}

{block name=content}
{include file='banners/geokret_anonymous_log.tpl'}
{include file='forms/move.tpl'}
{/block}

{block name=javascript}
{include file="js/moves/geokret_move.tpl.js"}
{if $f3->get('SESSION.CURRENT_USER')}
{include file="js/dialogs/dialog_geokret_move_select_from_inventory.tpl.js"}
{/if}
{include file="js/moves/tomselect-inventory.tpl.js"}
{if GK_DEVEL}
{* used by Tests-qa in Robot  Framework *}
$("#mapid").data({ map: map });
$("#comment").data({ editor: inscrybmde });
{/if}
{/block}
