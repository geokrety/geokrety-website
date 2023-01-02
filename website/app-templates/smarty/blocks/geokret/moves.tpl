{include file='macros/pagination.tpl'}

{call pagination pg=$pg anchor='moves'}

{if $moves.subset}
<div class="target-highlight-next-div">
{foreach from=$moves.subset item=item}
{include file='elements/move.tpl' move=$item showMainAvatarMedal=true}
{/foreach}
</div>
{else}

{if $geokret->isOwner()}
    {t}This GeoKret has not moved yet.{/t}
{else}
    {t}Did you found this GeoKret? Log it!{/t}
{/if}

{/if}

{call pagination pg=$pg anchor='moves'}
