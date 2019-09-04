{extends file='base.tpl'}

{block name=css}
<link rel="stylesheet" href="{GK_CDN_STRENGTHIFY_CSS}">
{/block}

{block name=js}
<script type="text/javascript" src="{GK_CDN_STRENGTHIFY_JS}"></script>
{if GK_GOOGLE_RECAPTCHA_JS_URL}<script type="text/javascript" src="{GK_GOOGLE_RECAPTCHA_JS_URL}"></script>{/if}
{/block}

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
