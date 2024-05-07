{if isset($current_user) && !$current_user->isEmailValidForEmailTask()}
<div class="alert alert-danger" role="alert">
  {t}Sorry, but we have troubles sending you email notifications.{/t}
  {$current_user->emailStatusText()}
</div>
{/if}
