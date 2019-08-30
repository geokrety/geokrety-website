{if isset($user) && $user->isCurrentUser() && $user->email_activation}
<div class="alert alert-warning" role="alert">
  {t days=GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY}You have a pending email validation. Don't forget to click on the link present in the email to finish the procedure. The link is valid only for %1 days!{/t}
</div>
{/if}
