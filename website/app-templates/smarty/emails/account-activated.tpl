{extends file='emails/base.tpl'}

{block name=title}{t}Congratulation{/t} 🎉{/block}
{block name=preview}{t}Your account on GeoKrety.org is now active.{/t}{/block}

{block name=content}
  <p class="text-justify">
    {t}Your account on GeoKrety.org is now fully functional.{/t}
  </p>
  <div class="s-3"></div>
  <div class="text-center">
    <a class="btn btn-success btn-lg" href="{'login'|alias}">{t}Login{/t}</a>
  </div>
  <div class="s-3"></div>
  <p class="text-justified">{t escape=no move='move_create'|alias create='geokret_create'|alias}As a member you can now <a href="%1">register GeoKrety moves</a>, upload pictures and of course <a href="%2">create your own GeoKrety</a> for free!{/t}</p>
{/block}

{block name=reason}
  {t}You're getting this email because you've just signed up on our website.{/t}
{/block}
