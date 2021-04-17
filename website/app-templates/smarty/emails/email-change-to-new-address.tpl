{extends file='emails/base.tpl'}

{block name=title}{t}Changing your email address{/t} ✉{/block}
{block name=preview}{t}Confirm your email address change.{/t}{/block}

{block name=content}
  <p class="text-justify">
    {t}Someone, hopefully you, has requested to change it's GeoKrety contact email address to yours.{/t}
  </p>
  <div class="s-3"></div>
  <div class="align-center text-center">
    <a class="btn btn-success btn-lg" href="{'user_update_email_validate_token'|alias:sprintf('token=%s', $token->token)}">{t}Validate your new email address{/t}</a>
  </div>
  <div class="s-3"></div>
  <p>{t expire=$token->update_expire_on_datetime|print_date_expiration}This link is valid for %1.{/t}</p>
  <div class="s-3"></div>
  <p>{t}If you did not requested this and do not know what are GeoKrety, please ignore this email. You will never hear about us again!{/t}</p>
{/block}

{block name=reason}
  {t}You're getting this email because you've requested an email address change on our website.{/t}
{/block}
