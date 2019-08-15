<p>{t username=$user->username}Hi %1,{/t}</p>
<p>{t}Someone, hopefully you, has requested a change in your GeoKrety contact email address. In order to validate the change please open the link below.{/t}</p>

<p><a href="{'user_update_email_validate_token'|alias:sprintf('token=%s', $token)}">{t}Click here to validate your new email address{/t}</a></p>
<p>{t url={'user_update_email_validate_token'|alias:sprintf('token=%s', $token)}}If the link doesn't work, please copy/paste this to your browser: %1{/t}</p>

<p>{t}If you did not requested this, please change your password immediatelly.{/t}</p>
---
<p>{t}The GeoKrety Team{/t}</p>
