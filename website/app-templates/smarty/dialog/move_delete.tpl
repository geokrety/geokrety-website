{block name=modal_content}
<div class="modal-header alert-danger">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Do you really want to delete this move?{/t}</h4>
</div>

<form name="moveDeleteForm" action="{'move_delete'|alias:sprintf('moveid=%d', $move->id)}" method="post">
    <div class="modal-body">
        {include file='elements/move.tpl' hide_actions=true}
        {if $move->pictures_count}
            <hr>
            <div class="alert alert-danger" role="alert">
            {if !is_null($move->geokret->avatar) && $move->pictures->contains($move->geokret->avatar->id)}
                {t}Deleting this Move will delete attached pictures and the main avatar!{/t}
            {else}
                {t}Deleting this Move will delete attached pictures!{/t}
            {/if}
            </div>
        {/if}
    </div>
    <div class="modal-footer">
        {call csrf}
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-danger">{t}Delete{/t}</button>
    </div>
</form>
{/block}
