{if !$f3->get('SESSION.CURRENT_USER')}
<div class="alert alert-warning" role="alert">
    {t escape=no login={login_link}}Even if it is - for now - not required, we recommend you to <a href="%1">login</a>.{/t}
    {t escape=no create={'registration'|alias}}Do not hesitate to <a href="%2">create an account</a>.{/t}
    {t}You then will be able to manage your GeoKrety inventory, upload pictures, edit your logs and of course create your own GeoKrety!{/t}
</div>
{/if}
