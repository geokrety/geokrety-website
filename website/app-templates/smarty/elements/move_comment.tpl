<ul class="list-group">
    <li class="list-group-item list-group-item-{if $comment->type === 0}info{else}danger{/if}">
        <div class="pull-left">
            {if $comment->type === 0}{fa icon="comment"}{else}{fa icon="exclamation-triangle"}{/if}
            {$comment->author|userlink nofilter}:
            {$comment->content}
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
</ul>
