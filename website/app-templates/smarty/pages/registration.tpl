{extends file='base.tpl'}

{\Assets::instance()->addCss(GK_CDN_STRENGTHIFY_CSS)}
{\Assets::instance()->addJs(GK_CDN_STRENGTHIFY_JS)}
{if GK_GOOGLE_RECAPTCHA_JS_URL}{\Assets::instance()->addJs(GK_GOOGLE_RECAPTCHA_JS_URL)}{/if}

{block name=content}

<div class="panel panel-default">
    <div class="panel-heading">
        {if isset($social_auth) && $social_auth}
            {t provider=$social_auth_data->provider}Create an account using %1{/t}
        {else}
            {t}Create an account{/t}
        {/if}
    </div>
    <div class="panel-body">
        {if isset($social_auth) && $social_auth}
            {include file='forms/registration_social_auth.tpl'}
        {else}
            {include file='forms/registration.tpl'}
        {/if}
    </div>
</div>
{/block}

{block name=javascript}
{include 'js/registration/registration.validation.tpl.js'}
{include 'js/dialogs/dialog_terms_of_use.tpl.js'}
{/block}
