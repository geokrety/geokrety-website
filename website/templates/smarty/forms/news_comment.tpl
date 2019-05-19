{include 'blocks/news.tpl' item=$news}

<div class="panel panel-default">
    <div class="panel-heading">
        {t}Leave a comment{/t}
    </div>
    <div class="panel-body">
        {if $isLoggedIn}
        <form class="form-horizontal" action="/newscomments.php?newsid={$news->id}" method="post" id="formNewComment">

            <div class="form-group">
                <label for="comment" class="col-sm-2 control-label">{t}Comment{/t}</label>
                <div class="col-sm-10">
                    <textarea class="form-control maxl" rows="5" id="comment" name="comment" placeholder="{t}Your comment{/t}" maxlength="1000"></textarea>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="subscribe" name="subscribe" {if isset($newsSubscription) and $newsSubscription->subscribed} checked{/if}> {t}Subscribe to this news post{/t}
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">{t}Comment{/t}</button>
                </div>
            </div>

        </form>
        {else}
        <em>{t}Please login to post a new comment{/t}</em>
        {/if}
    </div>
</div>

<h3>{t}Comments{/t}</h3>
{foreach $newsComments as $comment}
<div class="panel panel-{if isset($newsSubscription) and $comment->date >= $newsSubscription->read}info{else}default{/if}">
    <div class="panel-heading">
        <div class="pull-left">
            {fa icon="file-text-o"}
            {userlink user=$comment->author}
        </div>
        <div class="pull-right">
            {Carbon::parse($comment->date)->diffForHumans()}
            {if $comment->isAuthor() or $isSuperUser}
            <button type="button" class="btn btn-danger btn-xs" title="{t}Delete comment{/t}" data-toggle="modal" data-target="#modal" data-type="news-comment-delete" data-id="{$comment->id}">
                {fa icon="trash"}
            </button>
            {/if}
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">
        {$comment->comment}
    </div>
</div>
{foreachelse}
{t}There are no comments for this post.{/t}
{/foreach}

{if $isLoggedIn}
{capture name="jq"}
// ----------------------------------- JQUERY - NEWS COMMENT - BEGIN
$("#formNewComment").validate({

    rules: {
        comment: {
            required: true,
            maxlength: 1000
        },
    },
    {include 'js/_jsValidationFixup.tpl.js'}
});

// ----------------------------------- JQUERY - NEWS COMMENT - END
{/capture}
{capture}{$jquery|array_push:$smarty.capture.jq}{/capture}
{/if}
