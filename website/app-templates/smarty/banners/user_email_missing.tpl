{if isset($current_user) && !$current_user->hasEmail()}
<div class="alert alert-warning" role="alert">
  {t}Sorry, but your account has no email registered.{/t}
  {t}You will not be able to recover from a password loss!{/t}
  {t}Also, you will not receive daily notifications of your GeoKrety or watched GeoKrety.{/t}
  {t escape=no url1='user_update_email'|alias}Please consider <a href="%1">adding an email address</a>.{/t}
</div>
{/if}
