{extends file='base.tpl'}

{\Assets::instance()->addCss(GK_CDN_STRENGTHIFY_CSS)}
{\Assets::instance()->addJs(GK_CDN_STRENGTHIFY_JS)}
{if GK_GOOGLE_RECAPTCHA_JS_URL}{\Assets::instance()->addJs(GK_GOOGLE_RECAPTCHA_JS_URL)}{/if}

{block name=content}

<div class="panel panel-default">
    <div class="panel-heading">
        {t}Create an account{/t}
    </div>
    <div class="panel-body">
{include file='forms/registration.tpl'}
    </div>
</div>
{/block}

{block name=javascript}
{include 'js/registration.validation.tpl.js'}
{include 'js/dialog_terms_of_use.tpl.js'}
{/block}
