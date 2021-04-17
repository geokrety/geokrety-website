{extends file='emails/base.tpl'}

{block name=title}{t}Welcome to GeoKrety.org{/t} 🎉{/block}
{block name=preview}{t}One step left to use GeoKrety.org.{/t}{/block}

{block name=content}
<p class="text-justify">{t escape=no}You have just registered on GeoKrety.org. <strong>You still have to activate your account before your can use it.</strong> Please follow the link bellow.{/t}</p>
<div class="s-3"></div>
<div class="text-center">
  <a class="btn btn-success btn-lg" href="{'registration_activate'|alias:sprintf('@token=%s', $token->token)}">{t}Activate your account{/t}</a>
</div>
<div class="s-3"></div>
<p>{t expire=$token->expire_on_datetime|print_date_expiration}This link expires %1.{/t}</p>
<div class="s-3"></div>
<p>{t}If your account is not activated within that time, then it will be automatically deleted.{/t}</p>
{/block}

{block name=reason}
  {t}You're getting this email because you've just signed up on our website.{/t}
{/block}
