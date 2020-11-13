{if isset($current_user) && $current_user->email_invalid === GeoKrety\Model\User::USER_EMAIL_INVALID}
<div class="alert alert-danger" role="alert">
  {t}Sorry, but we have troubles sending your email notifications. Is your email still valid?{/t}
</div>
{/if}
