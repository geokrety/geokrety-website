{extends file='base.tpl'}

{block name=css}
<link rel="stylesheet" href="{GK_CDN_LEAFLET_CSS}">
<link rel="stylesheet" href="{GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL}">
<link rel="stylesheet" href="{GK_CDN_BOOTSTRAP_DATETIMEPICKER_CSS}">
{/block}

{block name=js}
<script type="text/javascript" src="{GK_CDN_LEAFLET_JS}"></script>
<script type="text/javascript" src="{GK_CDN_LATINIZE_JS}"></script>
<script type="text/javascript" src="{GK_CDN_MOMENT_JS}"></script>
<script type="text/javascript" src="{GK_CDN_BOOTSTRAP_DATETIMEPICKER_JS}"></script>
<script type="text/javascript" src="{GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL}"></script>
<script type="text/javascript" src="{GK_CDN_BOOTSTRAP_3_TYPEAHEAD_JS}"></script>
{if GK_GOOGLE_RECAPTCHA_JS_URL}<script type="text/javascript" src="{GK_GOOGLE_RECAPTCHA_JS_URL}"></script>{/if}
{/block}

{block name=content}
{include file='banners/geokret_anonymous_log.tpl'}
{include file='forms/move.tpl'}
{/block}

{block name=javascript}
{include file="js/geokret_move.tpl.js"}
{include file="js/dialog_geokret_move_select_from_inventory.tpl.js"}
{/block}
