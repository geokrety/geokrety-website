{if isset($current_user) && !$current_user->hasEmail()}
<div class="alert alert-warning" role="alert">
  {t}Sorry, but your account has no email registered. You will not be able to recover from a password loss! Also, you will not receive daily notifications of your GeoKrety or watched GeoKrety.{/t}
  {t escape=no url1='user_update_email'|alias url2='user_details'|alias:sprintf('userid=%d', $current_user->id)}Please consider <a href="%1">adding an email address</a> or <a href="%2">link your account</a> with some OAuth provider.{/t}
</div>
{/if}
