{include file='macros/picture.tpl'}

{function move_picture}
<div class="gallery pull-right">
{foreach from=$moves_pictures item=item}
  {if $item->tripId != $move->ruchId}{continue}{/if}
  {call picture item=$item skipLinkToEntity=true skipTags=true isOwner=$geokret_details->isOwner()}
{/foreach}
</div>
<div class="clearfix"></div>
{/function}
