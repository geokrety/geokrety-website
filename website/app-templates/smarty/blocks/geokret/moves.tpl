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
TODO: Hey! This GeoKret has not moved it. blablabla
{else}
TODO: Did you found this GeoKret? Log it!
{/if}

{/if}

{call pagination pg=$pg anchor='moves'}
