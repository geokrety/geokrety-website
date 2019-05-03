{function pictureIcon}{* filename="" *}
<a href="{$avatarUrl}/{$filename}" data-preview-image="{$avatarMinUrl}/{$filename}">
  <img src="{$iconsUrl}/idcard.png" width="14" height="10" alt="{t}GeoKret has avatar{/t}" />
</a>
{/function}

{function img width=100 height=100}{* filename="" *}
<img src="{$avatarMinUrl}/{$filename}" width="{$width}" height="{$height}" data-preview-image="{$avatarUrl}/{$filename}" />
{/function}

{function picture}{* item="" *}
<figure>
  <div class="parent">
    {call img filename=$item.filename}
    {if $item.country}{call flag country=$item.country}{/if}
    {if $item.type == '0'}<span class="type gk" title="{t}A GeoKret avatar{/t}"></span>
    {elseif $item.type == '1'}<span class="type plane" title="{t}A move picture{/t}"></span>
    {elseif $item.type == '2'}<span class="type human" title="{t}User's avatar{/t}"></span>
    {/if}
  </div>
  <figcaption>
    <p class="text-center">
      {if $item.legend}
      <small title="{$item.legend}">{$item.legend|truncate:30:'…'}</small>
      {else}
      &nbsp;
      {/if}
    </p>
    <p class="text-center">
      {if $item.type == '0'}{* GK *}
      {call gkLink id=$item.gk_id gk_name=$item.gk_name}
      {else if $item.type == '1'}{* MOVE *}
      {call gkLink id=$item.gk_id gk_name=$item.gk_name move_id=$item.id}
      {else if $item.type == '2'}{* USER *}
      {call userLink id=$item.user_id username=$item.username}
      {else}
      {t}Unknown type{/t}
      {/if}
    </p>
  </figcaption>
</figure>
{/function}
