{\Assets::instance()->addJs(GK_CDN_JQUERY_JS, 100)}
{\Assets::instance()->addJs(GK_CDN_BOOTSTRAP_JS, 100)}
{\Assets::instance()->addJs(GK_CDN_MOMENT_JS, 100)}
{\Assets::instance()->addJs(GK_CDN_BOOTSTRAP_MAXLENGTH_JS)}
{\Assets::instance()->addJs(GK_CDN_PREVIEW_IMAGE_JQUERY_JS)}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_PARSLEY_BOOTSTRAP3_JS_URL)}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_PARSLEY_JS_URL)}
{\Assets::instance()->addJs(sprintf('%s/%s.js', GK_CDN_LIBRARIES_PARSLEY_JS_LANG_DIR_URL, \Multilang::instance()->current))}
{\Assets::instance()->addJs(GK_CDN_SPIN_JS)}

{\Assets::instance()->renderGroup(\Assets::instance()->getAssets('footer')) nofilter}
<script type="text/javascript">
    (function($) {
        $( document ).ready( function () {

{include file="js/modal.tpl.js"}

{include file="js/maxlenght.tpl.js"}

{include file="js/tooltips.tpl.js"}

{if !$f3->get('SESSION.IS_LOGGED_IN')}{include 'js/dialog_login.js.tpl'}{/if}
{block name=javascript_modal}{/block}
{block name=javascript}{/block}

        });
    })(jQuery);

</script>
