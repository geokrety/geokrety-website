{if $user && $user->isCurrentUser() && is_null($user->email) && is_null($user->email_activation)}
<div class="alert alert-danger" role="alert">
  {t}Sorry, but your account has no email registered. You will not be able to recover from a password loss! Also, you will not receive daily notifications of your GeoKrety or watched GeoKrety.{/t}
</div>
{/if}
