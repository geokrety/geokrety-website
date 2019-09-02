<p>{t username=$token->user->username}Hi %1,{/t}</p>

<p>{t}Somebody, hopefully you, requested a password change on GeoKrety.org.{/t}</p>

<p>{t}To validate the request, please click on the link below.{/t}</p>
<p><a href="{'password_recovery_validate_token'|alias:sprintf('@token=%s', $token->token)}">Reset my password</a></p>

<p>{t escape=no url={'password_recovery_validate'|alias}}If the link doesn't work, go to page: <a href="%1">%1</a> and use the token below:{/t}</p>
<p>{$token->token}</p>

<p>{t}If you didn't requested a password change, then please ignore this email.{/t}</p>
---
<p>{t}The GeoKrety Team{/t}</p>
