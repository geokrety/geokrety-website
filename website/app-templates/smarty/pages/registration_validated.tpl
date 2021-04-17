{extends file='base.tpl'}

{block name=title}{t}Welcome to the GeoKrety.org community!{/t}{/block}

{block name=content}

<div class="jumbotron">
  <h1>{t}Welcome to the GeoKrety.org community!{/t} 🎉</h1>
  {if isset($current_user)}
    <p>{t escape=no move='move_create'|alias create='geokret_create'|alias}You are now able to <a href="%1">post move</a> and <a href="%2">create your own GeoKrety</a>.{/t}</p>
  {else}
    <p>{t}You just finish your registration. You may now wish to login and move your first GeoKret.{/t}</p>
    <p><a class="btn btn-primary btn-lg" href="{'login'|alias}" role="button">{fa icon="sign-in"} {t}Sign in{/t}</a></p>
  {/if}
</div>
{/block}
