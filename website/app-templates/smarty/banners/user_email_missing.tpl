{if isset($current_user) && is_null($current_user->email) && !$current_user->email_activation}
<div class="alert alert-warning" role="alert">
  {t}Sorry, but your account has no email registered. You will not be able to recover from a password loss! Also, you will not receive daily notifications of your GeoKrety or watched GeoKrety.{/t}
</div>
{/if}
