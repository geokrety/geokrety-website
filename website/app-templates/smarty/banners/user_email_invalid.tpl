{if isset($current_user) && !$current_user->isEmailValid()}
<div class="alert alert-danger" role="alert">
  {t}Sorry, but we have troubles sending you email notifications. Is your email still valid?{/t}
</div>
{/if}
