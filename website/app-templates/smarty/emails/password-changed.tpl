{extends file='emails/base.tpl'}

{block name=title}{t}Congratulation{/t} 🎉{/block}
{block name=preview}{t}Your account on GeoKrety.org is now active.{/t}{/block}

{block name=content}
  <p class="text-justify">
    {t}Your password has been successfully changed.{/t}
  </p>
  <div class="s-3"></div>
  <div class="text-center">
    <a class="btn btn-success btn-lg" href="{'login'|alias}">{t}Login{/t}</a>
  </div>
{/block}

{block name=reason}
  {t}You're getting this email because you've changed your password on our website.{/t}
{/block}
