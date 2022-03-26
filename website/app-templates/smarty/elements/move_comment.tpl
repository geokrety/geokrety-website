<li class="list-group-item list-group-item-{if $comment->type === 0}info{else}danger{/if}" data-type="move-comment" data-move-comment-id="{$comment->id}" data-move-id="{$comment->move->id}">
    <div class="pull-left">
        {if $comment->type === 0}{fa icon="comment"}{else}{fa icon="exclamation-triangle"}{/if}
        {$comment->author|userlink:"{t}{GK_USER_DELETED_USERNAME}{/t}" nofilter}:
        <span class="move-comment">{$comment->content}</span>
    </div>
    <div class="pull-right">
        {$comment->created_on_datetime|print_date nofilter}
        {if !(isset($hide_actions) && $hide_actions) and ($comment->geokret->isOwner() or $comment->move->isAuthor() or $comment->isAuthor())}
        <button type="button" class="btn btn-danger btn-xs" title="{t}Delete comment{/t}" data-toggle="modal" data-target="#modal" data-type="move-comment-delete" data-id="{$comment->id}">
            {fa icon="trash"}
        </button>
        {/if}
    </div>
    <div class="clearfix"></div>
</li>
