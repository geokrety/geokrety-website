{function move_picture}
<div class="gallery pull-right">
{foreach from=$moves_pictures item=item}
  {if $item.id != $move.id}{continue}{/if}
  {picture item=$item skipLinkToEntity=true skipTags=true isGeokretOwner=$isGeokretOwner}
{/foreach}
</div>
<div class="clearfix"></div>
{/function}
