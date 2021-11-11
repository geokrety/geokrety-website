{extends file='emails/base.tpl'}

{block name=title}{t}Contact from a GeoKrety.org user{/t} 💌{/block}
{block name=preview}{t}Someone sent you a private message.{/t}{/block}

{block name=content}
  <p class="text-justify">
    {t escape=no username=$mail->from_user->username|escape url={'user_details'|alias:sprintf('@userid=%d', $mail->from_user->id)}}This email was sent by user <a href="%2">%1</a>.{/t}
  </p>
  <div class="s-3"></div>
  <p>
    {t email=GK_SITE_EMAIL}If you suspect an abuse, please let us know: %1{/t}<br />
    {t username=$mail->token}Referer: %1{/t}
  </p>
  <div class="s-3"></div>
  <hr />
  <div class="s-3"></div>
  <p>{t}Subject:{/t} {$mail->subject}</p>
    {$mail->content|markdown nofilter}
  <hr />
  <div class="s-3"></div>
  <div class="text-center">
    <a class="btn btn-success btn-lg" href="{'mail_to_user'|alias:sprintf('@userid=%d', $mail->from_user->id)}">{t escape=no username=$mail->from_user->username|escape}Reply to %1{/t}</a>
  </div>
{/block}

{block name=reason}
  {t}You're getting this email because one of GeoKrety.org users send you a message via your user profile.{/t}
{/block}
