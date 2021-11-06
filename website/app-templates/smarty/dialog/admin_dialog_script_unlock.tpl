{block name=content}
<div class="modal-header alert-warning">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Do you really want to unlock this script?{/t}</h4>
</div>

<form name="UnlockScriptForm" action="{'admin_scripts_unlock'|alias:sprintf('scriptid=%d', $script->id)}" method="post">
    <div class="modal-body">
        {t escape=no script=$script->name since=$script->locked_on_datetime|print_date}"%1" locked since %2{/t}
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-warning">{t}Unlock{/t}</button>
    </div>
</form>
{/block}
