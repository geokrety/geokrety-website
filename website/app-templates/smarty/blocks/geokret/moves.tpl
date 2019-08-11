<a class="anchor" id="moves"></a>

{foreach from=$geokret->moves item=item}
{*call move move=$item geokret=$geokret_details moves_pictures=$geokret_pictures*}

{include file='elements/move.tpl' move=$item}

{foreachelse}
{if $geokret->isOwner()}
TODO: Hey! This GeoKret has not moved it. blablabla
{else}
TODO: Did you found this GeoKret? Log it!
{/if}
{/foreach}
