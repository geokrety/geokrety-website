{include file='macros/pagination.tpl'}
<a class="anchor" id="moves"></a>

{call pagination pg=$pg anchor='moves'}
{foreach from=(array)$moves.subset item=item}
{include file='elements/move.tpl' move=$item}

{foreachelse}
{if $geokret->isOwner()}
TODO: Hey! This GeoKret has not moved it. blablabla
{else}
TODO: Did you found this GeoKret? Log it!
{/if}
{/foreach}
{call pagination pg=$pg anchor='moves'}
