{extends file='base.tpl'}

{block name=title}{t}Registration{/t}{/block}

{\GeoKrety\Assets::instance()->addCss(GK_CDN_STRENGTHIFY_CSS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_STRENGTHIFY_JS) && ''}
{include file='macros/recaptcha.tpl'}

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
{include 'js/registration/registration.events.tpl.js'}
{include 'js/dialogs/dialog_terms_of_use.tpl.js'}
{include 'js/registration/username2.tpl.js'}
{/block}
