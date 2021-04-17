{extends file='emails/base.tpl'}

{block name=title}{t}Hooray{/t} 🥳{/block}
{block name=preview}{t}Your email address has been successfully reverted.{/t}{/block}

{block name=content}
  <p class="text-justify">
    {t}Your email address has been successfully reverted.{/t}
  </p>
  <div class="s-3"></div>
  <div class="text-center">
    <a class="btn btn-success btn-lg" href="{'login'|alias}">{t}Login{/t}</a>
  </div>
  <div class="s-3"></div>
  <p>{t}If you did not requested a password change, then you should also consider changing your password immediately.{/t}</p>
  <div class="align-center text-center">
    <a class="btn btn-success btn-lg" href="{'user_update_password'|alias}">{t}Change my password!{/t}</a>
  </div>
{/block}

{block name=reason}
  {t}You're getting this email because you've changed your email on our website.{/t}
{/block}
