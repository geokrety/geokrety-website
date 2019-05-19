<div class="modal-header alert-danger">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Do you really want to delete this news comment?{/t}</h4>
</div>
<form name="comment" action="newscomments.php?newsid={$comment->newsId}" method="post">
    <input type="hidden" name="delete" value="{$comment->id}" />

    <div class="panel panel-default margin-20">
        <div class="panel-heading">
            <div class="pull-left">
                {fa icon="file-text-o"}
                {userlink user=$comment->author}
            </div>
            <div class="pull-right">
                {Carbon::parse($comment->date)->diffForHumans()}
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="panel-body">
            {$comment->comment}
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-danger">{t}Delete{/t}</button>
    </div>
</form>
