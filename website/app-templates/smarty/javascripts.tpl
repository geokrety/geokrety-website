{\GeoKrety\Assets::instance()->addJs(GK_CDN_JQUERY_JS, 100) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_BOOTSTRAP_JS, 100) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_MOMENT_JS, 100) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_MOMENT_TIMEZONE_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_BOOTSTRAP_MAXLENGTH_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_PREVIEW_IMAGE_JQUERY_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LIBRARIES_PARSLEY_BOOTSTRAP3_JS_URL)}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LIBRARIES_PARSLEY_JS_URL) && ''}
{* Load Parsley i18n - skip for inline-translation mode since it's not a real language *}
{if \Multilang::instance()->current !== 'inline-translation'}
{\GeoKrety\Assets::instance()->addJs(sprintf('%s/%s.js', GK_CDN_LIBRARIES_PARSLEY_JS_LANG_DIR_URL, \Multilang::instance()->current)) && ''}
{/if}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_SPIN_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_DROPZONE_JS) && ''}
{\GeoKrety\Assets::instance()->addJsAsync(GK_CDN_LAZYSIZES_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LIGHTBOX_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_BOOTSTRAP_3_TYPEAHEAD_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_D3_JS) && ''}
{*{\GeoKrety\Assets::instance()->addJs(GK_CDN_D3_QUEUE_JS) && ''}*}
{*{\GeoKrety\Assets::instance()->addJs(GK_CDN_D3_PLOT_JS) && ''}*}
{*{\GeoKrety\Assets::instance()->addJs(GK_CDN_D3_PATH_JS) && ''}*}
{*{\GeoKrety\Assets::instance()->addJs(GK_CDN_D3_SHAPE_JS) && ''}*}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_HTML5_QRCODE_JS) && ''}

{\Assets::instance()->renderGroup(\GeoKrety\Assets::instance()->getAssets('footer')) nofilter}
<script type="text/javascript" nonce="{\GeoKrety\Service\SecurityHeaders::instance()->getNonce()}">
    Dropzone.autoDiscover = false;
    (function($) {
        $(document).ready(function() {

{include file='js/modal.tpl.js'}
{include file='js/maxlenght.tpl.js'}
{include file='js/tooltips.tpl.js'}
{include file='js/lightbox2.tpl.js'}
{include file='js/dialogs/dialog_login.tpl.js'}{*load js/dialogs/dialog_login all the time as it may be necessary when user leave it's session open too long*}
{include file='js/search_advanced.tpl.js'}

{block name=javascript_modal}{/block}
{block name=javascript}{/block}

        });
    })(jQuery);

</script>
