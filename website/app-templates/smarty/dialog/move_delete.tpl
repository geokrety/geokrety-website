{block name=content}
<div class="modal-header alert-danger">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Do you really want to delete this move?{/t}</h4>
</div>

<form name="move" action="{'move_delete'|alias:sprintf('moveid=%d', $move->id)}" method="post">
    <div class="modal-body">
        {include file='elements/move.tpl' hide_actions=true}
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-danger">{t}Delete{/t}</button>
    </div>
</form>
{/block}
