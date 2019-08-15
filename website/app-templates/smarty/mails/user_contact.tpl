<p>{t username=$mail->to->username}Hi %1,{/t}</p>
<p>{t escape=no username=$mail->from->username url={'user_details'|alias:sprintf('@userid=%d', $mail->from->id)}}This email was sent by user <a href="%2">%1</a>.{/t}</p>

<p>
    {t email=GK_SITE_EMAIL}If you suspect an abuse, please let us know: %1{/t}<br />
    {t username=$mail->token}Referer: %1{/t}
</p>

<hr />
<p>{t}Subject:{/t} {$mail->subject}</p>
{$mail->content|markdown nofilter}
<hr />

<p>{t escape=no username=$mail->from->username url={'mail_to_user'|alias:sprintf('@userid=%d', $mail->from->id)}}You can reply to %1 using this <a href="%2">link</a>{/t}</p>
---
<p>{t}The GeoKrety Team{/t}</p>
