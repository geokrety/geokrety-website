<p>{t username=$user->username}Hi %1,{/t}</p>
<p>{t escape=no gkname=$geokret->name gkurl={'geokret_details'|alias:sprintf('@gkid=%d', $geokret->id)} username=$user->username userurl={'user_details'|alias:sprintf('@userid=%d', $user->id)}}Good news, your GeoKret <a href="%2">%1</a> was just adopted by user <a href="%4">%3</a>.{/t}</p>
---
<p>{t}The GeoKrety Team{/t}</p>
