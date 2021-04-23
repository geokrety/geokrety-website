{extends file='emails/base.tpl'}

{block name=title}{t}Revalidate your email address{/t} 👌{/block}
{block name=preview}{t}Is this address still valid?{/t}{/block}

{block name=content}
  <p class="text-justify">
    {t}Your account has been imported from GKv1.{/t}
    {t}We would like to verify that your mail address is still valid.{/t}
    {t}This is important in case of password loss.{/t}
  </p>
  <div class="s-3"></div>
  <div class="align-center text-center">
    <a class="btn btn-success btn-lg" href="{'user_update_email_revalidate_token'|alias:sprintf('token=%s', $token->token)}">{t}Validate your email address{/t}</a>
  </div>
  <div class="s-3"></div>
  <p>{t expire=$token->validate_expire_on_datetime|print_date_expiration:2}This link expires %1.{/t}</p>
{/block}

{block name=reason}
  {t}You're getting this email because we need to verify that your email registered on our website is still valid.{/t}
{/block}
