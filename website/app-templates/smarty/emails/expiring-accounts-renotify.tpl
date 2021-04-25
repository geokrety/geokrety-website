{extends file='emails/base.tpl'}

{block name=title}❗ {t}Account not yet active{/t} ❗{/block}
{block name=preview}{t}Your account is about to be deleted.{/t}{/block}

{block name=content}
<p class="text-justify">
    {t creation=$token->created_on_datetime|print_date_expiration:1}The account you created %1 has not yet been activated.{/t}
    <strong>{t}If you would like to keep that account, you still have to validate it by clicking on the link below.{/t}</strong>
    {t expire=GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY}Unactivated accounts are automatically deleted after %1 days.{/t}
</p>
<div class="s-3"></div>
<div class="text-center">
    <a class="btn btn-success btn-lg" href="{'registration_activate'|alias:sprintf('@token=%s', $token->token)}">{t}Activate your account{/t}</a>
</div>
<div class="s-3"></div>
<p>{t expire=$token->expire_on_datetime|print_date_expiration:2}This link expires %1.{/t}</p>
<div class="s-3"></div>
<p>{t}If your account is not activated within that time, then it will be automatically deleted.{/t}</p>
{/block}

{block name=reason}
  {t}You're getting this email because you've just signed up on our website.{/t}
{/block}
