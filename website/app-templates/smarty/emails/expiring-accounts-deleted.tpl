{extends file='emails/base.tpl'}

{block name=title}❗ {t}Account has been deleted{/t} ❗{/block}
{block name=preview}{t}Your account is now deleted.{/t}{/block}

{block name=content}
<p class="text-justify">
    {t creation=$token->created_on_datetime|print_date_expiration:1}The account you created %1 has never been activated.{/t}
    {t delay=GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY}Our policy is to delete unactivated accounts after %1 days.{/t}
</p>
<div class="s-3"></div>
<p class="text-justify">
    <strong>{t}We are sorry to inform you that your account has been permanently erased.{/t}</strong>
    {t}The username you have chosen on registration is now released and can be freely taken by someone else.{/t}
</p>
{/block}

{block name=reason}
  {t}You're getting this email because you've just signed up on our website.{/t}
{/block}
