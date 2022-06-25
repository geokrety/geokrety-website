<div class="news-comment panel panel-{if isset($news_last_read_datetime) && $comment->updated_on_datetime >= $news_last_read_datetime}info{else}default{/if}" data-gk-type="news-comment" data-id="{$comment->id}">
    <div class="panel-heading">
        <div class="pull-left">
            {fa icon="file-text-o"}
            {$comment->author|userlink nofilter}
        </div>
        <div class="pull-right">
            {$comment->updated_on_datetime|print_date nofilter}
            {if !(isset($hide_action) && $hide_actions) and $comment->isAuthor()}
            <button type="button" class="btn btn-danger btn-xs" title="{t}Delete comment{/t}" data-toggle="modal" data-target="#modal" data-type="news-comment-delete" data-id="{$comment->id}">
                {fa icon="trash"}
            </button>
            {/if}
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">
        {$comment->content|markdown nofilter}
    </div>
</div>
