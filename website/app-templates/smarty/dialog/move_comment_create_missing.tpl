{block name=modal_content}
<div class="modal-header alert-danger">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Commenting a GeoKret move{/t}</h4>
</div>

<form name="move" action="{'move_comment_create_missing'|alias:sprintf('moveid=%d', $move->id)}" method="post">
    <div class="modal-body">
        {include file='elements/move.tpl' move=$move hide_actions=true hide_comments=true}

        <hr />

        <div class="form-group">
            <label class="control-label">{t}Your comment{/t}</label>
            <input type="text" class="form-control" name="comment" id="comment" value="{if isset($comment)}{$comment->content}{/if}" minlength="1" maxlength="500" autofocus required>
        </div>

    </div>
    <div class="modal-footer">
        {call csrf}
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-danger">{t}Declare missing{/t}</button>
    </div>
</form>
{/block}
