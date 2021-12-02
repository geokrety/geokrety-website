{extends file='emails/base.tpl'}

{block name=title}{t}Username changed{/t} 👥{/block}
{block name=preview}{t}Your username on GeoKrety.org has been changed.{/t}{/block}

{block name=content}
  <p class="text-justify">
    {t}Someone, hopefully you, has requested a change on your GeoKrety username to: {$username}.{/t}
    {t}Please enjoy your new username!{/t}
  </p>
  <div class="s-3"></div>
  <div class="text-center">
    <a class="btn btn-success btn-lg" href="{'login'|alias}">{t}Login{/t}</a>
  </div>
{/block}

{block name=reason}
  {t}You're getting this email because you've changed your username on our website.{/t}
{/block}
