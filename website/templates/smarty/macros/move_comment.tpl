{function move_comment}
{foreach from=$moves_comments item=comment}
{if $comment->tripId != $move->ruchId}{continue}{/if}
<!-- List group -->
<ul class="list-group">
  <li class="list-group-item list-group-item-{if $comment->type === 0}info{else}danger{/if}">
    <div class="pull-left">
      {if $comment->type == 0}{fa icon="comment"}{else}{fa icon="exclamation-triangle"}{/if}
      {userlink user=$comment->author()}:
      {$comment->comment}
    </div>
    <div class="pull-right">
      {Carbon::parse($comment->date)->diffForHumans()}
      {if $geokret_details->isOwner() or $currentUser == $move->userId or $currentUser == $comment->userId }
      <button type="button" class="btn btn-danger btn-xs" title="{t}Delete comment{/t}" data-toggle="modal" data-target="#modal" data-type="move-comment-delete" data-id="{$comment->id}">
        {fa icon="trash"}
      </button>
      {/if}
    </div>
    <div class="clearfix"></div>
  </li>
</ul>
{/foreach}
{/function}
