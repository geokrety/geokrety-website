{extends file='emails/base.tpl'}

{block name=title}{t}Hooray{/t} 🥳{/block}
{block name=preview}{t}One of your GeoKrety has been adopted.{/t}{/block}

{block name=content}
<p>{t escape=no gkname=$geokret->name gkurl={'geokret_details'|alias:sprintf('@gkid=%s', $geokret->gkid)} username=$geokret->owner->username userurl={'user_details'|alias:sprintf('@userid=%d', $geokret->owner->id)}}Good news, your GeoKret <a href="%2">%1</a> was just adopted by user <a href="%4">%3</a>.{/t}</p>
<div class="s-3"></div>
<div class="text-center">
  <a class="btn btn-success btn-lg " href="{'geokret_details'|alias:sprintf('@gkid=%s', $geokret->gkid)}">{t}View GeoKret{/t}</a>
</div>
{/block}

{block name=reason}
  {t}You're getting this email because one of your GeoKrety has changed of owner on our website.{/t}
{/block}
