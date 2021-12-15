{block name=content}
<div class="modal-header alert-success">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Do you really want to ack this script locking alert?{/t}</h4>
</div>

<form name="UnlockScriptForm" action="{'admin_scripts_ack'|alias:sprintf('scriptid=%d', $script->id)}" method="post">
    <div class="modal-body">
        {t escape=no script=$script->name since=$script->locked_on_datetime|print_date}"%1" locked since %2{/t}
    </div>
    <div class="modal-footer">
        {call csrf}
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-success">{t}Ack{/t}</button>
    </div>
</form>
{/block}
