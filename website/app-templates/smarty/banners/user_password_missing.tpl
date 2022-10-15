{if isset($current_user) && !$current_user->hasPassword() && !$current_user->isConnectedWithProvider()}
<div class="alert alert-warning" role="alert">
    {t escape=no}Sorry, but your account has no password registered and no OAuth connection. <b>You will not be able to login again!</b>{/t}
  {t escape=no url1='user_update_password'|alias url2='user_details'|alias:sprintf('userid=%d', $current_user->id)}Please consider <a href="%1">setting a password</a> or <a href="%2">link your account</a> with some OAuth provider.{/t}
</div>
{/if}
