{if isset($user) && $user->isCurrentUser() && $user->email_activation}
<div class="alert alert-warning" role="alert">
  {t days=$user->email_activation.0->expire_on_datetime|print_date_expiration}You have a pending email validation. Don't forget to click on the link present in the email to finish the procedure. The link will expire %1!{/t}
</div>
{/if}
