{extends file='base.tpl'}

{\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS)}
{\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL)}
{\Assets::instance()->addCss(GK_CDN_BOOTSTRAP_DATETIMEPICKER_CSS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_JS)}
{\Assets::instance()->addJs(GK_CDN_LATINIZE_JS)}
{\Assets::instance()->addJs(GK_CDN_MOMENT_JS)}
{\Assets::instance()->addJs(GK_CDN_BOOTSTRAP_DATETIMEPICKER_JS)}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL)}
{\Assets::instance()->addJs(GK_CDN_BOOTSTRAP_3_TYPEAHEAD_JS)}
{if GK_GOOGLE_RECAPTCHA_JS_URL}{\Assets::instance()->addJs(GK_GOOGLE_RECAPTCHA_JS_URL)}{/if}

{block name=content}
{include file='banners/geokret_anonymous_log.tpl'}
{include file='forms/move.tpl'}
{/block}

{block name=javascript}
{include file="js/geokret_move.tpl.js"}
{include file="js/dialog_geokret_move_select_from_inventory.tpl.js"}
{/block}
