{extends file='emails/base.tpl'}

{block name=title}{t}Forgot Your Password?{/t} 😇{/block}
{block name=preview}{t}Recover your password.{/t}{/block}

{block name=content}
  <p class="text-justify">
    {t}It happens. Click the link below to reset your password.{/t}
  </p>
  <div class="s-3"></div>
  <div class="text-center">
    <a class="btn btn-success btn-lg" href="{'password_recovery_validate_token'|alias:sprintf('@token=%s', $token->token)}">{t}Reset Password{/t}</a>
  </div>
  <div class="s-3"></div>
  <p>{t expire=$token->expire_on_datetime|print_date_expiration}This link expires %1.{/t}</p>
{/block}

{block name=reason}
  {t}You're getting this email because you've changed your password on our website.{/t}
{/block}
