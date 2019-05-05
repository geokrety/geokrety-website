{function move_comment}
{foreach from=$moves_comments item=comment}
{if $comment.ruch_id != $move.id}{continue}{/if}
<!-- List group -->
<ul class="list-group">
  <li class="list-group-item list-group-item-{if $comment.type == 0}info{else}danger{/if}">
    <div class="pull-left">
      {if $comment.type == 0}{fa icon="comment"}{else}{fa icon="exclamation-triangle"}{/if}
      {call userLink id=$comment.user_id username=$comment.user}:
      {$comment.comment}
    </div>
    <div class="pull-right">
      {Carbon::parse($comment.data_dodania)->diffForHumans()}
      {if $isGeokretOwner or $currentUser == $move.author_id or $currentUser == $comment.user_id }
      <button type="button" class="btn btn-danger btn-xs" title="{t}Delete comment{/t}" data-toggle="modal" data-target="#modal" data-type="move-comment-delete" data-id="{$comment.comment_id}">
        {fa icon="trash"}
      </button>
      {/if}
    </div>
    <div class="clearfix"></div>
  </li>
</ul>
{/foreach}
{/function}
