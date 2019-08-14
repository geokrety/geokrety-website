{if $user && $user->isCurrentUser() && $user->email_invalid}
<div class="alert alert-danger" role="alert">
  {t}Sorry, but we have troubles sending your email notifications. Is you email still valid?{/t}
</div>
{/if}
