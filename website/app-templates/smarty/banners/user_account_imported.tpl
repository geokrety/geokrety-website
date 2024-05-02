{if isset($current_user) && $current_user->isAccountImported()}
<div class="alert alert-warning" role="alert">
  {t}Your account has been imported from GKv1.{/t}
  <strong>{t}Please validate your email address.{/t}</strong>
  {t escape=no href={$f3->alias('user_account_imported_gkv1_send_mail')}}You can request a <a href="%1">new confirmation mail</a>.{/t}
</div>
{/if}
