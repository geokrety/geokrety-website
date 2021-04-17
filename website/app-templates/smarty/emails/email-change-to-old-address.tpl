{extends file='emails/base.tpl'}

{block name=title}{t}Changing your email address{/t} 📯{/block}
{block name=preview}{t}Confirm your email address change.{/t}{/block}

{block name=content}
  <p class="text-justify">
    {t email=$token->email}Someone, hopefully you, has requested a change on your GeoKrety contact email address to: %1.{/t}
  </p>
  <div class="s-3"></div>
  <hr>
  <div class="s-3"></div>
  <p>{t}If you did not requested this, click the link below.{/t}</p>
  <div class="align-center text-center">
    <a class="btn btn-danger btn-lg" href="{'user_update_email_validate_token'|alias:sprintf('token=%s', $token->token)}">{t}Do not change!{/t}</a>
  </div>
  <div class="s-3"></div>
  <p>{t expire=$token->update_expire_on_datetime|print_date_expiration}This link expires %1.{/t}</p>
  <div class="s-3"></div>
  <p>{t}If you did not requested a password change, then you should also consider changing your password immediately.{/t}</p>
  <div class="align-center text-center">
    <a class="btn btn-success btn-lg" href="{'user_update_password'|alias}">{t}Change my password!{/t}</a>
  </div>
{/block}

{block name=reason}
  {t}You're getting this email because you've changed your email on our website.{/t}
{/block}
