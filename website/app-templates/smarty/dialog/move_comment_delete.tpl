{block name=modal_content}
<div class="modal-header alert-danger">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Do you really want to delete this move comment?{/t}</h4>
</div>

<form name="comment" action="{'move_comment_delete'|alias:sprintf('movecommentid=%d', $comment->id)}" method="post">
    <div class="modal-body">
        {include file='elements/move_comment.tpl' comment=$comment hide_actions=true}

    </div>
    <div class="modal-footer">
        {call csrf}
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-danger">{t}Delete{/t}</button>
    </div>
</form>
{/block}
